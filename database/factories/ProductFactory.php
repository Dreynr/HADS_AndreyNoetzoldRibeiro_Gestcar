<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Produto ' . $this->faker->unique()->numberBetween(1, 99999),
            'unit' => 'L',
            'quantity' => 0,
            'min_quantity' => 5,
            'active' => true,
        ];
    }

    public function withBalance(float $quantity): static
    {
        return $this->state(['quantity' => $quantity]);
    }
}
