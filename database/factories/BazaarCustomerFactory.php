<?php

namespace Database\Factories;

use App\Models\BazaarCustomer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BazaarCustomer>
 */
class BazaarCustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'birth_date' => $this->faker->dateTimeBetween('-90 years', '-18 years')->format('Y-m-d'),
            'cpf' => $this->faker->unique()->numerify('###.###.###-##'),
        ];
    }
}
