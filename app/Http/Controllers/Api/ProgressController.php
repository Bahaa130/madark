<?php

namespace App\Http\Controllers\Api;

use App\Enums\EnrollmentStatus;
use App\Enums\NotificationType;
use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    /**
     * Mirrors POST /api/progress — records lesson completion and, once every
     * lesson in the journey is done, closes the enrollment and issues the
     * certificate + achievement + notification.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lessonId' => ['required', 'integer', 'exists:lessons,id'],
            'completed' => ['required', 'boolean'],
            'watchedSeconds' => ['nullable', 'integer', 'min:0'],
        ]);

        $user = $this->user();

        $lesson = Lesson::with('module')->findOrFail($data['lessonId']);
        $journeyId = $lesson->module->journey_id;

        // The learner must be enrolled in the journey that owns the lesson.
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('journey_id', $journeyId)
            ->first();

        if (! $enrollment) {
            return response()->json(['error' => 'not_enrolled'], 403);
        }

        // `completed_at` must always be written, otherwise un-completing a
        // lesson would leave the previous timestamp behind. `watched_seconds`
        // is only touched when the client actually sends it, because the
        // column is NOT NULL.
        $payload = [
            'completed' => $data['completed'],
            'completed_at' => $data['completed'] ? now() : null,
        ];

        if (array_key_exists('watchedSeconds', $data)) {
            $payload['watched_seconds'] = $data['watchedSeconds'];
        }

        LessonProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
            ],
            $payload
        );

        $certificateSerial = $this->maybeIssueCertificate($user, $journeyId, $enrollment);

        return response()->json([
            'ok' => true,
            'certificateSerial' => $certificateSerial,
        ]);
    }

    /**
     * Completes the enrollment and mints a certificate when the learner has
     * finished every lesson. Returns the serial when one was just issued.
     */
    private function maybeIssueCertificate(
        \App\Models\User $user,
        int $journeyId,
        Enrollment $enrollment
    ): ?string {
        if ($enrollment->status !== EnrollmentStatus::ACTIVE) {
            return null;
        }

        $total = Lesson::whereHas('module', fn ($q) => $q->where('journey_id', $journeyId))->count();

        if ($total === 0) {
            return null;
        }

        $done = LessonProgress::where('user_id', $user->id)
            ->where('completed', true)
            ->whereHas('lesson.module', fn ($q) => $q->where('journey_id', $journeyId))
            ->count();

        if ($done < $total) {
            return null;
        }

        $enrollment->update([
            'status' => EnrollmentStatus::COMPLETED,
            'completed_at' => now(),
        ]);

        // Do not re-issue if the learner already holds this certificate.
        $existing = $user->certificates()->where('journey_id', $journeyId)->first();

        if ($existing) {
            return null;
        }

        $serial = $this->makeSerial($journeyId);

        $user->certificates()->create([
            'serial' => $serial,
            'journey_id' => $journeyId,
        ]);

        $user->achievements()->create([
            'type' => 'JOURNEY_COMPLETED',
            'title' => 'Journey completed',
            'description' => 'Finished every lesson in a learning journey',
            'icon' => 'trophy',
        ]);

        $user->notifications()->create([
            'type' => NotificationType::CERTIFICATE,
            'title' => 'Certificate earned',
            'body' => 'Your certificate has been added to your wallet',
            'url' => route('student.profile'),
        ]);

        return $serial;
    }

    /**
     * MDK-<journey suffix>-<timestamp>, matching the reference format.
     */
    private function makeSerial(int $journeyId): string
    {
        $suffix = strtoupper(substr(str_pad((string) $journeyId, 6, '0', STR_PAD_LEFT), -6));

        return 'MDK-' . $suffix . '-' . strtoupper(base_convert((string) time(), 10, 36));
    }
}