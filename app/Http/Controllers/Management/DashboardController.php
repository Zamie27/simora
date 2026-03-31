<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\TrainingLog;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. System-wide Users Stats
        $totalAthletes = User::whereHas('role', fn ($q) => $q->where('name', 'Atlet'))->count();
        $totalCoaches = User::whereHas('role', fn ($q) => $q->where('name', 'Pelatih'))->count();

        $totalUsers = User::count();
        $verifiedUsers = User::where('is_verified', true)->count();
        $verifiedRatio = $totalUsers > 0 ? round(($verifiedUsers / $totalUsers) * 100) : 0;

        // 2. Training Sessions and Logs Overviews
        $totalSessions = TrainingSession::count();
        $totalLogs = TrainingLog::count();

        // 3. Recent Activity Feed (Global)
        $recentLogs = TrainingLog::with(['athlete.category', 'exerciseType'])
            ->latest('date')
            ->latest('created_at')
            ->take(10)
            ->get();

        // 4. Global Performance Trend (Last 7 days distance)
        $performanceTrend = TrainingLog::where('date', '>=', now()->subDays(7)->toDateString())
            ->selectRaw('date, SUM(distance_km) as distance_km')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return Inertia::render('management/Dashboard', [
            'stats' => [
                'total_athletes' => $totalAthletes,
                'total_coaches' => $totalCoaches,
                'verified_ratio_percent' => $verifiedRatio,
                'total_sessions' => $totalSessions,
                'total_logs' => $totalLogs,
            ],
            'recentLogs' => $recentLogs,
            'performanceTrend' => $performanceTrend,
        ]);
    }
}
