<?php

use App\Http\Controllers\Auth\KeamananController;
use App\Http\Controllers\Auth\KelolaOtpController;
use App\Http\Controllers\KelolaProfilPenggunaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [KelolaProfilPenggunaController::class, 'tampilFormUbah'])->name('profile.edit');
    Route::post('settings/profile/send-otp', [KelolaOtpController::class, 'kirimOtpEmail'])->name('profile.send-otp');
    Route::post('settings/profile/verify-otp', [KelolaOtpController::class, 'verifikasiOtpEmail'])->name('profile.verify-otp');
    Route::patch('settings/profile', [KelolaProfilPenggunaController::class, 'perbaruiData'])->name('profile.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('settings/profile', [KelolaProfilPenggunaController::class, 'hapusData'])->name('profile.destroy');

    Route::get('settings/security', [KeamananController::class, 'edit'])->name('security.edit');

    Route::put('settings/password', [KeamananController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');
});
