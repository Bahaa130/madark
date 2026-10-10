<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Journey;
use App\Models\LessonAsset;
use App\Models\ForumThread;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $instructorId = $user?->id;

        $journeys = Journey::query()
            ->where('instructor_id', $instructorId)
            ->withCount(['enrollments', 'modules'])
            ->with(['modules.lessons'])
            ->get();

        $journeyIds = $journeys->pluck('id')->all();

        $students = $journeys->sum('enrollments_count');

        $totalLessons = 0;
        foreach ($journeys as $journey) {
            foreach ($journey->modules as $module) {
                $totalLessons += $module->lessons->count();
            }
        }

        $doneLessons = 0;
        if (! empty($journeyIds)) {
            $doneLessons = \App\Models\LessonProgress::query()
                ->where('completed', true)
                ->whereHas('lesson.module', function ($q) use ($journeyIds) {
                    $q->whereIn('journey_id', $journeyIds);
                })
                ->count();
        }

        $revenue = $journeys->sum(function ($journey) {
            $price = (float) ($journey->price ?? 0);
            return $journey->enrollments_count * $price;
        });

        $avgProgress = $totalLessons > 0 ? ($doneLessons / $totalLessons) * 100 : 0;

        $journeysRows = $journeys->map(function ($journey) use ($journeyIds) {
            $lessonsCount = 0;
            foreach ($journey->modules as $module) {
                $lessonsCount += $module->lessons->count();
            }

            $doneForJourney = 0;
            $doneForJourney = \App\Models\LessonProgress::query()
                ->where('completed', true)
                ->whereHas('lesson.module', function ($q) use ($journey) {
                    $q->where('journey_id', $journey->id);
                })
                ->count();

            $progressPercent = $lessonsCount > 0 ? ($doneForJourney / $lessonsCount) * 100 : 0;

            return [
                'id' => (string) $journey->id,
                'title' => $journey->title,
                'status' => $journey->status?->value ?? (string) $journey->status,
                'level' => $journey->level?->value ?? (string) $journey->level,
                'lessonsCount' => $lessonsCount,
                'enrollments' => $journey->enrollments_count,
                'progressPercent' => (float) $progressPercent,
            ];
        });

        return view('instructor.dashboard', [
            'instructorName' => $user?->name ?? __('nav.instructor.dashboard'),
            'stats' => [
                'journeys' => $journeys->count(),
                'students' => $students,
                'avgProgress' => (float) $avgProgress,
                'revenue' => (int) round($revenue),
            ],
            'journeys' => $journeysRows,
        ]);
    }
}