<?php

use App\Http\Controllers\Auth\KeamananController;
use App\Http\Controllers\Auth\ProfilController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfilController::class, 'edit'])->name('profile.edit');
    Route::post('settings/profile/send-otp', [ProfilController::class, 'sendEmailOTP'])->name('profile.send-otp');
    Route::post('settings/profile/verify-otp', [ProfilController::class, 'verifyEmailOTP'])->name('profile.verify-otp');
    Route::patch('settings/profile', [ProfilController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('settings/profile', [ProfilController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/security', [KeamananController::class, 'edit'])->name('security.edit');

    Route::put('settings/password', [KeamananController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');
});
