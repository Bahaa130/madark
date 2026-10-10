<?php

namespace App\Http\Controllers\Student;

use App\Enums\InstructorStatus;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class LighthouseController extends Controller
{
    public function __invoke(): View
    {
        $instructors = User::query()
            ->where('role', Role::INSTRUCTOR->value)
            ->with('instructorProfile')
            ->withCount('journeys')
            ->orderBy('name')
            ->limit(24)
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->display_name,
                'image' => $user->image,
                'headline' => $user->headline,
                'bio' => $user->bio,
                'verified' => $user->instructorProfile?->status === InstructorStatus::VERIFIED,
                'journeysCount' => $user->journeys_count,
            ]);

        return view('student.lighthouse', [
            'instructors' => $instructors,
        ]);
    }
}