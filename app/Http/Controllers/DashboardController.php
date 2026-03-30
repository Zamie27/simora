<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        
        \Illuminate\Support\Facades\Log::info('Dashboard Dispatcher Trace', [
            'user_id' => $user->id,
            'role' => $role,
        ]);

        if ($role === 'Manajemen') {
            return Inertia::render('Dashboard');
        }

        if ($role === 'Pelatih') {
            return redirect()->route('coach.athletes.index');
        }

        if ($role === 'Atlet') {
            return redirect()->route('athlete.dashboard');
        }

        return Inertia::render('Dashboard');
    }
}
