<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Notifications\Settings\EmailChangeOTP;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    public function sendEmailOTP(Request $request): RedirectResponse
    {
        $user = $request->user();
        $otp = (string) rand(100000, 999999);

        // Store OTP in cache for 10 minutes
        Cache::put('email_change_otp_'.$user->id, $otp, now()->addMinutes(10));

        $user->notify(new EmailChangeOTP($otp));

        return back()->with('status', 'otp-sent');
    }

    /**
     * Verify the email change OTP.
     */
    public function verifyEmailOTP(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();
        $cachedOtp = Cache::get('email_change_otp_'.$user->id);

        if (! $cachedOtp || $cachedOtp !== $request->otp) {
            return back()->withErrors(['otp' => 'Kode OTP tidak valid atau sudah kadaluarsa.']);
        }

        // Store verification status in cache for 15 minutes to allow email update
        Cache::put('email_change_verified_'.$user->id, true, now()->addMinutes(15));
        Cache::forget('email_change_otp_'.$user->id);

        return back()->with('status', 'otp-verified');
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // If email is being changed, verify OTP first
        if ($request->email !== $request->user()->email) {
            $isVerified = Cache::get('email_change_verified_'.$request->user()->id);
            if (! $isVerified) {
                return back()->withErrors(['email' => 'Silahkan verifikasi OTP terlebih dahulu sebelum mengubah email.']);
            }
            Cache::forget('email_change_verified_'.$request->user()->id);
        }

        $request->user()->fill(Arr::except($validated, ['avatar']));

        if ($request->hasFile('avatar')) {
            if ($request->user()->avatar) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $request->user()->avatar));
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $request->user()->avatar = '/storage/'.$path;
        }

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return to_route('profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(ProfileDeleteRequest $request): RedirectResponse
    {
        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
