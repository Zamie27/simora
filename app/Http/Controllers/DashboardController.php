<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request and dispatch to role-specific dashboard.
     */
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $role = $user->role->name ?? 'NO_ROLE';

        Log::info('Dashboard Dispatcher Trace', [
            'user_id' => $user->id,
            'role' => $role,
        ]);

        if ($role === 'Manajemen') {
            return redirect()->route('management.dashboard');
        }

        if ($role === 'Pelatih') {
            return redirect()->route('coach.dashboard');
        }

        if ($role === 'Atlet') {
            return redirect()->route('athlete.dashboard');
        }

        return Inertia::render('Dashboard');
    }
}
