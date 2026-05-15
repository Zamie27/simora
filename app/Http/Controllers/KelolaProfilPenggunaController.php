<?php

namespace App\Http\Controllers;

use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class KelolaProfilPenggunaController extends Controller
{
    /**
     * UC-03: Kelola Profil Pengguna
     * Turunan: Menampilkan halaman pengaturan profil pengguna.
     */
    public function tampilFormUbah(Request $permintaan): Response
    {
        return Inertia::render('pengaturan/Profil', [
            'mustVerifyEmail' => $permintaan->user() instanceof MustVerifyEmail,
            'status' => $permintaan->session()->get('status'),
        ]);
    }

    /**
     * UC-03: Kelola Profil Pengguna
     * Turunan: Memperbarui informasi profil pengguna (termasuk avatar).
     */
    public function perbaruiData(ProfileUpdateRequest $permintaan): RedirectResponse
    {
        $dataTervalidasi = $permintaan->validated();

        // If email is being changed, verify OTP first
        if ($permintaan->email !== $permintaan->user()->email) {
            $telahDiverifikasi = Cache::get('email_change_verified_'.$permintaan->user()->id);
            if (! $telahDiverifikasi) {
                return back()->withErrors(['email' => 'Silahkan verifikasi OTP terlebih dahulu sebelum mengubah email.']);
            }
            Cache::forget('email_change_verified_'.$permintaan->user()->id);
        }

        $permintaan->user()->fill(Arr::except($dataTervalidasi, ['avatar']));

        if ($permintaan->hasFile('avatar')) {
            $permintaan->user()->updateProfilePhoto($permintaan->file('avatar'));
        }

        if ($permintaan->user()->isDirty('email')) {
            $permintaan->user()->email_verified_at = null;
        }

        $permintaan->user()->save();

        return to_route('profile.edit');
    }

    /**
     * UC-03: Kelola Profil Pengguna
     * Turunan: Menghapus profil/akun pengguna dari sistem.
     */
    public function hapusData(ProfileDeleteRequest $permintaan): RedirectResponse
    {
        $pengguna = $permintaan->user();

        Auth::logout();

        $pengguna->delete();

        $permintaan->session()->invalidate();
        $permintaan->session()->regenerateToken();

        return redirect('/');
    }
}
