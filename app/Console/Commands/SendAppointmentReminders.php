<?php

namespace App\Console\Commands;

use App\Mail\AppointmentReminderMail;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders';
    protected $description = 'Envia lembretes de agendamento (1 dia antes e 1 hora antes)';

    public function handle(): int
    {
        $this->sendOneDayReminders();
        $this->sendOneHourReminders();

        return Command::SUCCESS;
    }

    private function sendOneDayReminders(): void
    {
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');

        $appointments = Appointment::whereDate('scheduled_at', $tomorrow)
            ->whereIn('status', ['pendente', 'confirmado'])
            ->where('reminder_1day_sent', false)
            ->with(['user', 'vehicle', 'service', 'extras'])
            ->get();

        foreach ($appointments as $appointment) {
            try {
                Mail::to($appointment->user->email)
                    ->send(new AppointmentReminderMail($appointment, '1day'));

                $appointment->update(['reminder_1day_sent' => true]);
                $this->info("Lembrete 1 dia enviado: {$appointment->user->name}");
            } catch (\Exception $e) {
                $this->error("Erro ao enviar lembrete 1 dia para {$appointment->user->email}: {$e->getMessage()}");
            }
        }
    }

    private function sendOneHourReminders(): void
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
                try {
                    Mail::to($appointment->user->email)
                        ->send(new AppointmentReminderMail($appointment, '1hour'));

                    $appointment->update(['reminder_1hour_sent' => true]);
                    $this->info("Lembrete 1 hora enviado: {$appointment->user->name}");
                } catch (\Exception $e) {
                    $this->error("Erro ao enviar lembrete 1 hora para {$appointment->user->email}: {$e->getMessage()}");
                }
            }
        }
    }
}
