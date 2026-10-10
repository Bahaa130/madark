<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\Journey;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuizTakeController extends Controller
{
    public function show(Request $request, $id)
    {
        $user = Auth::user();

        $quiz = Quiz::with(['journey', 'questions.options'])
            ->whereHas('journey', fn ($q) => $q->published())
            ->findOrFail($id);

        $enrolled = $user->enrollments()
            ->where('journey_id', $quiz->journey_id)
            ->exists();

        if (! $enrolled) {
            return redirect()
                ->route('student.journey.show', $quiz->journey->slug)
                ->with('error', __('quiz.enrollFirst'));
        }

        $attempts = $quiz->attempts()
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return view('student.quiz-take', [
            'quiz' => $quiz,
            'attempts' => $attempts,
        ]);
    }

    public function store(Request $request, $id)
    {
        $user = Auth::user();

        $quiz = Quiz::with(['journey', 'questions.options'])
            ->whereHas('journey', fn ($q) => $q->published())
            ->findOrFail($id);

        $enrolled = $user->enrollments()
            ->where('journey_id', $quiz->journey_id)
            ->exists();

        if (! $enrolled) {
            abort(403);
        }

        $data = $request->validate([
            'answers' => ['required', 'array'],
            'answers.*' => ['nullable', 'integer'],
        ]);

        $questions = $quiz->questions;

        $correctCount = 0;
        foreach ($questions as $question) {
            $answerId = $data['answers'][(string) $question->id] ?? ($data['answers'][$question->id] ?? null);

            if ($answerId !== null && $question->options->contains(fn ($o) => (int) $o->id === (int) $answerId && $o->is_correct)) {
                $correctCount++;
            }
        }

        $total = $questions->count();
        $score = $total > 0 ? (int) round(($correctCount / $total) * 100) : 0;
        $passed = $score >= (int) $quiz->passing_score;

        $attempt = DB::transaction(function () use ($quiz, $user, $score, $passed, $data) {
            $attempt = QuizAttempt::create([
                'quiz_id' => $quiz->id,
                'user_id' => $user->id,
                'score' => $score,
                'passed' => $passed,
                'answers' => json_encode($data['answers']),
            ]);

            if ($passed) {
                Achievement::firstOrCreate([
                    'user_id' => $user->id,
                    'type' => 'QUIZ_PASS',
                    'title' => $quiz->title,
                ], [
                    'description' => __('quiz.achievementDesc'),
                    'icon' => 'trophy',
                    'earned_at' => now(),
                ]);
            }

            return $attempt;
        });

        return redirect()
            ->route('student.quiz.take', $quiz->id)
            ->with('attempt', [
                'score' => $score,
                'passed' => $passed,
                'correct' => $correctCount,
                'total' => $total,
            ]);
    }
}
