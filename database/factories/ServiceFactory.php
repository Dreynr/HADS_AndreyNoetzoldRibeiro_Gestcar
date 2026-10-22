<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Lavagem ' . fake()->unique()->words(2, true),
            'type' => 'principal',
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 30, 150),
            'duration_minutes' => fake()->randomElement([45, 60, 120, 180]),
            'blocks_period' => false,
            'active' => true,
        ];
    }

    /** Servico completo: ocupa o periodo inteiro. */
    public function blocksPeriod(): static
    {
        return $this->state(['blocks_period' => true]);
    }

    /** Servico vendido como adicional de uma lavagem. */
    public function extra(): static
    {
        return $this->state(['type' => 'extra']);
    }

    public function inactive(): static
    {
        return $this->state(['active' => false]);
    }
}
