<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __invoke(): View
    {
        $user = $this->user();

        $profile = [
            'name' => $user->display_name,
            'image' => $user->image,
            'headline' => $user->headline,
            'bio' => $user->bio,
            'createdAt' => $user->created_at,
            'lessonsDone' => $user->lessonProgress()->where('completed', true)->count(),
            'enrollmentsCount' => $user->enrollments()->count(),
            'certificatesCount' => $user->certificates()->count(),
        ];

        return view('student.profile', [
            'profile' => $profile,
            'achievements' => $user->achievements()
                ->orderByDesc('earned_at')
                ->get(),
            'certificates' => $user->certificates()
                ->with('journey')
                ->orderByDesc('issued_at')
                ->get(),
        ]);
    }
}