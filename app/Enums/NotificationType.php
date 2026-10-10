<?php

namespace App\Enums;

enum NotificationType: string
{
    case SYSTEM = 'SYSTEM';
    case JOURNEY = 'JOURNEY';
    case ACHIEVEMENT = 'ACHIEVEMENT';
    case FORUM = 'FORUM';
    case CERTIFICATE = 'CERTIFICATE';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}