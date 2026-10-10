<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Journey;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'journeyId' => ['required', 'integer'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $user = Auth::user();

        $journey = Journey::findOrFail($data['journeyId']);

        $enrolled = $user->enrollments()
            ->where('journey_id', $journey->id)
            ->exists();

        if (! $enrolled) {
            return response()->json([
                'message' => __('review.enrollFirst'),
            ], 403);
        }

        $review = Review::updateOrCreate(
            ['user_id' => $user->id, 'journey_id' => $journey->id],
            ['rating' => (int) $data['rating'], 'comment' => $data['comment'] ?? null]
        );

        $avg = (float) $journey->reviews()->avg('rating');

        return response()->json([
            'ok' => true,
            'review' => [
                'id' => (string) $review->id,
                'rating' => (int) $review->rating,
                'comment' => $review->comment,
            ],
            'average' => round($avg, 1),
        ]);
    }
}
