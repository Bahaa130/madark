<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Journey;
use App\Support\Orbit;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class JourneyController extends Controller
{
    public function index(): View
    {
        $user = $this->user();

        $enrolled = Orbit::enrolledJourneys($user);

        return view('student.paths', [
            'active' => $enrolled->filter(fn ($j) => $j->enrollment->status->value === 'ACTIVE')->values(),
            'completed' => $enrolled->filter(fn ($j) => $j->enrollment->status->value === 'COMPLETED')->values(),
        ]);
    }

    public function show(string $slug): View|RedirectResponse
    {
        $user = $this->user();

        $journey = Journey::published()
            ->where('slug', $slug)
            ->with(['instructor', 'modules.lessons'])
            ->firstOrFail();

        $total = $journey->lessonsCount();

        $done = $user->lessonProgress()
            ->where('completed', true)
            ->whereHas('lesson.module', fn ($q) => $q->where('journey_id', $journey->id))
            ->count();

        $enrollment = $user->enrollments()
            ->where('journey_id', $journey->id)
            ->first();

        $journey->title = $journey->localizedTitle(app()->getLocale());
        $journey->lessons_count = $total;
        $journey->progress_percent = $total > 0 ? ($done / $total) * 100 : 0;
        $journey->lessons_done = $done;

        return view('student.journey', [
            'journey' => $journey,
            'enrollment' => $enrollment,
        ]);
    }
}