<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('management/Users', [
            'users' => User::with('role')->get(),
            'roles' => Role::all(),
        ]);
    }

    /**
     * Athletes management: List all verified athletes with sorting and filtering.
     */
    public function athletes(Request $request): Response
    {
        $atletRole = Role::where('name', 'Atlet')->first();
        $coachRole = Role::where('name', 'Pelatih')->first();

        $query = User::where('role_id', $atletRole?->id)
            ->where('is_verified', true)
            ->with('coach');

        // Filter by coach
        if ($request->filled('coach_id')) {
            $query->where('coach_id', $request->coach_id);
        }

        // Sorting
        $sort = $request->get('sort', 'name');
        $direction = $request->get('direction', 'asc');

        if (in_array($sort, ['name', 'email', 'created_at'])) {
            $query->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');
        }

        return Inertia::render('management/Athletes', [
            'athletes' => $query->get(),
            'coaches' => User::where('role_id', $coachRole?->id)->get(),
            'filters' => $request->only(['coach_id', 'sort', 'direction']),
        ]);
    }

    /**
     * List unverified users awaiting approval.
     */
    public function pending(): Response
    {
        return Inertia::render('management/PendingVerifications', [
            'pendingUsers' => User::where('is_verified', false)->with('role')->get(),
            'coaches' => User::whereHas('role', fn ($q) => $q->where('name', 'Pelatih'))->get(),
        ]);
    }

    /**
     * Verify a user and assign a coach.
     */
    public function verify(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'coach_id' => ['nullable', 'exists:users,id'],
        ]);

        $user->update([
            'is_verified' => true,
            'coach_id' => $request->coach_id,
        ]);

        return redirect()->back()->with('success', 'Akun berhasil diverifikasi dan coach telah ditugaskan.');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'min:8'],
            'role_id' => ['required', 'exists:roles,id'],
            'is_verified' => ['boolean'],
            'coach_id' => ['nullable', 'exists:users,id'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
            'is_verified' => $validated['is_verified'] ?? true, // Manual creation is verified by default
            'coach_id' => $validated['coach_id'] ?? null,
        ]);

        return redirect()->back()->with('success', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['nullable', 'string', 'min:8'],
            'is_verified' => ['boolean'],
            'coach_id' => ['nullable', 'exists:users,id'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role_id' => $validated['role_id'],
            'is_verified' => $validated['is_verified'] ?? $user->is_verified,
            'coach_id' => $validated['coach_id'] ?? $user->coach_id,
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->back()->with('success', 'Data user berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak bisa menghapus diri sendiri.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'User berhasil dihapus.');
    }
}
