<?php

namespace Database\Factories;

use App\Models\SpecialDay;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SpecialDay>
 */
class SpecialDayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => fake()->unique()->dateTimeBetween('+1 week', '+1 year')->format('Y-m-d'),
            'label' => fake()->randomElement([
                'Journée pédagogique',
                'Fermeture exceptionnelle',
                'Jour férié',
            ]),
            'created_by' => null,
        ];
    }
}
