<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Services\AppointmentNotifier;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders';
    protected $description = 'Envia lembretes de agendamento (1 dia antes e 1 hora antes)';

    public function handle(AppointmentNotifier $notifier): int
    {
        $this->sendOneDayReminders($notifier);
        $this->sendOneHourReminders($notifier);

        return Command::SUCCESS;
    }

    /**
     * Lembrete de um dia, contado a partir do HORARIO marcado (D-22).
     *
     * A janela vai de 24 horas antes ate 2 horas antes. O limite de baixo
     * existe para nao colidir com o lembrete de 1 hora: sem ele, um
     * agendamento criado em cima da hora receberia os dois quase juntos.
     *
     * Como o comando roda a cada 5 minutos, o aviso sai poucos minutos
     * depois de o agendamento cruzar a marca das 24 horas.
     */
    private function sendOneDayReminders(AppointmentNotifier $notifier): void
    {
        $agora = Carbon::now();

        $appointments = Appointment::whereIn('status', ['pendente', 'confirmado'])
            ->where('reminder_1day_sent', false)
            ->whereBetween('scheduled_at', [
                $agora->copy()->addHours(2),
                $agora->copy()->addDay(),
            ])
            ->with(['user', 'vehicle', 'service', 'extras'])
            ->get();

        foreach ($appointments as $appointment) {
            // O notifier trata a falha de cada canal por dentro e registra o
            // que aconteceu, entao o comando segue para o proximo cliente.
            if (!$notifier->reminder($appointment, '1day')) {
                $this->error("Nenhum canal entregou o lembrete de 1 dia para {$appointment->user->name}");

                continue;
            }

            // A marca so e gravada quando algum canal entregou. Se nenhum
            // entregou, a proxima rodada tenta de novo — e o indice unico de
            // notification_logs garante que quem ja recebeu nao receba duas
            // vezes.
            $appointment->update(['reminder_1day_sent' => true]);
            $this->info("Lembrete 1 dia enviado: {$appointment->user->name} ({$appointment->scheduled_at->format('d/m H:i')})");
        }
    }

    /**
     * Lembrete de uma hora, contado a partir do HORARIO marcado (D-22).
     *
     * Antes isto usava o inicio do periodo, entao quem marcava as 19:30 era
     * lembrado as 16h. Agora a janela e simplesmente "o agendamento comeca
     * na proxima hora".
     */
    private function sendOneHourReminders(AppointmentNotifier $notifier): void
    {
        $agora = Carbon::now();

        $appointments = Appointment::whereIn('status', ['pendente', 'confirmado'])
            ->where('reminder_1hour_sent', false)
            ->whereBetween('scheduled_at', [
                $agora,
                $agora->copy()->addHour(),
            ])
            ->with(['user', 'vehicle', 'service', 'extras'])
            ->get();

        foreach ($appointments as $appointment) {
            if (!$notifier->reminder($appointment, '1hour')) {
                $this->error("Nenhum canal entregou o lembrete de 1 hora para {$appointment->user->name}");

                continue;
            }

            $appointment->update(['reminder_1hour_sent' => true]);
            $this->info("Lembrete 1 hora enviado: {$appointment->user->name} ({$appointment->scheduled_at->format('d/m H:i')})");
        }
    }
}
