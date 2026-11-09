<?php

use App\Enums\Role;
use App\Models\User;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();

    expect(User::query()->where('email', 'test@example.com')->sole()->role)->toBe(Role::Student);
});

test('new users can register as professors', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Awa Sossou',
        'email' => 'awa@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => Role::Professor->value,
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    expect(User::query()->where('email', 'awa@example.com')->sole()->role)->toBe(Role::Professor);
});
