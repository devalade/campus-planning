<?php

namespace App\Enums;

use Carbon\CarbonInterface;

enum Weekday: int
{
    case Monday = 1;
    case Tuesday = 2;
    case Wednesday = 3;
    case Thursday = 4;
    case Friday = 5;
    case Saturday = 6;
    case Sunday = 7;

    /**
     * Get the full French label for the weekday.
     */
    public function label(): string
    {
        return match ($this) {
            self::Monday => 'Lundi',
            self::Tuesday => 'Mardi',
            self::Wednesday => 'Mercredi',
            self::Thursday => 'Jeudi',
            self::Friday => 'Vendredi',
            self::Saturday => 'Samedi',
            self::Sunday => 'Dimanche',
        };
    }

    /**
     * Get the short French label for the weekday.
     */
    public function shortLabel(): string
    {
        return match ($this) {
            self::Monday => 'Lun.',
            self::Tuesday => 'Mar.',
            self::Wednesday => 'Mer.',
            self::Thursday => 'Jeu.',
            self::Friday => 'Ven.',
            self::Saturday => 'Sam.',
            self::Sunday => 'Dim.',
        };
    }

    /**
     * Determine the weekday for a given date.
     */
    public static function fromDate(CarbonInterface $date): self
    {
        return self::from($date->isoWeekday());
    }

    /**
     * Get the weekdays that can be scheduled in the calendar.
     *
     * @return list<self>
     */
    public static function schedulable(): array
    {
        return [
            self::Monday,
            self::Tuesday,
            self::Wednesday,
            self::Thursday,
            self::Friday,
            self::Saturday,
        ];
    }
}
