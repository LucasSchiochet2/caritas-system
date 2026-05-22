<?php

namespace Database\Factories;

use App\Models\Family;
use App\Models\Parish;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Family>
 */
class FamilyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parish_id' => Parish::factory(),
            'name' => 'Familia '.$this->faker->lastName(),
            'address' => $this->faker->streetAddress(),
            'observations' => $this->faker->optional()->sentence(),
        ];
    }
}
