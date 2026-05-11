<?php

use App\Http\Controllers\Atlet\DashboardController as AtletDashboardController;
use App\Http\Controllers\Atlet\DataFisikController as AtletDataFisikController;
use App\Http\Controllers\Atlet\EventController as AtletEventController;
use App\Http\Controllers\Atlet\LisensiUciController;
use App\Http\Controllers\Atlet\PesanNotifikasiController as AtletPesanController;
use App\Http\Controllers\Atlet\StatistikLatihanController as AtletStatistikController;
use App\Http\Controllers\Auth\DashboardController as DashboardDispatcher;
use App\Http\Controllers\Auth\DocumentAccessController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Manajer\DaftarAtletController as ManajerDaftarAtletController;
use App\Http\Controllers\Manajer\DashboardController as ManajerDashboardController;
use App\Http\Controllers\Manajer\EventController as ManajerEventController;
use App\Http\Controllers\Manajer\JenisLatihanController;
use App\Http\Controllers\Manajer\KategoriController;
use App\Http\Controllers\Manajer\LaporanPerformaController as ManajerLaporanController;
use App\Http\Controllers\Manajer\ManajemenAkunController;
use App\Http\Controllers\Pelatih\DaftarAtletController as PelatihDaftarAtletController;
use App\Http\Controllers\Pelatih\DashboardController as PelatihDashboardController;
use App\Http\Controllers\Pelatih\EvaluasiLatihanController;
use App\Http\Controllers\Pelatih\EventController as PelatihEventController;
use App\Http\Controllers\Pelatih\JadwalLatihanController;
use App\Http\Controllers\Pelatih\KomparasiPerformaController;
use App\Http\Controllers\Pelatih\LaporanPerformaController as PelatihLaporanController;
use App\Http\Controllers\Pelatih\PesanNotifikasiController as PelatihPesanController;
use App\Http\Controllers\Pelatih\SetelanEventController;
use App\Http\Controllers\Report\BugReportController;
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
    Route::get('dashboard', DashboardDispatcher::class)->name('dashboard');

    // Tools
    Route::inertia('tools/gear-calculator', 'tools/GearCalculator')->name('tools.gear-calculator');

    // Secure Document Access
    Route::get('documents/{athlete}/{type}', [DocumentAccessController::class, 'show'])->name('documents.show');

    // Management Routes
    Route::middleware(['role:Manajemen'])->prefix('management')->name('management.')->group(function () {
        Route::get('dashboard', [ManajerDashboardController::class, 'index'])->name('dashboard');
        Route::get('users', [ManajemenAkunController::class, 'index'])->name('users.index');
        Route::post('users', [ManajemenAkunController::class, 'store'])->name('users.store');
        Route::patch('users/{user}', [ManajemenAkunController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [ManajemenAkunController::class, 'destroy'])->name('users.destroy');

        // Athlete Verification & Coaching
        Route::get('pending', [ManajemenAkunController::class, 'pending'])->name('users.pending');
        Route::post('users/{user}/verify', [ManajemenAkunController::class, 'verify'])->name('users.verify');
        Route::get('athletes', [ManajemenAkunController::class, 'athletes'])->name('athletes.index');
        Route::get('athletes/{athlete}', [ManajerDaftarAtletController::class, 'show'])->name('athletes.show');
        Route::post('athletes/{athlete}/license', [ManajerDaftarAtletController::class, 'uploadLicense'])->name('athletes.license.upload');

        // Category Management
        Route::get('categories', [KategoriController::class, 'index'])->name('categories.index');
        Route::post('categories', [KategoriController::class, 'store'])->name('categories.store');
        Route::put('categories/{category}', [KategoriController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [KategoriController::class, 'destroy'])->name('categories.destroy');

        // Exercise Type Management
        Route::get('exercise-types', [JenisLatihanController::class, 'index'])->name('exercise-types.index');
        Route::post('exercise-types', [JenisLatihanController::class, 'store'])->name('exercise-types.store');
        Route::put('exercise-types/{exerciseType}', [JenisLatihanController::class, 'update'])->name('exercise-types.update');
        Route::delete('exercise-types/{exerciseType}', [JenisLatihanController::class, 'destroy'])->name('exercise-types.destroy');

        // Reports
        Route::get('reports', [ManajerLaporanController::class, 'index'])->name('reports.index');
        Route::post('reports/export', [ManajerLaporanController::class, 'export'])->name('reports.export');

        // Event Settings
        Route::get('event-settings', [ManajerEventController::class, 'index'])->name('event-settings.index');
        Route::post('event-types', [ManajerEventController::class, 'storeType'])->name('event-types.store');
        Route::patch('event-types/{type}', [ManajerEventController::class, 'updateType'])->name('event-types.update');
        Route::delete('event-types/{type}', [ManajerEventController::class, 'destroyType'])->name('event-types.destroy');
        Route::post('event-points', [ManajerEventController::class, 'storePoint'])->name('event-points.store');
        Route::patch('event-points/{point}', [ManajerEventController::class, 'updatePoint'])->name('event-points.update');
        Route::delete('event-points/{point}', [ManajerEventController::class, 'destroyPoint'])->name('event-points.destroy');
    });

    // Coach specific routes
    Route::middleware(['role:Pelatih'])->prefix('coach')->name('coach.')->group(function () {
        Route::get('dashboard', [PelatihDashboardController::class, 'index'])->name('dashboard');
        Route::get('athletes', [PelatihDaftarAtletController::class, 'index'])->name('athletes.index');
        Route::get('athletes/{athlete}', [PelatihDaftarAtletController::class, 'show'])->name('athletes.show');
        Route::patch('athletes/{athlete}/category', [PelatihDaftarAtletController::class, 'updateCategory'])->name('athletes.category.update');
        Route::post('athletes/{athlete}/metrics', [PelatihDaftarAtletController::class, 'storeMetric'])->name('athletes.metrics.store');

        // Training Sessions (Evaluasi Latihan)
        Route::get('training-sessions', [EvaluasiLatihanController::class, 'index'])->name('training-sessions.index');
        Route::post('training-sessions', [EvaluasiLatihanController::class, 'store'])->name('training-sessions.store');
        Route::get('training-sessions/{session}', [EvaluasiLatihanController::class, 'show'])->name('training-sessions.show');
        Route::patch('training-sessions/{session}', [EvaluasiLatihanController::class, 'update'])->name('training-sessions.update');
        Route::delete('training-sessions/{session}', [EvaluasiLatihanController::class, 'destroy'])->name('training-sessions.destroy');

        Route::patch('training-logs/{log}/evaluation', [EvaluasiLatihanController::class, 'updateEvaluation'])->name('training-logs.evaluation');
        Route::patch('training-logs/{log}', [PelatihDaftarAtletController::class, 'updateLog'])->name('training-logs.update');
        Route::delete('training-logs/{log}', [PelatihDaftarAtletController::class, 'destroyLog'])->name('training-logs.destroy');

        // Performance Comparison
        Route::get('performance-comparison', [KomparasiPerformaController::class, 'comparison'])->name('performance.comparison');
        Route::get('performance-comparison/data', [KomparasiPerformaController::class, 'getComparisonData'])->name('performance.comparison.data');

        // Reports
        Route::get('reports', [PelatihLaporanController::class, 'index'])->name('reports.index');
        Route::post('reports/export', [PelatihLaporanController::class, 'export'])->name('reports.export');

        // Events
        Route::get('events', [PelatihEventController::class, 'index'])->name('events.index');
        Route::post('events', [PelatihEventController::class, 'store'])->name('events.store');
        Route::patch('events/{event}', [PelatihEventController::class, 'update'])->name('events.update');
        Route::delete('events/{event}', [PelatihEventController::class, 'destroy'])->name('events.destroy');
        Route::patch('events/{event}/athletes/{athlete}', [PelatihEventController::class, 'updateParticipation'])->name('events.participation.update');

        // Event Settings
        Route::post('event-types', [SetelanEventController::class, 'storeType'])->name('event-types.store');
        Route::patch('event-types/{type}', [SetelanEventController::class, 'updateType'])->name('event-types.update');
        Route::delete('event-types/{type}', [SetelanEventController::class, 'destroyType'])->name('event-types.destroy');
        Route::post('event-points', [SetelanEventController::class, 'storePoint'])->name('event-points.store');
        Route::patch('event-points/{point}', [SetelanEventController::class, 'updatePoint'])->name('event-points.update');
        Route::delete('event-points/{point}', [SetelanEventController::class, 'destroyPoint'])->name('event-points.destroy');

        // Jadwal Latihan
        Route::get('training-plans', [JadwalLatihanController::class, 'index'])->name('training-plans.index'); // Added from plan

        // Messages
        Route::post('messages', [PelatihPesanController::class, 'store'])->name('messages.store');
        Route::delete('messages/{message}', [PelatihPesanController::class, 'destroy'])->name('messages.destroy');
    });

    // Athlete specific routes
    Route::middleware(['role:Atlet'])->prefix('athlete')->name('athlete.')->group(function () {
        Route::get('dashboard', [AtletDashboardController::class, 'index'])->name('dashboard');
        Route::post('dashboard/quick-update', [AtletDashboardController::class, 'quickUpdate'])->name('dashboard.quick-update');

        Route::get('physical', [AtletDataFisikController::class, 'index'])->name('physical.index');
        Route::post('physical', [AtletDataFisikController::class, 'store'])->name('physical.store');

        // Training
        Route::get('training', [AtletStatistikController::class, 'index'])->name('training.index');
        Route::post('training/log', [AtletStatistikController::class, 'storeLog'])->name('training.log.store');
        Route::delete('training/log/{log}', [AtletStatistikController::class, 'destroy'])->name('training.log.destroy');

        // Events
        Route::get('events', [AtletEventController::class, 'index'])->name('events.index');

        // Documents (Lisensi UCI)
        Route::get('documents', [LisensiUciController::class, 'index'])->name('documents.index');
        Route::post('documents', [LisensiUciController::class, 'store'])->name('documents.store');

        // Messages
        Route::patch('messages/{message}/read', [AtletPesanController::class, 'markAsRead'])->name('messages.read');
    });

    // Report specific routes
    Route::middleware(['role:Report'])->prefix('report')->name('report.')->group(function () {
        Route::get('dashboard', [BugReportController::class, 'index'])->name('dashboard');
        Route::patch('bug-reports/{bugReport}/status', [BugReportController::class, 'updateStatus'])->name('bug-reports.status');
    });

    // Verification Pending Route (for athletes)
    Route::inertia('waiting-verification', 'auth/PendingVerification')->name('verification.pending');
});

require __DIR__.'/settings.php';
