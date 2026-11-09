<?php

namespace App\Enums;

enum Room: string
{
    case AmphiA = 'Amphi A';
    case AmphiB = 'Amphi B';
    case AmphiC = 'Amphi C';
    case Salle01 = 'Salle 01';
    case Salle05 = 'Salle 05';
    case Salle07 = 'Salle 07';
    case Salle12 = 'Salle 12';
    case Salle21 = 'Salle 21';
    case Salle24 = 'Salle 24';
    case Salle30 = 'Salle 30';
    case EnLigne = 'En ligne';

    /**
     * Get the values of every available room.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $room): string => $room->value, self::cases());
    }
}
