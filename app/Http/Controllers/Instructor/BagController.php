<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Journey;
use App\Models\Lesson;
use App\Models\LessonAsset;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;

class BagController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $instructorId = $user?->id;

        $assets = LessonAsset::query()
            ->whereHas('lesson.module.journey', function ($q) use ($instructorId) {
                $q->where('instructor_id', $instructorId);
            })
            ->with([
                'lesson.module.journey' => fn ($q) => $q->select('id', 'title'),
                'lesson.module' => fn ($q) => $q->select('id', 'title', 'journey_id'),
                'lesson' => fn ($q) => $q->select('id', 'title', 'module_id'),
            ])
            ->orderByDesc('id')
            ->get();

        $assetsData = $assets->map(function ($asset) {
            return [
                'id' => (string) $asset->id,
                'name' => $asset->name,
                'url' => $asset->url,
                'sizeKb' => (int) ($asset->size_kb ?? 0),
                'mimeType' => $asset->mime_type,
                'extension' => $asset->extension,
                'lesson' => [
                    'title' => $asset->lesson?->title,
                    'module' => [
                        'title' => $asset->lesson?->module?->title,
                        'journey' => [
                            'id' => (string) ($asset->lesson?->module?->journey?->id ?? ''),
                            'title' => $asset->lesson?->module?->journey?->title,
                        ],
                    ],
                ],
            ];
        });

        return view('instructor.bag', [
            'assets' => $assetsData,
        ]);
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $instructorId = $user?->id;

        $journeys = Journey::query()
            ->where('instructor_id', $instructorId)
            ->orderBy('title')
            ->get(['id', 'title']);

        $modules = Module::query()
            ->whereHas('journey', fn ($q) => $q->where('instructor_id', $instructorId))
            ->orderBy('order')
            ->get(['id', 'title', 'journey_id']);

        $lessons = Lesson::query()
            ->whereHas('module.journey', fn ($q) => $q->where('instructor_id', $instructorId))
            ->orderBy('order')
            ->get(['id', 'title', 'module_id']);

        return view('instructor.bag-create', [
            'journeys' => $journeys,
            'modules' => $modules,
            'lessons' => $lessons,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $instructorId = $user?->id;

        $data = $request->validate([
            'lesson_id' => ['required', 'integer', 'exists:lessons,id'],
            'file' => [
                'required',
                File::types(['pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt', 'zip', 'mp4', 'webm', 'mp3', 'wav', 'jpg', 'jpeg', 'png', 'webp', 'gif'])
                    ->max(51200),
            ],
            'name' => ['nullable', 'string', 'max:190'],
        ]);

        $lesson = Lesson::with('module.journey')->findOrFail($data['lesson_id']);

        if ((int) ($lesson->module?->journey?->instructor_id ?? 0) !== (int) $instructorId) {
            abort(403);
        }

        $file = $request->file('file');
        $stored = $file->store('assets', 'public');
        $absolute = Storage::disk('public')->path($stored);
        $sizeKb = is_file($absolute) ? (int) ceil(filesize($absolute) / 1024) : 0;

        LessonAsset::create([
            'lesson_id' => $lesson->id,
            'name' => filled($data['name'] ?? null) ? $data['name'] : $file->getClientOriginalName(),
            'path' => $stored,
            'url' => Storage::disk('public')->url($stored),
            'mime_type' => $file->getMimeType() ?: $file->getClientMimeType(),
            'extension' => strtolower($file->getClientOriginalExtension()),
            'size_kb' => $sizeKb,
        ]);

        return redirect()
            ->route('instructor.bag')
            ->with('success', __('common.saved'));
    }

    public function destroy(Request $request, $id)
    {
        $user = Auth::user();
        $instructorId = $user?->id;

        $asset = LessonAsset::with('lesson.module.journey')->findOrFail($id);

        if ((int) ($asset->lesson?->module?->journey?->instructor_id ?? 0) !== (int) $instructorId) {
            abort(403);
        }

        if (filled($asset->path)) {
            Storage::disk('public')->delete($asset->path);
        }

        $asset->delete();

        return back()->with('success', __('common.deleted'));
    }
}
