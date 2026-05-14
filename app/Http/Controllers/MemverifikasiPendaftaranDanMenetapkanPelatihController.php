<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Notifications\AccountActivated;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class MemverifikasiPendaftaranDanMenetapkanPelatihController extends Controller
{
    public function tampilDaftar(): Response
    {
        return Inertia::render('manajemen/Users', [
            'users' => User::with(['role', 'athleteProfile'])->get(),
            'roles' => Role::all(),
        ]);
    }

    /**
     * Athletes management: List all verified athletes with sorting and filtering.
     */
    public function tampilDaftarAtlet(Request $permintaan): Response
    {
        $atletRole = Role::where('name', 'Atlet')->first();
        $pelatihRole = Role::where('name', 'Pelatih')->first();

        $kueri = User::where('role_id', $atletRole?->id)
            ->where('is_verified', true)
            ->with(['coach', 'athleteProfile']);

        // Filter by coach
        if ($permintaan->filled('coach_id')) {
            $kueri->where('coach_id', $permintaan->coach_id);
        }

        // Sorting
        $sort = $permintaan->input('sort', 'name');
        $direction = $permintaan->input('direction', 'asc');

        if (in_array($sort, ['name', 'email', 'created_at'])) {
            $kueri->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');
        }

        return Inertia::render('manajemen/Athletes', [
            'athletes' => $kueri->get(),
            'coaches' => User::where('role_id', $pelatihRole?->id)->get(),
            'filters' => $permintaan->only(['coach_id', 'sort', 'direction']),
        ]);
    }

    /**
     * List unverified users awaiting approval.
     */
    public function tampilDaftarTertunda(): Response
    {
        return Inertia::render('manajemen/PendingVerifications', [
            'pendingUsers' => User::where('is_verified', false)
                ->whereRole('Atlet')
                ->with('role')
                ->get(),
            'coaches' => User::whereRole('Pelatih')->get(),
        ]);
    }

    /**
     * Verify a user and assign a coach.
     */
    public function verifikasiPendaftaran(Request $permintaan, User $pengguna): RedirectResponse
    {
        $permintaan->validate([
            'coach_id' => ['nullable', 'exists:users,id'],
        ]);

        $pengguna->update([
            'is_verified' => true,
            'coach_id' => $permintaan->coach_id,
        ]);

        // Send activation notification
        $pengguna->notify(new AccountActivated);

        return redirect()->back()->with('success', 'Akun berhasil diverifikasi dan coach telah ditugaskan.');
    }

    public function simpanData(Request $permintaan): RedirectResponse
    {
        $dataTervalidasi = $permintaan->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'min:8'],
            'role_id' => ['required', 'exists:roles,id'],
            'is_verified' => ['boolean'],
            'coach_id' => ['nullable', 'exists:users,id'],
        ]);

        $pengguna = User::create([
            'name' => $dataTervalidasi['name'],
            'email' => $dataTervalidasi['email'],
            'password' => Hash::make($dataTervalidasi['password']),
            'role_id' => $dataTervalidasi['role_id'],
            'is_verified' => $dataTervalidasi['is_verified'] ?? true, // Manual creation is verified by default
            'coach_id' => $dataTervalidasi['coach_id'] ?? null,
        ]);

        $role = Role::find($dataTervalidasi['role_id']);

        if ($role && $role->name === 'Atlet') {
            $pengguna->sendEmailVerificationNotification();
        } else {
            $pengguna->markEmailAsVerified();
        }

        return redirect()->back()->with('success', 'User berhasil ditambahkan.');
    }

    public function perbaruiData(Request $permintaan, User $pengguna): RedirectResponse
    {
        /** @var User $pengguna */
        $dataTervalidasi = $permintaan->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($pengguna->id)],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['nullable', 'string', 'min:8'],
            'is_verified' => ['boolean'],
            'coach_id' => ['nullable', 'exists:users,id'],
        ]);

        $pengguna->update([
            'name' => $dataTervalidasi['name'],
            'email' => $dataTervalidasi['email'],
            'role_id' => $dataTervalidasi['role_id'],
            'is_verified' => $dataTervalidasi['is_verified'] ?? $pengguna->is_verified,
            'coach_id' => $dataTervalidasi['coach_id'] ?? $pengguna->coach_id,
        ]);

        if ($permintaan->filled('password')) {
            $pengguna->update(['password' => Hash::make($permintaan->password)]);
        }

        return redirect()->back()->with('success', 'Data user berhasil diperbarui.');
    }

    public function hapusData(User $pengguna): RedirectResponse
    {
        /** @var User $pengguna */
        if ($pengguna->id === auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak bisa menghapus diri sendiri.');
        }

        $pengguna->delete();

        return redirect()->back()->with('success', 'User berhasil dihapus.');
    }
}
