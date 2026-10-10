<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InstructorProfile;
use App\Models\Journey;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ControlCenterController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $role = $user?->role?->value ?? (string) $user?->role;

        $users = User::query()
            ->select(['id', 'name', 'email', 'image', 'role', 'created_at'])
            ->withCount('enrollments')
            ->orderByDesc('created_at')
            ->limit(40)
            ->get();

        $usersData = $users->map(function ($u) {
            $roleVal = $u->role?->value ?? (string) $u->role;
            return [
                'id' => (string) $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'image' => $u->image,
                'role' => $roleVal,
                'createdAt' => optional($u->created_at)->toISOString() ?? now()->toISOString(),
                'enrollmentsCount' => $u->enrollments_count ?? 0,
            ];
        });

        $pendingInstructors = InstructorProfile::query()
            ->where('status', \App\Enums\InstructorStatus::PENDING)
            ->with(['user:id,name,email,image,headline'])
            ->get();

        $pendingInstructorsData = $pendingInstructors->map(function ($p) {
            return [
                'id' => (string) $p->id,
                'user' => $p->user ? [
                    'id' => (string) $p->user->id,
                    'name' => $p->user->name,
                    'email' => $p->user->email,
                    'image' => $p->user->image,
                    'headline' => $p->user->headline,
                ] : null,
            ];
        });

        $pendingJourneys = Journey::query()
            ->where('status', \App\Enums\JourneyStatus::PENDING_REVIEW)
            ->with(['instructor:id,name,image'])
            ->withCount(['modules', 'enrollments'])
            ->get();

        $pendingJourneysData = $pendingJourneys->map(function ($j) {
            $levelVal = $j->level?->value ?? (string) $j->level;
            return [
                'id' => (string) $j->id,
                'title' => $j->title,
                'level' => $levelVal,
                'coverImage' => $j->cover_image,
                'instructor' => $j->instructor ? [
                    'name' => $j->instructor->name,
                    'image' => $j->instructor->image,
                ] : null,
                'modulesCount' => $j->modules_count ?? 0,
                'enrollmentsCount' => $j->enrollments_count ?? 0,
            ];
        });

        $totalUsers = User::count();
        $totalJourneys = Journey::count();
        $publishedJourneys = Journey::query()->where('status', \App\Enums\JourneyStatus::PUBLISHED)->count();

        return view('admin.control-center', [
            'data' => [
                'users' => $usersData,
                'pendingInstructors' => $pendingInstructorsData,
                'pendingJourneys' => $pendingJourneysData,
                'totals' => [
                    'users' => $totalUsers,
                    'journeys' => $totalJourneys,
                    'published' => $publishedJourneys,
                ],
            ],
        ]);
    }
}