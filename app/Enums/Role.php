<?php

namespace App\Enums;

enum Role: string
{
    case Professor = 'professor';
    case Student = 'student';

    /**
     * Get the human-readable label for the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::Professor => 'Professeur',
            self::Student => 'Étudiant',
        };
    }
}
