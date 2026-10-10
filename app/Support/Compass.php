<?php

namespace App\Support;

use App\Models\Journey;
use Illuminate\Support\Collection;

class Compass
{
    public static function questions(): Collection
    {
        return collect(__('compass.questions'))->values();
    }

    public static function recommendations(int $take = 6): Collection
    {
        return Journey::published()
            ->orderByDesc('created_at')
            ->limit($take)
            ->get()
            ->map(fn (Journey $journey) => [
                'title' => $journey->localizedTitle(app()->getLocale()),
                'slug' => $journey->slug,
                'tagline' => $journey->tagline,
            ]);
    }
}