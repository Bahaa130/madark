<?php

use App\Http\Controllers\Admin\ControlCenterController;
use App\Http\Controllers\Admin\ModerationController;
use App\Http\Controllers\Api\EnrollController;
use App\Http\Controllers\Api\ProgressController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ExploreController;
use App\Http\Controllers\Instructor\BagController;
use App\Http\Controllers\Instructor\CouncilController;
use App\Http\Controllers\Instructor\DashboardController as InstructorDashboardController;
use App\Http\Controllers\Instructor\JourneyBuilderController;
use App\Http\Controllers\Instructor\QuizBuilderController;
use App\Http\Controllers\Student\CompassController;
use App\Http\Controllers\Student\CosmosController;
use App\Http\Controllers\Student\JourneyController;
use App\Http\Controllers\Student\LighthouseController;
use App\Http\Controllers\Student\OrbitController;
use App\Http\Controllers\Student\ProfileController;
use App\Http\Controllers\Student\QuizTakeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => view('welcome'))->name('home');

Route::get('/explore', ExploreController::class)->name('explore');

/*
|--------------------------------------------------------------------------
| Guest only
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
});

/*
|--------------------------------------------------------------------------
| Authenticated — role aware landing
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::post('/logout', LogoutController::class)->name('logout');

    Route::get('/dashboard', function () {
        return match (auth()->user()->role->value) {
            'ADMIN' => redirect()->route('admin.control'),
            'INSTRUCTOR' => redirect()->route('instructor.dashboard'),
            default => redirect()->route('student.orbit'),
        };
    })->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| Student Space
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:STUDENT,INSTRUCTOR,ADMIN'])->prefix('student')->name('student.')->group(function () {
    Route::get('/', OrbitController::class)->name('orbit');

    Route::get('/paths', [JourneyController::class, 'index'])->name('paths');
    Route::get('/paths/{slug}', [JourneyController::class, 'show'])->name('journey.show');

    Route::get('/cosmos', CosmosController::class)->name('cosmos');
    Route::get('/lighthouse', LighthouseController::class)->name('lighthouse');
    Route::get('/profile', ProfileController::class)->name('profile');
    Route::get('/compass', CompassController::class)->name('compass');

    Route::get('/quizzes/{id}', [QuizTakeController::class, 'show'])->name('quiz.take');
    Route::post('/quizzes/{id}', [QuizTakeController::class, 'store'])->name('quiz.submit');
});

/*
|--------------------------------------------------------------------------
| API (session authenticated, CSRF protected)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->prefix('api')->name('api.')->group(function () {
    Route::post('/enroll', [EnrollController::class, 'store'])->name('enroll');
    Route::post('/progress', [ProgressController::class, 'store'])->name('progress');
    Route::post('/review', [ReviewController::class, 'store'])->name('review');
});/*
|--------------------------------------------------------------------------
| Instructor Space
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:INSTRUCTOR,ADMIN'])->prefix('instructor')->name('instructor.')->group(function () {
    Route::get('/', [InstructorDashboardController::class, 'index'])->name('dashboard');

    // Journey builder (Udemy-grade)
    Route::get('/journeys', [JourneyBuilderController::class, 'index'])->name('journeys');
    Route::get('/journeys/create', [JourneyBuilderController::class, 'create'])->name('journeys.create');
    Route::post('/journeys', [JourneyBuilderController::class, 'store'])->name('journeys.store');
    Route::get('/journeys/{id}', [JourneyBuilderController::class, 'edit'])->name('builder');
    Route::patch('/journeys/{id}', [JourneyBuilderController::class, 'update'])->name('journeys.update');
    Route::post('/journeys/{id}/submit', [JourneyBuilderController::class, 'submit'])->name('journeys.submit');
    Route::delete('/journeys/{id}', [JourneyBuilderController::class, 'destroy'])->name('journeys.destroy');

    // Modules
    Route::post('/journeys/{id}/modules', [JourneyBuilderController::class, 'storeModule'])->name('modules.store');
    Route::patch('/modules/{moduleId}', [JourneyBuilderController::class, 'updateModule'])->name('modules.update');
    Route::delete('/modules/{moduleId}', [JourneyBuilderController::class, 'destroyModule'])->name('modules.destroy');

    // Lessons
    Route::post('/modules/{moduleId}/lessons', [JourneyBuilderController::class, 'storeLesson'])->name('lessons.store');
    Route::patch('/lessons/{lessonId}', [JourneyBuilderController::class, 'updateLesson'])->name('lessons.update');
    Route::delete('/lessons/{lessonId}', [JourneyBuilderController::class, 'destroyLesson'])->name('lessons.destroy');

    // Quizzes
    Route::get('/quizzes', [QuizBuilderController::class, 'index'])->name('quizzes');
    Route::post('/quizzes', [QuizBuilderController::class, 'store'])->name('quizzes.store');
    Route::delete('/quizzes/{id}', [QuizBuilderController::class, 'destroy'])->name('quizzes.destroy');

    // Bag (assets)
    Route::get('/bag', [BagController::class, 'index'])->name('bag');
    Route::get('/bag/create', [BagController::class, 'create'])->name('bag.create');
    Route::post('/bag', [BagController::class, 'store'])->name('bag.store');
    Route::delete('/bag/{id}', [BagController::class, 'destroy'])->name('bag.destroy');

    Route::get('/council', [CouncilController::class, 'index'])->name('council');
});

/*
|--------------------------------------------------------------------------
| Admin Space
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:ADMIN'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [ControlCenterController::class, 'index'])->name('control');

    // Moderation
    Route::post('/journeys/{id}/approve', [ModerationController::class, 'approveJourney'])->name('approveJourney');
    Route::post('/journeys/{id}/reject', [ModerationController::class, 'rejectJourney'])->name('rejectJourney');
    Route::post('/instructors/{id}/verify', [ModerationController::class, 'verifyInstructor'])->name('verifyInstructor');
    Route::post('/instructors/{id}/reject', [ModerationController::class, 'rejectInstructor'])->name('rejectInstructor');
});



