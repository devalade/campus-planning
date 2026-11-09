<?php

namespace Database\Factories;

use App\Enums\Room;
use App\Enums\Weekday;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startHour = fake()->numberBetween(8, 17);

        return [
            'professor_id' => User::factory()->professor(),
            'title' => fake()->randomElement([
                'Algorithmique',
                'Bases de données',
                'Réseaux informatiques',
                'Génie logiciel',
                'Mathématiques discrètes',
                'Architecture logicielle',
            ]),
            'room' => fake()->randomElement(Room::values()),
            'day_of_week' => fake()->randomElement(Weekday::schedulable()),
            'starts_at' => sprintf('%02d:00:00', $startHour),
            'ends_at' => sprintf('%02d:00:00', $startHour + 2),
        ];
    }
}
