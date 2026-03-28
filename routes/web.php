<?php

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
    });

    // Verification Pending Route (for athletes)
    Route::inertia('waiting-verification', 'auth/PendingVerification')->name('verification.pending');
});

require __DIR__.'/settings.php';
