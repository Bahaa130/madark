<?php

namespace App\Enums;

enum EnrollmentStatus: string
{
    case ACTIVE = 'ACTIVE';
    case COMPLETED = 'COMPLETED';
    case PAUSED = 'PAUSED';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}