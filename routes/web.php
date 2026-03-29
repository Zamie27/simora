<?php

use App\Http\Controllers\Athlete\PhysicalController;
use App\Http\Controllers\Coach\AthleteController;
use App\Http\Controllers\Management\CategoryController;
use App\Http\Controllers\Management\UserController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified', 'verified-user'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    // Management Routes (still filtered by role + verified-user)
    Route::middleware(['role:Manajemen'])->prefix('management')->name('management.')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::patch('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // Athlete Verification & Coaching
        Route::get('pending', [UserController::class, 'pending'])->name('users.pending');
        Route::post('users/{user}/verify', [UserController::class, 'verify'])->name('users.verify');
        Route::get('athletes', [UserController::class, 'athletes'])->name('athletes.index');

        // Category Management
        Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    });

    // Coach specific routes
    Route::middleware(['role:Pelatih'])->prefix('coach')->name('coach.')->group(function () {
        Route::get('athletes', [AthleteController::class, 'index'])->name('athletes.index');
        Route::get('athletes/{athlete}', [AthleteController::class, 'show'])->name('athletes.show');
        Route::patch('athletes/{athlete}/category', [AthleteController::class, 'updateCategory'])->name('athletes.category.update');
        Route::post('athletes/{athlete}/metrics', [AthleteController::class, 'storeMetric'])->name('athletes.metrics.store');
    });

    // Athlete specific routes
    Route::middleware(['role:Atlet'])->prefix('athlete')->name('athlete.')->group(function () {
        Route::get('physical', [PhysicalController::class, 'index'])->name('physical.index');
        Route::post('physical', [PhysicalController::class, 'store'])->name('physical.store');
    });

    // Verification Pending Route (for athletes)
    Route::inertia('waiting-verification', 'auth/PendingVerification')->name('verification.pending');
});

require __DIR__.'/settings.php';
