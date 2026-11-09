<?php

use App\Enums\Weekday;
use App\Models\Course;
use App\Models\User;
use Livewire\Livewire;

test('professors see the courses they teach', function () {
    $professor = User::factory()->professor()->create();
    $course = Course::factory()->for($professor, 'professor')->create([
        'title' => 'Algorithmique avancée',
        'room' => 'Amphi B',
        'day_of_week' => Weekday::Monday,
    ]);

    $this->actingAs($professor);

    Livewire::test('pages::calendar')
        ->assertStatus(200)
        ->assertSee($course->title)
        ->assertSee($course->room);
});

test('professors do not see courses taught by another professor', function () {
    $professor = User::factory()->professor()->create();
    $otherProfessor = User::factory()->professor()->create();

    Course::factory()->for($professor, 'professor')->create([
        'title' => 'Algorithmique avancée',
        'day_of_week' => Weekday::Monday,
    ]);

    Course::factory()->for($otherProfessor, 'professor')->create([
        'title' => 'Réseaux informatiques',
        'day_of_week' => Weekday::Monday,
    ]);

    $this->actingAs($professor);

    Livewire::test('pages::calendar')
        ->assertSee('Algorithmique avancée')
        ->assertDontSee('Réseaux informatiques');
});

test('students see the courses they are enrolled in', function () {
    $student = User::factory()->student()->create();
    $course = Course::factory()->create([
        'title' => 'Bases de données',
        'day_of_week' => Weekday::Tuesday,
    ]);

    $course->students()->attach($student);

    $this->actingAs($student);

    Livewire::test('pages::calendar')
        ->assertStatus(200)
        ->assertSee($course->title)
        ->assertSee($course->professor->name);
});

test('students do not see courses they are not enrolled in', function () {
    $student = User::factory()->student()->create();

    Course::factory()->create([
        'title' => 'Bases de données',
        'day_of_week' => Weekday::Tuesday,
    ]);

    $this->actingAs($student);

    Livewire::test('pages::calendar')
        ->assertDontSee('Bases de données');
});
