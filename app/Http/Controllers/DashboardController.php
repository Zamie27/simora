<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\JenisLatihan;
use App\Models\LaporanBug;
use App\Models\LogLatihan;
use App\Models\Pesan;
use App\Models\SesiLatihan;
use App\Models\User;
use App\Repositories\TrainingLogRepository;
use App\Services\TrainingLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        private TrainingLogRepository $logRepository,
        private TrainingLogService $logService
    ) {}

    /**
     * Display the dashboard based on user role.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user->role->name ?? 'NO_ROLE';

        if ($role === 'Manajemen') {
            return $this->managementDashboard($request);
        }

        if ($role === 'Pelatih') {
            return $this->coachDashboard($request);
        }

        if ($role === 'Atlet') {
            return $this->athleteDashboard($request);
        }

        if ($role === 'Report') {
            return $this->reportDashboard($request);
        }

        // Default fallback if no role matches
        return Inertia::render('Dashboard');
    }

    /**
     * -----------------------------------------------------------------
     * MANAJEMEN DASHBOARD
     * -----------------------------------------------------------------
     */
    private function managementDashboard(Request $request)
    {
        // 1. System-wide Users Stats
        $totalAthletes = User::whereHas('role', fn ($q) => $q->where('name', 'Atlet'))->count();
        $totalCoaches = User::whereHas('role', fn ($q) => $q->where('name', 'Pelatih'))->count();

        $totalUsers = User::count();
        $verifiedUsers = User::where('is_verified', true)->count();
        $verifiedRatio = $totalUsers > 0 ? round(($verifiedUsers / $totalUsers) * 100) : 0;

        // 2. Training Sessions and Logs Overviews
        $totalSessions = SesiLatihan::count();
        $totalLogs = LogLatihan::count();

        // 3. Recent Activity Feed (Global)
        $recentLogs = LogLatihan::with(['athlete.category', 'athlete.athleteProfile', 'exerciseType'])
            ->latest('date')
            ->latest('created_at')
            ->take(10)
            ->get();

        // 4. Global Performance Trend (Last 7 days distance)
        $performanceTrend = LogLatihan::where('date', '>=', now()->subDays(7)->toDateString())
            ->selectRaw('date, SUM(distance_km) as distance_km')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 5. Athlete Ranking
        $athleteRanking = $this->logRepository->getAthleteRanking();

        // 6. Running Sessions (Today)
        $runningSessions = SesiLatihan::where(function ($query) {
            $query->whereDate('scheduled_date', now()->toDateString())
                ->orWhere('repeat_weekly', true);
        })
            ->with(['coach:id,name', 'exerciseType:id,name', 'athletes' => fn ($q) => $q->with('athleteProfile')])
            ->get()
            ->filter(function ($session) {
                if (! $session->repeat_weekly) {
                    return true;
                }

                return $session->scheduled_date->dayOfWeek === now()->dayOfWeek;
            })
            ->values();

        // 7. Upcoming Events
        $upcomingEvents = Event::where('event_date', '>=', now()->toDateString())
            ->with(['type:id,name', 'coach:id,name', 'participants.user', 'participants.point'])
            ->orderBy('event_date')
            ->take(5)
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
            'athleteRanking' => $athleteRanking,
            'runningSessions' => $runningSessions,
            'upcomingEvents' => $upcomingEvents,
        ]);
    }

    /**
     * -----------------------------------------------------------------
     * COACH (PELATIH) DASHBOARD
     * -----------------------------------------------------------------
     */
    private function coachDashboard(Request $request)
    {
        $coach = $request->user();
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        // 1. Athletes Performance Stat
        $athletes = User::where('coach_id', $coach->id)->with('category')->get();
        $athleteIds = $athletes->pluck('id');

        // 2. Weekly Stats Aggregate
        $weeklyStats = LogLatihan::whereIn('athlete_id', $athleteIds)
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->selectRaw('SUM(distance_km) as total_distance, SUM(duration_minutes) as total_duration, SUM(calories) as total_calories, COUNT(*) as log_count')
            ->first();

        // 3. Upcoming Sessions (Coach specific)
        $upcomingSessions = SesiLatihan::where('coach_id', $coach->id)
            ->where('scheduled_date', '>=', now()->toDateString())
            ->with('exerciseType')
            ->orderBy('scheduled_date')
            ->orderBy('scheduled_time')
            ->take(5)
            ->get();

        // 4. Recent Athlete Activity
        $recentLogs = LogLatihan::whereIn('athlete_id', $athleteIds)
            ->with(['athlete.category', 'exerciseType'])
            ->latest('date')
            ->latest('created_at')
            ->take(10)
            ->get();

        // 5. Performance Trend (Last 7 days, overall squad distance)
        $performanceTrend = LogLatihan::whereIn('athlete_id', $athleteIds)
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
        $recentMessages = Pesan::where('sender_id', $coach->id)
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
                'upcoming_sessions_count' => SesiLatihan::where('coach_id', $coach->id)
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

    /**
     * -----------------------------------------------------------------
     * ATHLETE DASHBOARD
     * -----------------------------------------------------------------
     */
    private function athleteDashboard(Request $request)
    {
        $user = $request->user();
        $user->load(['category', 'latestPhysicalMetric']);

        $now = Carbon::now();

        // Weekly Stats (last 7 days)
        $weeklyLogs = $user->trainingLogs()
            ->where('date', '>=', $now->copy()->subDays(7))
            ->get();

        $weeklyStats = [
            'distance' => (float) $weeklyLogs->sum('distance_km'),
            'duration' => (int) $weeklyLogs->sum('duration_minutes'),
            'calories' => (int) $weeklyLogs->sum('calories'),
            'count' => $weeklyLogs->count(),
        ];

        // Upcoming Events (Missions)
        $upcomingEvents = $user->athleteEvents()
            ->where('event_date', '>=', $now->copy()->startOfDay())
            ->with([
                'type',
                'coach:id,name',
                'participants.user:id,name,avatar',
                'participants.point',
            ])
            ->orderBy('event_date', 'asc')
            ->take(3)
            ->get();

        // Performance Trend (Last 7 logs)
        $performanceTrend = $user->trainingLogs()
            ->orderBy('date', 'desc')
            ->take(7)
            ->get()
            ->reverse()
            ->values();

        return Inertia::render('athlete/Dashboard', [
            'user' => $user,
            'weeklyStats' => $weeklyStats,
            'upcomingEvents' => $upcomingEvents,
            'performanceTrend' => $performanceTrend,
            'exerciseTypes' => JenisLatihan::all(),
            'recentMessages' => Pesan::where('receiver_id', $user->id)
                ->with('sender:id,name')
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }

    /**
     * -----------------------------------------------------------------
     * REPORT DASHBOARD
     * -----------------------------------------------------------------
     */
    private function reportDashboard(Request $request)
    {
        $bugReports = LaporanBug::latest('created_at')->get();

        return Inertia::render('report/Dashboard', [
            'bugReports' => $bugReports,
            'stats' => [
                'total' => $bugReports->count(),
                'pending' => $bugReports->where('status', 'pending')->count(),
                'in_progress' => $bugReports->where('status', 'sedang dikerjakan')->count(),
                'resolved' => $bugReports->where('status', 'tuntas diperbaiki')->count(),
            ],
        ]);
    }

    /**
     * -----------------------------------------------------------------
     * QUICK UPDATE (Athlete Specific)
     * -----------------------------------------------------------------
     * Powerful Quick Update: Saves both Physical Metric and a Manual Training Log.
     */
    public function quickUpdate(Request $request)
    {
        $user = $request->user();
        Log::info('QuickUpdate Started', ['user_id' => $user->id]);

        $validated = $request->validate([
            // Physical data
            'weight' => 'nullable|numeric|min:20|max:200',
            'height' => 'nullable|numeric|min:50|max:250',

            // Training data
            'title' => 'nullable|string|max:255',
            'exercise_type_id' => 'nullable|exists:exercise_types,id',
            'distance_km' => 'nullable|numeric|min:0',
            'duration_minutes' => 'nullable|integer|min:0',
            'avg_speed' => 'nullable|numeric|min:0',
            'rpm' => 'nullable|numeric|min:0', // cadence
            'avg_heart_rate' => 'nullable|numeric|min:0',
            'calories' => 'nullable|integer|min:0',
            'elevation_m' => 'nullable|numeric|min:0',
            'temperature_c' => 'nullable|numeric',
            'intensity' => 'nullable|in:low,medium,high,very_high',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($user, $validated) {
                // 1. Create or Update Physical Metric
                if (! empty($validated['weight']) || ! empty($validated['height'])) {
                    $recordedAt = Carbon::now();
                    $age = $user->date_of_birth ? $recordedAt->diffInYears($user->date_of_birth) : 0;

                    $latestMetric = $user->physicalMetrics()
                        ->whereDate('recorded_at', Carbon::today())
                        ->first();

                    if ($latestMetric) {
                        $latestMetric->update([
                            'weight' => $validated['weight'] ?? $latestMetric->weight,
                            'height' => $validated['height'] ?? $latestMetric->height,
                            'age' => $age,
                        ]);
                    } else {
                        $user->physicalMetrics()->create([
                            'weight' => $validated['weight'] ?? ($user->latestPhysicalMetric->weight ?? 0),
                            'height' => $validated['height'] ?? ($user->latestPhysicalMetric->height ?? 0),
                            'age' => $age,
                            'category' => $user->category->name ?? 'Uncategorized',
                            'recorded_at' => $recordedAt,
                        ]);
                    }
                }

                // 2. Create or Update Training Log
                if (! empty($validated['title']) || (! empty($validated['distance_km']) && $validated['distance_km'] > 0)) {
                    $logData = [
                        'title' => $validated['title'] ?? 'Latihan Mandiri (Quick Update)',
                        'exercise_type_id' => $validated['exercise_type_id'] ?? 1,
                        'date' => now(),
                        'distance_km' => $validated['distance_km'] ?? 0,
                        'duration_minutes' => $validated['duration_minutes'] ?? 0,
                        'avg_speed' => $validated['avg_speed'] ?? null,
                        'rpm' => $validated['rpm'] ?? null,
                        'avg_heart_rate' => $validated['avg_heart_rate'] ?? null,
                        'calories' => $validated['calories'] ?? null,
                        'elevation_m' => $validated['elevation_m'] ?? null,
                        'temperature_c' => $validated['temperature_c'] ?? null,
                        'intensity' => $validated['intensity'] ?? 'medium',
                        'athlete_notes' => $validated['notes'] ?? '',
                        'type' => 'manual',
                        'attendance_status' => 'present',
                        'completion_status' => 'completed',
                    ];

                    $this->logService->create($user->id, $logData);
                }
            });

            return redirect()->route('athlete.dashboard')->with('success', 'Data fisik dan latihan berhasil disimpan sekaligus!');
        } catch (\Exception $e) {
            return redirect()->route('athlete.dashboard')->withErrors(['error' => 'Gagal menyimpan data: '.$e->getMessage()]);
        }
    }
}
