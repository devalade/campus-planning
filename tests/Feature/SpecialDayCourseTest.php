<?php

use App\Enums\Weekday;
use App\Models\Course;
use App\Models\Group;
use App\Models\SpecialDay;
use App\Models\User;

beforeEach(function () {
    // Nous sommes le lundi 9 novembre 2026 : la semaine affichée va du 9 au 14.
    $this->travelTo('2026-11-09 08:00:00');
});

test('a professor cannot create a course on a special day', function () {
    $professor = User::factory()->professor()->create();
    $group = Group::factory()->create();
    SpecialDay::factory()->create(['date' => '2026-11-11']);

    $response = $this->actingAs($professor)->post(route('courses.store'), [
        'title' => 'Algorithmique',
        'room' => 'Amphi B',
        'day_of_week' => Weekday::Wednesday->value,
        'starts_at' => '08:00',
        'ends_at' => '10:00',
        'group_id' => $group->id,
    ]);

    $response->assertSessionHasErrors('day_of_week');
    $this->assertDatabaseCount('courses', 0);
});

test('a professor can create a course on another day of the same week', function () {
    $professor = User::factory()->professor()->create();
    $group = Group::factory()->create();
    SpecialDay::factory()->create(['date' => '2026-11-11']);

    $response = $this->actingAs($professor)->post(route('courses.store'), [
        'title' => 'Algorithmique',
        'room' => 'Amphi B',
        'day_of_week' => Weekday::Monday->value,
        'starts_at' => '08:00',
        'ends_at' => '10:00',
        'group_id' => $group->id,
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseCount('courses', 1);
});

test('a special day next week does not block this week', function () {
    $professor = User::factory()->professor()->create();
    $group = Group::factory()->create();
    SpecialDay::factory()->create(['date' => '2026-11-18']);

    $response = $this->actingAs($professor)->post(route('courses.store'), [
        'title' => 'Algorithmique',
        'room' => 'Amphi B',
        'day_of_week' => Weekday::Wednesday->value,
        'starts_at' => '08:00',
        'ends_at' => '10:00',
        'group_id' => $group->id,
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseCount('courses', 1);
});

test('a professor cannot move a course to a special day', function () {
    $professor = User::factory()->professor()->create();
    $course = Course::factory()->for($professor, 'professor')->create(['day_of_week' => Weekday::Monday]);
    SpecialDay::factory()->create(['date' => '2026-11-11']);

    $response = $this->actingAs($professor)->put(route('courses.update', $course), [
        'title' => $course->title,
        'room' => $course->room,
        'day_of_week' => Weekday::Wednesday->value,
        'starts_at' => '08:00',
        'ends_at' => '10:00',
        'group_id' => $course->group_id,
    ]);

    $response->assertSessionHasErrors('day_of_week');
    $this->assertDatabaseHas('courses', ['id' => $course->id, 'day_of_week' => Weekday::Monday->value]);
});

test('the calendar shows the special day', function () {
    $professor = User::factory()->professor()->create();
    SpecialDay::factory()->create(['date' => '2026-11-11', 'label' => 'Journée pédagogique']);

    $response = $this->actingAs($professor)->get(route('dashboard'));

    $response->assertSee('Journée pédagogique');
});
