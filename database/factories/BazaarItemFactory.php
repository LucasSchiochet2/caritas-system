<?php

namespace Database\Factories;

use App\Models\BazaarItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BazaarItem>
 */
class BazaarItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'suggested_price' => $this->faker->randomFloat(2, 5, 500),
            'name' => ucfirst($this->faker->words(2, true)),
            'color' => $this->faker->safeColorName(),
            'size' => $this->faker->randomElement(['PP', 'P', 'M', 'G', 'GG', 'Unico']),
            'gender' => $this->faker->randomElement(['masculino', 'feminino', 'unissex']),
            'quantity' => $this->faker->numberBetween(1, 30),
            'condition' => $this->faker->randomElement(['novo', 'seminovo', 'usado']),
        ];
    }
}