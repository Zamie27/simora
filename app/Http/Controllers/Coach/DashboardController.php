<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\TrainingLog;
use App\Models\TrainingSession;
use App\Models\User;
use App\Repositories\TrainingLogRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        private TrainingLogRepository $logRepository
    ) {}

    public function index(Request $request)
    {
        $coach = $request->user();
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        // 1. Athletes Performance Stat
        $athletes = User::where('coach_id', $coach->id)->with('category')->get();
        $athleteIds = $athletes->pluck('id');

        // 2. Weekly Stats Aggregate
        $weeklyStats = TrainingLog::whereIn('athlete_id', $athleteIds)
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->selectRaw('SUM(distance_km) as total_distance, SUM(duration_minutes) as total_duration, SUM(calories) as total_calories, COUNT(*) as log_count')
            ->first();

        // 3. Upcoming Sessions (Coach specific)
        $upcomingSessions = TrainingSession::where('coach_id', $coach->id)
            ->where('scheduled_date', '>=', now()->toDateString())
            ->with('exerciseType')
            ->orderBy('scheduled_date')
            ->orderBy('scheduled_time')
            ->take(5)
            ->get();

        // 4. Recent Athlete Activity
        $recentLogs = TrainingLog::whereIn('athlete_id', $athleteIds)
            ->with(['athlete.category', 'exerciseType'])
            ->latest('date')
            ->latest('created_at')
            ->take(10)
            ->get();

        // 5. Performance Trend (Last 7 days, overall squad distance)
        $performanceTrend = TrainingLog::whereIn('athlete_id', $athleteIds)
            ->where('date', '>=', now()->subDays(7)->toDateString())
            ->selectRaw('date, SUM(distance_km) as distance_km')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 6. Category Distribution
        $categoryDistribution = $athletes->groupBy('category.name')->map(function ($group) {
            return $group->count();
        });

        // 7. Recent Messages Sent
        $recentMessages = Message::where('sender_id', $coach->id)
            ->with(['receiver' => function ($q) {
                $q->select('id', 'name');
            }])
            ->latest()
            ->take(5)
            ->get();

        // 8. Athlete Ranking (Coach specific)
        $athleteRanking = $this->logRepository->getAthleteRanking($athleteIds->toArray());

        return Inertia::render('coach/Dashboard', [
            'stats' => [
                'total_athletes' => $athletes->count(),
                'upcoming_sessions_count' => TrainingSession::where('coach_id', $coach->id)
                    ->where('scheduled_date', now()->toDateString())
                    ->count(),
                'weekly_distance' => round($weeklyStats->total_distance ?? 0, 2),
                'weekly_duration' => round($weeklyStats->total_duration ?? 0, 0),
            ],
            'upcomingSessions' => $upcomingSessions,
            'recentLogs' => $recentLogs,
            'performanceTrend' => $performanceTrend,
            'categoryDistribution' => $categoryDistribution,
            'athletesList' => $athletes->map(fn ($a) => ['id' => $a->id, 'name' => $a->name]),
            'recentMessages' => $recentMessages,
            'athleteRanking' => $athleteRanking,
        ]);
    }
}
