<?php

namespace App\Http\Controllers;

use App\Models\Journey;
use Illuminate\View\View;

class ExploreController extends Controller
{
    public function __invoke(): View
    {
        $journeys = Journey::published()
            ->with(['modules.lessons', 'instructor'])
            ->withCount('enrollments')
            ->orderByDesc('duration_min')
            ->get()
            ->map(function (Journey $journey) {
                $journey->title = $journey->localizedTitle(app()->getLocale());
                $journey->lessons_count = $journey->lessonsCount();
                $journey->students_count = $journey->enrollments_count;

                return $journey;
            });

        return view('explore', [
            'journeys' => $journeys,
            'levels' => ['ALL', 'BEGINNER', 'INTERMEDIATE', 'ADVANCED', 'MASTER'],
        ]);
    }
}