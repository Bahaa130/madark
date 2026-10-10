<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Journey;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuizBuilderController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $quizzes = Quiz::query()
            ->whereHas('journey', fn ($q) => $q->where('instructor_id', $user->id))
            ->with(['journey:id,title', 'questions'])
            ->withCount(['questions', 'attempts'])
            ->orderByDesc('id')
            ->get();

        $journeys = Journey::query()
            ->where('instructor_id', $user->id)
            ->orderBy('title')
            ->get(['id', 'title']);

        return view('instructor.quizzes', [
            'quizzes' => $quizzes,
            'journeys' => $journeys,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'journey_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'min:3', 'max:190'],
            'description' => ['nullable', 'string', 'max:1000'],
            'passing_score' => ['nullable', 'integer', 'min:1', 'max:100'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.text' => ['required', 'string', 'min:3', 'max:1000'],
            'questions.*.options' => ['required', 'array', 'min:2', 'max:6'],
            'questions.*.options.*.text' => ['required', 'string', 'min:1', 'max:500'],
            'questions.*.correct' => ['required', 'integer', 'min:0'],
        ]);

        $journey = Journey::where('instructor_id', $user->id)->findOrFail($data['journey_id']);

        $quiz = DB::transaction(function () use ($data, $journey) {
            $quiz = Quiz::create([
                'journey_id' => $journey->id,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'passing_score' => (int) ($data['passing_score'] ?? 60),
            ]);

            foreach ($data['questions'] as $qIndex => $q) {
                $question = $quiz->questions()->create([
                    'text' => $q['text'],
                    'order' => $qIndex + 1,
                ]);

                foreach ($q['options'] as $oIndex => $o) {
                    $question->options()->create([
                        'text' => $o['text'],
                        'is_correct' => $oIndex === (int) $q['correct'],
                        'order' => $oIndex + 1,
                    ]);
                }
            }

            return $quiz;
        });

        return redirect()
            ->route('instructor.quizzes')
            ->with('success', __('builder.quizCreated'));
    }

    public function destroy(Request $request, $id)
    {
        $user = Auth::user();

        $quiz = Quiz::with('journey')->findOrFail($id);

        if ($user->role->value !== 'ADMIN' && (int) ($quiz->journey?->instructor_id ?? 0) !== (int) $user->id) {
            abort(403);
        }

        $quiz->delete();

        return back()->with('success', __('common.deleted'));
    }
}
