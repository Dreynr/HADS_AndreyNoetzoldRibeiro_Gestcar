<?php

namespace App\Services;

use App\Contracts\WhatsAppSender;
use App\Mail\AppointmentReminderMail;
use App\Mail\AppointmentStatusMail;
use App\Models\Appointment;
use App\Models\NotificationLog;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Avisa o cliente sobre o agendamento dele, por e-mail e, para quem
 * autorizou, tambem por WhatsApp (RF10, B-08).
 *
 * Esta classe existe para a regra do aviso ficar num lugar so: os tres
 * pontos que avisam o cliente (mudanca de status e os dois lembretes)
 * chamam os mesmos passos, com as mesmas garantias.
 */
class AppointmentNotifier
{
    public function __construct(
        private readonly WhatsAppSender $whatsapp,
    ) {
    }

    /**
     * @return bool true se pelo menos um canal entregou.
     */
    public function statusChanged(Appointment $appointment, string $status, ?string $observation = null): bool
    {
        return $this->deliver(
            $appointment,
            "status_{$status}",
            new AppointmentStatusMail($appointment, $status, $observation),
            $this->statusMessage($appointment, $status, $observation),
        );
    }

    /**
     * @param  string  $kind  '1day' ou '1hour'.
     * @return bool true se pelo menos um canal entregou.
     */
    public function reminder(Appointment $appointment, string $kind): bool
    {
        return $this->deliver(
            $appointment,
            $kind === '1day' ? 'reminder_1day' : 'reminder_1hour',
            new AppointmentReminderMail($appointment, $kind),
            $this->reminderMessage($appointment, $kind),
        );
    }

    /**
     * Manda o mesmo aviso pelos canais que valem para este cliente.
     *
     * Cada canal tem o seu proprio try, dentro do attempt(): o WhatsApp fora
     * do ar nao pode impedir o e-mail de sair (RNF10).
     */
    private function deliver(Appointment $appointment, string $type, Mailable $mail, string $message): bool
    {
        $user = $appointment->user;
        $delivered = false;

        // Conta anonimizada (D-07) fica sem e-mail: nao ha para onde mandar.
        if ($user->email) {
            $delivered = $this->attempt($appointment, NotificationLog::CHANNEL_EMAIL, $type, function () use ($user, $mail) {
                Mail::to($user->email)->send($mail);
            });
        }

        // Sem autorizacao do cliente, nao existe WhatsApp (LGPD, D-08).
        if ($user->whatsapp_opt_in && $user->phone) {
            $sent = $this->attempt($appointment, NotificationLog::CHANNEL_WHATSAPP, $type, function () use ($user, $message) {
                $this->whatsapp->send($user->phone, $message);
            });

            $delivered = $delivered || $sent;
        }

        return $delivered;
    }

    /**
     * Uma tentativa de envio, sempre registrada em notification_logs.
     *
     * Se o aviso ja saiu por este canal, nao sai de novo — e o que impede o
     * cliente de receber a mesma mensagem duas vezes quando o comando dos
     * lembretes roda de novo depois de uma falha.
     *
     * Nada aqui dentro pode escapar: quem chama esta classe (o controller e
     * o comando) conta com isso para nao quebrar por causa de um aviso.
     */
    private function attempt(Appointment $appointment, string $channel, string $type, callable $send): bool
    {
        try {
            $log = NotificationLog::firstOrNew([
                'appointment_id' => $appointment->id,
                'channel' => $channel,
                'type' => $type,
            ]);

            if ($log->status === NotificationLog::STATUS_SENT) {
                return true;
            }

            $log->user_id = $appointment->user_id;

            try {
                $send();

                $log->status = NotificationLog::STATUS_SENT;
                $log->error = null;
            } catch (Throwable $e) {
                // Falha de aviso nao quebra a tela nem derruba o outro canal,
                // mas tambem nao some: vai para o log do Laravel e para a
                // tabela, com o motivo.
                report($e);

                $log->status = NotificationLog::STATUS_FAILED;
                $log->error = mb_substr($e->getMessage(), 0, 1000);
            }

            $log->save();

            return $log->status === NotificationLog::STATUS_SENT;
        } catch (Throwable $e) {
            // Ate o registro falhando (banco fora, corrida pelo indice unico)
            // o aviso nao pode derrubar quem chamou.
            report($e);

            return false;
        }
    }

    private function statusMessage(Appointment $appointment, string $status, ?string $observation): string
    {
        $name = config('app.name');

        $opening = match ($status) {
            'confirmado' => "Olá, {$appointment->user->name}! Seu agendamento na {$name} foi confirmado.",
            'cancelado' => "Olá, {$appointment->user->name}! Seu agendamento na {$name} foi cancelado.",
            'concluido' => "Olá, {$appointment->user->name}! Seu serviço na {$name} foi concluído.",
            default => "Olá, {$appointment->user->name}! Seu agendamento na {$name} foi atualizado.",
        };

        $message = $opening . "\n\n" . $this->appointmentSummary($appointment);

        if ($observation) {
            $message .= "\n\nObservação: {$observation}";
        }

        return $message;
    }

    private function reminderMessage(Appointment $appointment, string $kind): string
    {
        $name = config('app.name');

        $when = $kind === '1day'
            ? 'é amanhã'
            : 'começa em cerca de uma hora';

        return "Olá, {$appointment->user->name}! Lembrete: seu agendamento na {$name} {$when}."
            . "\n\n" . $this->appointmentSummary($appointment);
    }

    private function appointmentSummary(Appointment $appointment): string
    {
        return "Serviço: {$appointment->service->name}"
            . "\nData: {$appointment->scheduled_at->format('d/m/Y')}"
            . "\nPeríodo: " . Appointment::periodLabel($appointment->period);
    }
}
