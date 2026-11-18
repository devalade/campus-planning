<?php

use App\Models\SpecialDay;
use App\Models\User;

beforeEach(function () {
    // Nous sommes le lundi 9 novembre 2026.
    $this->travelTo('2026-11-09 08:00:00');
});

test('an admin can see the special days page', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get(route('admin.special-days'));

    $response->assertOk();
});

test('a professor cannot see the special days page', function () {
    $professor = User::factory()->professor()->create();

    $response = $this->actingAs($professor)->get(route('admin.special-days'));

    $response->assertForbidden();
});

test('an admin can add a special day', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post(route('admin.special-days.store'), [
        'date' => '2026-11-11',
        'label' => 'Journée pédagogique',
    ]);

    $this->assertDatabaseHas('special_days', ['date' => '2026-11-11', 'label' => 'Journée pédagogique']);
});

test('a special day needs a label', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post(route('admin.special-days.store'), [
        'date' => '2026-11-11',
        'label' => '',
    ]);

    $response->assertSessionHasErrors('label');
    $this->assertDatabaseCount('special_days', 0);
});

test('a special day cannot be in the past', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post(route('admin.special-days.store'), [
        'date' => '2026-11-08',
        'label' => 'Journée passée',
    ]);

    $response->assertSessionHasErrors('date');
    $this->assertDatabaseCount('special_days', 0);
});

test('the same date cannot be a special day twice', function () {
    $admin = User::factory()->admin()->create();
    SpecialDay::factory()->create(['date' => '2026-11-11']);

    $response = $this->actingAs($admin)->post(route('admin.special-days.store'), [
        'date' => '2026-11-11',
        'label' => 'Autre journée',
    ]);

    $response->assertSessionHasErrors('date');
    $this->assertDatabaseCount('special_days', 1);
});

test('an admin can delete a special day', function () {
    $admin = User::factory()->admin()->create();
    $specialDay = SpecialDay::factory()->create(['date' => '2026-11-11']);

    $this->actingAs($admin)->delete(route('admin.special-days.destroy', $specialDay));

    $this->assertDatabaseMissing('special_days', ['id' => $specialDay->id]);
});
