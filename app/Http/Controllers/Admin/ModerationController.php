<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InstructorStatus;
use App\Enums\JourneyStatus;
use App\Enums\NotificationType;
use App\Http\Controllers\Controller;
use App\Models\InstructorProfile;
use App\Models\Journey;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModerationController extends Controller
{
    /** Approve a pending journey. */
    public function approveJourney(Request $request, $id)
    {
        $journey = Journey::findOrFail($id);

        $journey->update(['status' => JourneyStatus::PUBLISHED]);

        // Notify the instructor.
        Notification::create([
            'user_id' => $journey->instructor_id,
            'type' => NotificationType::JOURNEY,
            'title' => __('notifs.journeyApproved'),
            'body' => $journey->title,
            'url' => '/instructor/journeys/'.$journey->id,
        ]);

        return back()->with('success', __('notifs.journeyApproved'));
    }

    /** Reject a pending journey (back to draft). */
    public function rejectJourney(Request $request, $id)
    {
        $journey = Journey::findOrFail($id);

        $journey->update(['status' => JourneyStatus::DRAFT]);

        Notification::create([
            'user_id' => $journey->instructor_id,
            'type' => NotificationType::JOURNEY,
            'title' => __('notifs.journeyRejected'),
            'body' => $journey->title,
            'url' => '/instructor/journeys/'.$journey->id,
        ]);

        return back()->with('success', __('notifs.journeyRejected'));
    }

    /** Verify a pending instructor. */
    public function verifyInstructor(Request $request, $id)
    {
        $profile = InstructorProfile::findOrFail($id);

        $profile->update([
            'status' => InstructorStatus::VERIFIED,
            'verified_at' => now(),
        ]);

        Notification::create([
            'user_id' => $profile->user_id,
            'type' => NotificationType::SYSTEM,
            'title' => __('notifs.instructorVerified'),
            'url' => '/instructor',
        ]);

        return back()->with('success', __('notifs.instructorVerified'));
    }

    /** Reject a pending instructor. */
    public function rejectInstructor(Request $request, $id)
    {
        $profile = InstructorProfile::findOrFail($id);

        $profile->update([
            'status' => InstructorStatus::REJECTED,
            'verified_at' => null,
        ]);

        return back()->with('success', __('notifs.instructorRejected'));
    }
}
