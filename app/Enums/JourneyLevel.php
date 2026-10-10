<?php

namespace App\Enums;

enum JourneyLevel: string
{
    case BEGINNER = 'BEGINNER';
    case INTERMEDIATE = 'INTERMEDIATE';
    case ADVANCED = 'ADVANCED';
    case MASTER = 'MASTER';

    public function labelAr(): string
    {
        return match ($this) {
            self::BEGINNER => 'مبتدئ',
            self::INTERMEDIATE => 'متوسط',
            self::ADVANCED => 'متقدم',
            self::MASTER => 'محترف',
        };
    }

    public function labelEn(): string
    {
        return match ($this) {
            self::BEGINNER => 'Beginner',
            self::INTERMEDIATE => 'Intermediate',
            self::ADVANCED => 'Advanced',
            self::MASTER => 'Master',
        };
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}