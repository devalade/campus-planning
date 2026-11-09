<?php

namespace App\Enums;

enum CourseChange: string
{
    case Created = 'created';
    case Updated = 'updated';
    case Cancelled = 'cancelled';
}
