<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Notifications\Settings\EmailChangeOTP;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class KelolaOtpController extends Controller
{
    /**
     * UC-03: Kelola Profil Pengguna
     * Turunan: Mengirimkan OTP ke email baru pengguna.
     */
    public function kirimOtpEmail(Request $permintaan): RedirectResponse
    {
        $pengguna = $permintaan->user();
        $otp = (string) rand(100000, 999999);

        // Store OTP in cache for 10 minutes
        Cache::put('email_change_otp_'.$pengguna->id, $otp, now()->addMinutes(10));

        $pengguna->notify(new EmailChangeOTP($otp));

        return back()->with('status', 'otp-sent');
    }

    /**
     * UC-03: Kelola Profil Pengguna
     * Turunan: Memverifikasi kode OTP untuk perubahan email.
     */
    public function verifikasiOtpEmail(Request $permintaan): RedirectResponse
    {
        $permintaan->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $pengguna = $permintaan->user();
        $otpTersimpan = Cache::get('email_change_otp_'.$pengguna->id);

        if (! $otpTersimpan || $otpTersimpan !== $permintaan->otp) {
            return backErrors(['otp' => 'Kode OTP tidak valid atau sudah kadaluarsa.']);
        }

        // Store verification status in cache for 15 minutes to allow email update
        Cache::put('email_change_verified_'.$pengguna->id, true, now()->addMinutes(15));
        Cache::forget('email_change_otp_'.$pengguna->id);

        return back()->with('status', 'otp-verified');
    }
}
