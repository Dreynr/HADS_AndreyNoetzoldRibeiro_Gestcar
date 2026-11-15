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

    private function sendOneDayReminders(AppointmentNotifier $notifier): void
    {
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');

        $appointments = Appointment::whereDate('scheduled_at', $tomorrow)
            ->whereIn('status', ['pendente', 'confirmado'])
            ->where('reminder_1day_sent', false)
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
            $this->info("Lembrete 1 dia enviado: {$appointment->user->name}");
        }
    }

    private function sendOneHourReminders(AppointmentNotifier $notifier): void
    {
        $now = Carbon::now();

        $appointments = Appointment::whereDate('scheduled_at', today())
            ->whereIn('status', ['pendente', 'confirmado'])
            ->where('reminder_1hour_sent', false)
            ->with(['user', 'vehicle', 'service', 'extras'])
            ->get();

        foreach ($appointments as $appointment) {
            $periodStart = Appointment::periodStart($appointment->period);
            $appointmentTime = $appointment->scheduled_at->copy()->setTime($periodStart, 0);
            $oneHourBefore = $appointmentTime->copy()->subHour();

            if ($now->gte($oneHourBefore) && $now->lt($appointmentTime)) {
                if (!$notifier->reminder($appointment, '1hour')) {
                    $this->error("Nenhum canal entregou o lembrete de 1 hora para {$appointment->user->name}");

                    continue;
                }

                $appointment->update(['reminder_1hour_sent' => true]);
                $this->info("Lembrete 1 hora enviado: {$appointment->user->name}");
            }
        }
    }
}
