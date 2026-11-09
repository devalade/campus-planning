<?php

test('enums are backed and use TitleCase cases', function () {
    $enumFiles = glob(__DIR__.'/../../app/Enums/*.php') ?: [];

    expect($enumFiles)->not->toBeEmpty();

    foreach ($enumFiles as $enumFile) {
        $enumClass = 'App\\Enums\\'.basename($enumFile, '.php');
        $enum = new ReflectionEnum($enumClass);

        expect($enum->isBacked())
            ->toBeTrue();

        foreach ($enum->getCases() as $case) {
            expect($case->getName())
                ->toMatch('/^[A-Z][A-Za-z0-9]*$/');
        }
    }
});
