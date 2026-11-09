<?php

use Illuminate\Database\Eloquent\Factories\HasFactory;

arch()->preset()->laravel();

arch()->preset()->security();

arch('models use factories')
    ->expect('App\Models')
    ->toUse(HasFactory::class);

arch('models do not depend on the HTTP layer or send notifications')
    ->expect('App\Models')
    ->not->toUse([
        'Illuminate\Http',
        'Illuminate\Notifications\Notification',
        'Illuminate\Support\Facades\Notification',
    ]);

arch('notifications provide an email representation')
    ->expect('App\Notifications')
    ->toHaveMethod('toMail');

arch('enums stay framework agnostic')
    ->expect('App\Enums')
    ->not->toUse('Illuminate');
