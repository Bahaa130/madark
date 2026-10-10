<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\ForumThread;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CouncilController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $instructorId = $user?->id;

        $threads = ForumThread::query()
            ->whereHas('journey', function ($q) use ($instructorId) {
                $q->where('instructor_id', $instructorId);
            })
            ->with(['author:id,name,image', 'journey:id,title'])
            ->withCount('posts')
            ->orderByDesc('pinned')
            ->orderByDesc('updated_at')
            ->limit(30)
            ->get();

        $threadsData = $threads->map(function ($thread) {
            return [
                'id' => (string) $thread->id,
                'title' => $thread->title,
                'pinned' => (bool) $thread->pinned,
                'locked' => (bool) $thread->locked,
                'updatedAt' => optional($thread->updated_at)->toISOString() ?? now()->toISOString(),
                'author' => [
                    'name' => $thread->author?->name,
                    'image' => $thread->author?->image,
                ],
                'journey' => $thread->journey ? [
                    'title' => $thread->journey->title,
                ] : null,
                'postsCount' => $thread->posts_count ?? 0,
            ];
        });

        return view('instructor.council', [
            'threads' => $threadsData,
        ]);
    }
}