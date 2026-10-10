<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Support\Compass;
use Illuminate\View\View;

class CompassController extends Controller
{
    public function __invoke(): View
    {
        return view('student.compass', [
            'questions' => Compass::questions(),
            'recommendations' => Compass::recommendations(),
        ]);
    }
}