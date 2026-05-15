<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class LihatDashboardController extends Controller
{
    /**
     * Handle the incoming request and dispatch to role-specific dashboard.
     */
    public function __invoke(Request $permintaan)
    {
        $pengguna = $permintaan->user();

        $role = $pengguna->role->name ?? 'NO_ROLE';

        Log::info('Dashboard Dispatcher Trace', [
            'user_id' => $pengguna->id,
            'role' => $role,
        ]);

        if ($role === 'Manajemen') {
            return redirect()->route('manajemen.dashboard');
        }

        if ($role === 'Pelatih') {
            return redirect()->route('pelatih.dashboard');
        }

        if ($role === 'Atlet') {
            return redirect()->route('atlet.dashboard');
        }

        if ($role === 'Report') {
            return redirect()->route('report.dashboard');
        }

        return Inertia::render('dashboard/Index');
    }
}
