<?php

namespace App\Enums;

enum LibraryItemType: string
{
    case ARTICLE = 'ARTICLE';
    case BOOK = 'BOOK';
    case SUMMARY = 'SUMMARY';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}