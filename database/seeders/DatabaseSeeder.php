<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Enums\Weekday;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $awa = $this->seedUser('Awa Sossou', 'awa.sossou@univ.test', Role::Professor);
        $koffi = $this->seedUser('Koffi Dossou', 'koffi.dossou@univ.test', Role::Professor);
        $alice = $this->seedUser('Alice Houngbo', 'alice.houngbo@univ.test', Role::Student);
        $bob = $this->seedUser('Bob Adjovi', 'bob.adjovi@univ.test', Role::Student);
        $chloe = $this->seedUser('Chloé Kpadonou', 'chloe.kpadonou@univ.test', Role::Student);
        $david = $this->seedUser('David Tossou', 'david.tossou@univ.test', Role::Student);

        $this->seedCourse($awa, 'Algorithmique avancée', 'Amphi B', Weekday::Monday, '08:00', '10:00', [$alice, $bob]);
        $this->seedCourse($awa, 'Bases de données', 'Salle 12', Weekday::Tuesday, '10:00', '12:00', [$alice, $chloe]);
        $this->seedCourse($koffi, 'Réseaux informatiques', 'Salle 07', Weekday::Wednesday, '14:00', '16:00', [$bob, $david]);
        $this->seedCourse($awa, 'Génie logiciel', 'Amphi A', Weekday::Thursday, '08:00', '10:00', [$alice, $bob, $chloe, $david]);
        $this->seedCourse($koffi, 'Architecture logicielle', 'Salle 21', Weekday::Friday, '10:00', '12:00', [$alice, $david]);
        $this->seedCourse($awa, 'Mathématiques discrètes', 'Salle 05', Weekday::Saturday, '08:00', '10:00', [$bob, $chloe]);
    }

    /**
     * Seed a verified demo user.
     */
    private function seedUser(string $name, string $email, Role $role): User
    {
        return User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => 'password',
                'role' => $role,
                'email_verified_at' => now(),
            ],
        );
    }

    /**
     * Seed a course and its enrolled students.
     *
     * @param  array<int, User>  $students
     */
    private function seedCourse(
        User $professor,
        string $title,
        string $room,
        Weekday $weekday,
        string $startsAt,
        string $endsAt,
        array $students,
    ): void {
        $course = Course::query()->firstOrCreate(
            [
                'professor_id' => $professor->id,
                'title' => $title,
            ],
            [
                'room' => $room,
                'day_of_week' => $weekday,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ],
        );

        $course->students()->sync(
            collect($students)->map(fn (User $student): int => $student->id)->all(),
        );
    }
}
