<?php

use App\Http\Controllers\Athlete\DocumentController;
use App\Http\Controllers\Athlete\EventController as AthleteEventController;
use App\Http\Controllers\Athlete\PhysicalController;
use App\Http\Controllers\Athlete\TrainingController;
use App\Http\Controllers\BugReportController;
use App\Http\Controllers\Coach\AthleteController;
use App\Http\Controllers\Coach\EventController as CoachEventController;
use App\Http\Controllers\Coach\EventSettingController;
use App\Http\Controllers\Coach\PerformanceController;
use App\Http\Controllers\Coach\ReportController as CoachReportController;
use App\Http\Controllers\Coach\TrainingSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentAccessController;
use App\Http\Controllers\Management\CategoryController;
use App\Http\Controllers\Management\ExerciseTypeController;
use App\Http\Controllers\Management\ReportController as ManagementReportController;
use App\Http\Controllers\Management\UserController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

// Custom Email Verification Notification Route (Manual)
Route::post('email/verification-notification', [VerificationController::class, 'store'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

// Bug Report - accessible from all pages (even without login)
Route::post('bug-reports', [BugReportController::class, 'store'])->name('bug-reports.store');

Route::middleware(['auth', 'verified', 'verified-user'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // Tools
    Route::inertia('tools/gear-calculator', 'tools/GearCalculator')->name('tools.gear-calculator');

    // Secure Document Access
    Route::get('documents/{athlete}/{type}', [DocumentAccessController::class, 'show'])->name('documents.show');

    // Management Routes (still filtered by role + verified-user)
    Route::middleware(['role:Manajemen'])->prefix('management')->name('management.')->group(function () {
        Route::get('dashboard', [App\Http\Controllers\Management\DashboardController::class, 'index'])->name('dashboard');
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::patch('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // Athlete Verification & Coaching
        Route::get('pending', [UserController::class, 'pending'])->name('users.pending');
        Route::post('users/{user}/verify', [UserController::class, 'verify'])->name('users.verify');
        Route::get('athletes', [UserController::class, 'athletes'])->name('athletes.index');
        Route::get('athletes/{athlete}', [App\Http\Controllers\Management\AthleteController::class, 'show'])->name('athletes.show');
        Route::post('athletes/{athlete}/license', [App\Http\Controllers\Management\AthleteController::class, 'uploadLicense'])->name('athletes.license.upload');

        // Category Management
        Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        // Exercise Type Management
        Route::get('exercise-types', [ExerciseTypeController::class, 'index'])->name('exercise-types.index');
        Route::post('exercise-types', [ExerciseTypeController::class, 'store'])->name('exercise-types.store');
        Route::put('exercise-types/{exerciseType}', [ExerciseTypeController::class, 'update'])->name('exercise-types.update');
        Route::delete('exercise-types/{exerciseType}', [ExerciseTypeController::class, 'destroy'])->name('exercise-types.destroy');

        // Reports
        Route::get('reports', [ManagementReportController::class, 'index'])->name('reports.index');
        Route::post('reports/export', [ManagementReportController::class, 'export'])->name('reports.export');

        // Event Settings
        Route::get('event-settings', [App\Http\Controllers\Management\EventSettingController::class, 'index'])->name('event-settings.index');
        Route::post('event-types', [App\Http\Controllers\Management\EventSettingController::class, 'storeType'])->name('event-types.store');
        Route::patch('event-types/{type}', [App\Http\Controllers\Management\EventSettingController::class, 'updateType'])->name('event-types.update');
        Route::delete('event-types/{type}', [App\Http\Controllers\Management\EventSettingController::class, 'destroyType'])->name('event-types.destroy');
        Route::post('event-points', [App\Http\Controllers\Management\EventSettingController::class, 'storePoint'])->name('event-points.store');
        Route::patch('event-points/{point}', [App\Http\Controllers\Management\EventSettingController::class, 'updatePoint'])->name('event-points.update');
        Route::delete('event-points/{point}', [App\Http\Controllers\Management\EventSettingController::class, 'destroyPoint'])->name('event-points.destroy');
    });

    // Coach specific routes
    Route::middleware(['role:Pelatih'])->prefix('coach')->name('coach.')->group(function () {
        Route::get('dashboard', [App\Http\Controllers\Coach\DashboardController::class, 'index'])->name('dashboard');
        Route::get('athletes', [AthleteController::class, 'index'])->name('athletes.index');
        Route::get('athletes/{athlete}', [AthleteController::class, 'show'])->name('athletes.show');
        Route::patch('athletes/{athlete}/category', [AthleteController::class, 'updateCategory'])->name('athletes.category.update');
        Route::post('athletes/{athlete}/metrics', [AthleteController::class, 'storeMetric'])->name('athletes.metrics.store');

        // Training Sessions (Standalone, multi-athlete)
        Route::get('training-sessions', [TrainingSessionController::class, 'index'])->name('training-sessions.index');
        Route::post('training-sessions', [TrainingSessionController::class, 'store'])->name('training-sessions.store');
        Route::get('training-sessions/{session}', [TrainingSessionController::class, 'show'])->name('training-sessions.show');
        Route::patch('training-sessions/{session}', [TrainingSessionController::class, 'update'])->name('training-sessions.update');
        Route::delete('training-sessions/{session}', [TrainingSessionController::class, 'destroy'])->name('training-sessions.destroy');

        Route::patch('training-logs/{log}/evaluation', [TrainingSessionController::class, 'updateEvaluation'])->name('training-logs.evaluation');
        Route::patch('training-logs/{log}', [AthleteController::class, 'updateLog'])->name('training-logs.update');
        Route::delete('training-logs/{log}', [AthleteController::class, 'destroyLog'])->name('training-logs.destroy');

        // Performance Comparison
        Route::get('performance-comparison', [PerformanceController::class, 'comparison'])->name('performance.comparison');
        Route::get('performance-comparison/data', [PerformanceController::class, 'getComparisonData'])->name('performance.comparison.data');

        // Reports
        Route::get('reports', [CoachReportController::class, 'index'])->name('reports.index');
        Route::post('reports/export', [CoachReportController::class, 'export'])->name('reports.export');

        // Events
        Route::get('events', [CoachEventController::class, 'index'])->name('events.index');
        Route::post('events', [CoachEventController::class, 'store'])->name('events.store');
        Route::patch('events/{event}', [CoachEventController::class, 'update'])->name('events.update');
        Route::delete('events/{event}', [CoachEventController::class, 'destroy'])->name('events.destroy');
        Route::patch('events/{event}/athletes/{athlete}', [CoachEventController::class, 'updateParticipation'])->name('events.participation.update');

        // Event Settings
        Route::post('event-types', [EventSettingController::class, 'storeType'])->name('event-types.store');
        Route::patch('event-types/{type}', [EventSettingController::class, 'updateType'])->name('event-types.update');
        Route::delete('event-types/{type}', [EventSettingController::class, 'destroyType'])->name('event-types.destroy');
        Route::post('event-points', [EventSettingController::class, 'storePoint'])->name('event-points.store');
        Route::patch('event-points/{point}', [EventSettingController::class, 'updatePoint'])->name('event-points.update');
        Route::delete('event-points/{point}', [EventSettingController::class, 'destroyPoint'])->name('event-points.destroy');

        // Messages
        Route::post('messages', [MessageController::class, 'store'])->name('messages.store');
        Route::delete('messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');
    });

    // Athlete specific routes
    Route::middleware(['role:Atlet'])->prefix('athlete')->name('athlete.')->group(function () {
        Route::get('dashboard', [App\Http\Controllers\Athlete\DashboardController::class, 'index'])->name('dashboard');
        Route::post('dashboard/quick-update', [App\Http\Controllers\Athlete\DashboardController::class, 'quickUpdate'])->name('dashboard.quick-update');

        Route::get('physical', [PhysicalController::class, 'index'])->name('physical.index');
        Route::post('physical', [PhysicalController::class, 'store'])->name('physical.store');

        // Training
        Route::get('training', [TrainingController::class, 'index'])->name('training.index');
        Route::post('training/log', [TrainingController::class, 'storeLog'])->name('training.log.store');
        Route::delete('training/log/{log}', [TrainingController::class, 'destroy'])->name('training.log.destroy');

        // Events
        Route::get('events', [AthleteEventController::class, 'index'])->name('events.index');

        // Documents
        Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');
        Route::post('documents', [DocumentController::class, 'store'])->name('documents.store');

        // Messages
        Route::patch('messages/{message}/read', [MessageController::class, 'markAsRead'])->name('messages.read');
    });

    // Report specific routes
    Route::middleware(['role:Report'])->prefix('report')->name('report.')->group(function () {
        Route::get('dashboard', [App\Http\Controllers\Report\DashboardController::class, 'index'])->name('dashboard');
        Route::patch('bug-reports/{bugReport}/status', [App\Http\Controllers\Report\DashboardController::class, 'updateStatus'])->name('bug-reports.status');
    });

    // Verification Pending Route (for athletes)
    Route::inertia('waiting-verification', 'auth/PendingVerification')->name('verification.pending');
});

require __DIR__.'/settings.php';
