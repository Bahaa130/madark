<?php

namespace App\Http\Controllers\Instructor;

use App\Enums\JourneyLevel;
use App\Enums\JourneyStatus;
use App\Http\Controllers\Controller;
use App\Models\Journey;
use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class JourneyBuilderController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $journeys = Journey::query()
            ->where('instructor_id', $user->id)
            ->withCount(['enrollments', 'modules', 'lessons as lessons_count'])
            ->orderByDesc('updated_at')
            ->get();

        return view('instructor.journeys', [
            'journeys' => $journeys,
        ]);
    }

    public function create(Request $request)
    {
        return view('instructor.journey-create');
    }

    public function store(Request $request)
    {
        $data = $this->validateJourney($request);

        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['instructor_id'] = Auth::id();
        $data['status'] = JourneyStatus::DRAFT;

        $journey = Journey::create($data);

        return redirect()
            ->route('instructor.builder', $journey)
            ->with('success', __('builder.created'));
    }

    public function edit(Request $request, $id)
    {
        $journey = $this->ownJourney($id);

        $journey->load(['modules.lessons.resources']);

        return view('instructor.builder', [
            'journey' => $journey,
        ]);
    }

    public function update(Request $request, $id)
    {
        $journey = $this->ownJourney($id);

        $data = $this->validateJourney($request, updating: true);

        $journey->update($data);

        return redirect()
            ->route('instructor.builder', $journey)
            ->with('success', __('common.saved'));
    }

    /** Submit for admin review. */
    public function submit(Request $request, $id)
    {
        $journey = $this->ownJourney($id);

        if ($journey->lessons()->count() === 0) {
            return back()->with('error', __('builder.needsLesson'));
        }

        $journey->update(['status' => JourneyStatus::PENDING_REVIEW]);

        return back()->with('success', __('builder.submitted'));
    }

    public function destroy(Request $request, $id)
    {
        $journey = $this->ownJourney($id);

        $journey->delete();

        return redirect()->route('instructor.journeys')->with('success', __('common.deleted'));
    }

    // ─────────────── Modules ───────────────

    public function storeModule(Request $request, $id)
    {
        $journey = $this->ownJourney($id);

        $data = $request->validate([
            'title' => ['required', 'string', 'min:2', 'max:190'],
        ]);

        $order = (int) $journey->modules()->max('order') + 1;

        $module = $journey->modules()->create([
            'title' => $data['title'],
            'order' => $order,
        ]);

        return back()->with('success', __('builder.sectionAdded'));
    }

    public function updateModule(Request $request, $moduleId)
    {
        $module = Module::with('journey')->findOrFail($moduleId);
        $this->authorizeJourney($module->journey);

        $data = $request->validate([
            'title' => ['required', 'string', 'min:2', 'max:190'],
        ]);

        $module->update(['title' => $data['title']]);

        return back()->with('success', __('common.saved'));
    }

    public function destroyModule(Request $request, $moduleId)
    {
        $module = Module::with('journey')->findOrFail($moduleId);
        $this->authorizeJourney($module->journey);

        $module->delete();

        return back()->with('success', __('common.deleted'));
    }

    // ─────────────── Lessons ───────────────

    public function storeLesson(Request $request, $moduleId)
    {
        $module = Module::with('journey')->findOrFail($moduleId);
        $this->authorizeJourney($module->journey);

        $data = $this->validateLesson($request);

        $order = (int) $module->lessons()->max('order') + 1;

        $module->lessons()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'video_url' => $data['video_url'] ?? null,
            'duration_min' => (int) ($data['duration_min'] ?? 0),
            'is_preview' => (bool) ($data['is_preview'] ?? false),
            'order' => $order,
        ]);

        $this->refreshDuration($module->journey);

        return back()->with('success', __('builder.lessonAdded'));
    }

    public function updateLesson(Request $request, $lessonId)
    {
        $lesson = Lesson::with('module.journey')->findOrFail($lessonId);
        $this->authorizeJourney($lesson->module->journey);

        $data = $this->validateLesson($request, updating: true);

        $lesson->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'video_url' => $data['video_url'] ?? null,
            'duration_min' => (int) ($data['duration_min'] ?? 0),
            'is_preview' => (bool) ($data['is_preview'] ?? false),
        ]);

        $this->refreshDuration($lesson->module->journey);

        return back()->with('success', __('common.saved'));
    }

    public function destroyLesson(Request $request, $lessonId)
    {
        $lesson = Lesson::with('module.journey')->findOrFail($lessonId);
        $this->authorizeJourney($lesson->module->journey);

        $journey = $lesson->module->journey;
        $lesson->delete();
        $this->refreshDuration($journey);

        return back()->with('success', __('common.deleted'));
    }

    // ─────────────── Helpers ───────────────

    private function validateJourney(Request $request, bool $updating = false): array
    {
        $rules = [
            'title' => ['required', 'string', 'min:3', 'max:190'],
            'title_en' => ['nullable', 'string', 'max:190'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'level' => ['required', 'string', 'in:BEGINNER,INTERMEDIATE,ADVANCED,MASTER'],
            'language' => ['required', 'string', 'in:ar,en'],
            'is_free' => ['required', 'boolean'],
            'price' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'cover_image' => ['nullable', 'url', 'max:500'],
        ];

        $data = $request->validate($rules);

        $data['is_free'] = $request->boolean('is_free');
        $data['price'] = $data['is_free'] ? 0 : (int) ($data['price'] ?? 0);
        unset($data['cover_image']);
        if (filled($request->input('cover_image'))) {
            $data['cover_image'] = $request->input('cover_image');
        }

        return $data;
    }

    private function validateLesson(Request $request, bool $updating = false): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'min:2', 'max:190'],
            'description' => ['nullable', 'string', 'max:3000'],
            'video_url' => ['nullable', 'url', 'max:500'],
            'duration_min' => ['nullable', 'integer', 'min:0', 'max:600'],
            'is_preview' => ['nullable', 'boolean'],
        ]);
    }

    private function ownJourney($id): Journey
    {
        $journey = Journey::findOrFail($id);
        $this->authorizeJourney($journey);

        return $journey;
    }

    private function authorizeJourney(?Journey $journey): void
    {
        $user = Auth::user();

        if (! $journey) {
            abort(404);
        }

        if ($user->role->value === 'ADMIN') {
            return;
        }

        if ((int) $journey->instructor_id !== (int) $user->id) {
            abort(403);
        }
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'journey';
        $slug = $base;
        $i = 1;

        while (Journey::where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }

    private function refreshDuration(?Journey $journey): void
    {
        if (! $journey) {
            return;
        }

        $total = (int) $journey->lessons()->sum('duration_min');

        $journey->update(['duration_min' => $total]);
    }
}
