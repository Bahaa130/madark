<?php

namespace App\Http\Controllers\Api;

use App\Enums\JourneyStatus;
use App\Enums\NotificationType;
use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Journey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnrollController extends Controller
{
    /**
     * Mirrors POST /api/enroll — idempotent join plus a notification.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'journeyId' => ['required', 'integer', 'exists:journeys,id'],
        ]);

        $journey = Journey::findOrFail($data['journeyId']);

        if ($journey->status !== JourneyStatus::PUBLISHED) {
            return response()->json(['error' => 'not_published'], 403);
        }

        $enrollment = Enrollment::firstOrCreate(
            [
                'user_id' => $this->user()->id,
                'journey_id' => $journey->id,
            ]
        );

        $this->user()->notifications()->create([
            'type' => NotificationType::JOURNEY,
            'title' => 'Journey joined',
            'body' => $journey->title,
            'url' => route('student.journey.show', $journey->slug),
        ]);

        return response()->json([
            'ok' => true,
            'enrollmentId' => $enrollment->id,
        ]);
    }
}