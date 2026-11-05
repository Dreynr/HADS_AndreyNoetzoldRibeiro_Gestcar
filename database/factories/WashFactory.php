<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Wash>
 */
class WashFactory extends Factory
{
    public function definition(): array
    {
        $service = Service::factory();

        return [
            'appointment_id' => null,
            'user_id' => User::factory(),
            'vehicle_id' => Vehicle::factory(),
            'service_id' => $service,
            'service_name' => 'Lavagem simples',
            'amount' => 50.00,
            'performed_at' => now(),
        ];
    }

    public function performedAt(string $date): static
    {
        return $this->state(['performed_at' => $date]);
    }
}
