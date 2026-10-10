<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Support\Orbit;
use Illuminate\View\View;

class OrbitController extends Controller
{
    public function __invoke(): View
    {
        $user = $this->user();

        return view('student.orbit', [
            'stats' => Orbit::stats($user),
            'continueJourney' => Orbit::continueJourney($user),
            'notifications' => Orbit::notifications($user),
        ]);
    }
}