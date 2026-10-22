<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'vehicle_id' => Vehicle::factory(),
            'service_id' => Service::factory(),
            // Dia util futuro: so a noite e permitida em dia de semana.
            'scheduled_at' => now()->addWeek()->next('Monday')->setTime(17, 0),
            'period' => 'noite',
            'status' => 'pendente',
        ];
    }

    public function on(string $date, string $period = 'noite'): static
    {
        return $this->state(fn () => [
            'scheduled_at' => $date . ' ' . str_pad(
                (string) \App\Models\Appointment::periodStart($period), 2, '0', STR_PAD_LEFT
            ) . ':00:00',
            'period' => $period,
        ]);
    }

    public function status(string $status): static
    {
        return $this->state(['status' => $status]);
    }
}
