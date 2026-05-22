<?php

namespace Database\Factories;

use App\Models\AssistedFamilyMember;
use App\Models\Family;
use App\Models\Parish;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssistedFamilyMember>
 */
class AssistedFamilyMemberFactory extends Factory
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
            'family_id' => Family::factory(),
            'name' => $this->faker->name(),
            'mother_name' => $this->faker->name('female'),
            'relationship' => $this->faker->randomElement(['pai', 'mae', 'filho', 'filha', 'avo']),
            'age' => $this->faker->numberBetween(0, 90),
            'registration_status' => 'ativo',
            'registration_date' => $this->faker->date(),
            'personal_income' => $this->faker->randomFloat(2, 0, 3000),
            'is_responsible' => false,
        ];
    }

    public function responsible(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_responsible' => true,
        ]);
    }
}
