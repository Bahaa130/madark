<?php

namespace App\Enums;

enum Role: string
{
    case STUDENT = 'STUDENT';
    case INSTRUCTOR = 'INSTRUCTOR';
    case ADMIN = 'ADMIN';

    public function label(): string
    {
        return match ($this) {
            self::STUDENT => 'طالب',
            self::INSTRUCTOR => 'مدرّس',
            self::ADMIN => 'مدير',
        };
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}