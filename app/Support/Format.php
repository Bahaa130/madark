<?php

namespace App\Support;

use BackedEnum;
use DateTimeInterface;
use Illuminate\Support\Carbon;

class Format
{
    /**
     * Mirrors the reference `formatDuration(minutes, lang)` helper.
     */
    public static function duration(int $minutes, ?string $lang = null): string
    {
        $lang ??= app()->getLocale();

        if ($minutes < 60) {
            return $lang === 'ar' ? "{$minutes} د" : "{$minutes}m";
        }

        $hours = intdiv($minutes, 60);
        $rest = $minutes % 60;

        return $lang === 'ar'
            ? trim("{$hours} س" . ($rest ? " {$rest} د" : ''))
            : trim("{$hours}h" . ($rest ? " {$rest}m" : ''));
    }

    public static function date(DateTimeInterface|string|null $date, ?string $lang = null): string
    {
        if ($date === null || $date === '') {
            return '—';
        }

        $lang ??= app()->getLocale();

        $moment = $date instanceof DateTimeInterface
            ? Carbon::instance($date)
            : Carbon::parse($date);

        return $moment->locale($lang === 'ar' ? 'ar' : 'en')->translatedFormat('j M Y');
    }

    /**
     * Mirrors the reference `slugify` helper (Unicode-aware).
     */
    public static function slug(string $input, string $fallback = 'journey'): string
    {
        $slug = preg_replace('/[^\p{L}\p{N}]+/u', '-', mb_strtolower(trim($input), 'UTF-8'));
        $slug = trim((string) $slug, '-');

        return mb_substr($slug !== '' ? $slug : $fallback, 0, 80, 'UTF-8');
    }

    /**
     * Compact number formatting for stat tiles: 1200 → 1.2K.
     */
    public static function compact(int $number, ?string $lang = null): string
    {
        $lang ??= app()->getLocale();

        if ($number < 1000) {
            return (string) $number;
        }

        $suffix = $lang === 'ar'
            ? ['ألف', 'مليون']
            : ['K', 'M'];

        if ($number < 1_000_000) {
            return rtrim(rtrim(number_format($number / 1000, 1), '0'), '.') . ' ' . $suffix[0];
        }

        return rtrim(rtrim(number_format($number / 1_000_000, 1), '0'), '.') . ' ' . $suffix[1];
    }

    /**
     * Localised enum label lookup, e.g. levels.BEGINNER → مبتدئ.
     */
    public static function level(string|BackedEnum|null $level): ?string
    {
        if ($level === null) {
            return null;
        }

        $value = $level instanceof BackedEnum ? $level->value : $level;
        $key = 'levels.' . strtoupper((string) $value);

        $label = __($key);

        return $label === $key ? (string) $value : $label;
    }
}