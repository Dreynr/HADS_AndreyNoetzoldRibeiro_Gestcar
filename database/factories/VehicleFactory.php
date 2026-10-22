<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'plate' => strtoupper(fake()->bothify('???#?##')),
            'model' => fake()->randomElement(['Fusca', 'Gol', 'Civic', 'Onix', 'HB20', 'Corolla']),
            'color' => fake()->randomElement(['Preto', 'Branco', 'Prata', 'Azul', 'Vermelho']),
        ];
    }
}
