<?php

namespace App\Support;

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\Journey;
use App\Models\User;
use Illuminate\Support\Collection;

class Orbit
{
    /**
     * Mirrors getOrbitStats(): active paths, completed lessons, certificates
     * and the consecutive-day streak ending today (or yesterday).
     */
    public static function stats(User $user): array
    {
        $activePaths = Enrollment::where('user_id', $user->id)
            ->where('status', EnrollmentStatus::ACTIVE)
            ->count();

        $completedAt = $user->lessonProgress()
            ->where('completed', true)
            ->orderByDesc('completed_at')
            ->limit(60)
            ->pluck('completed_at')
            ->filter()
            ->map(fn ($date) => \Illuminate\Support\Carbon::parse($date)->toDateString())
            ->unique();

        return [
            'activePaths' => $activePaths,
            'lessonsDone' => $user->lessonProgress()->where('completed', true)->count(),
            'certificates' => $user->certificates()->count(),
            'streak' => self::streak($completedAt),
        ];
    }

    /**
     * @param  Collection<int, string>  $days  yyyy-mm-dd strings
     */
    private static function streak(Collection $days): int
    {
        if ($days->isEmpty()) {
            return 0;
        }

        $cursor = now()->startOfDay();

        // A streak may still be alive if the learner has not studied today.
        if (! $days->contains($cursor->toDateString())) {
            $cursor = $cursor->subDay();
        }

        $streak = 0;

        while ($days->contains($cursor->toDateString())) {
            $streak++;
            $cursor = $cursor->subDay();
        }

        return $streak;
    }

    /**
     * Mirrors getContinueJourney(): the most recent active enrollment, with
     * its computed progress percentage.
     */
    public static function continueJourney(User $user): ?object
    {
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('status', EnrollmentStatus::ACTIVE)
            ->orderByDesc('enrolled_at')
            ->with(['journey' => fn ($q) => $q->with(['instructor', 'modules.lessons'])])
            ->first();

        if (! $enrollment) {
            return null;
        }

        $journey = $enrollment->journey;
        $total = $journey->lessonsCount();

        $done = $user->lessonProgress()
            ->where('completed', true)
            ->whereHas('lesson.module', fn ($q) => $q->where('journey_id', $journey->id))
            ->count();

        $journey->title = $journey->localizedTitle(app()->getLocale());
        $journey->lessons_count = $total;
        $journey->progress_percent = $total > 0 ? ($done / $total) * 100 : 0;

        return $journey;
    }

    /**
     * @return Collection<int, object>
     */
    public static function notifications(User $user, int $take = 8): Collection
    {
        return $user->notifications()
            ->orderByDesc('created_at')
            ->limit($take)
            ->get();
    }

    /**
     * Journeys the learner is enrolled in, for the paths page.
     */
    public static function enrolledJourneys(User $user): Collection
    {
        return Enrollment::where('user_id', $user->id)
            ->with(['journey' => fn ($q) => $q->with('modules.lessons')])
            ->orderByDesc('enrolled_at')
            ->get()
            ->map(function (Enrollment $enrollment) use ($user) {
                $journey = $enrollment->journey;
                $total = $journey->lessonsCount();

                $done = $user->lessonProgress()
                    ->where('completed', true)
                    ->whereHas('lesson.module', fn ($q) => $q->where('journey_id', $journey->id))
                    ->count();

                $journey->title = $journey->localizedTitle(app()->getLocale());
                $journey->lessons_count = $total;
                $journey->progress_percent = $total > 0 ? ($done / $total) * 100 : 0;
                $journey->enrollment = $enrollment;

                return $journey;
            });
    }
}