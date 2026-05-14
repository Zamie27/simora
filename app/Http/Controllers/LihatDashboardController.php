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

class LihatDashboardController extends Controller
{
    public function __construct(
        private TrainingLogRepository $logRepository,
        private TrainingLogService $logService
    ) {}

    /**
     * UC-02: Lihat Dashboard
     * Turunan: Menampilkan dashboard utama berdasarkan peran pengguna.
     */
    public function tampilDashboard(Request $permintaan)
    {
        $pengguna = $permintaan->user();
        $role = $pengguna->role->name ?? 'NO_ROLE';

        if ($role === 'Manajemen') {
            return $this->tampilDashboardManajemen($permintaan);
        }

        if ($role === 'Pelatih') {
            return $this->tampilDashboardPelatih($permintaan);
        }

        if ($role === 'Atlet') {
            return $this->tampilDashboardAtlet($permintaan);
        }

        if ($role === 'Report') {
            return $this->tampilDashboardLaporan($permintaan);
        }

        // Default fallback if no role matches
        return Inertia::render('Dashboard');
    }

    /**
     * UC-02: Lihat Dashboard
     * Turunan: Menyajikan dashboard visual untuk role Manajemen.
     */
    private function tampilDashboardManajemen(Request $permintaan)
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
        $trenPerforma = LogLatihan::where('date', '>=', now()->subDays(7)->toDateString())
            ->selectRaw('date, SUM(distance_km) as distance_km')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 5. Athlete Ranking
        $atletRanking = $this->logRepository->getAthleteRanking();

        // 6. Running Sessions (Today)
        $runningSessions = SesiLatihan::where(function ($kueri) {
            $kueri->whereDate('scheduled_date', now()->toDateString())
                ->orWhere('repeat_weekly', true);
        })
            ->with(['coach:id,name', 'exerciseType:id,name', 'athletes' => fn ($q) => $q->with('athleteProfile')])
            ->get()
            ->filter(function ($sesi) {
                if (! $sesi->repeat_weekly) {
                    return true;
                }

                return $sesi->scheduled_date->dayOfWeek === now()->dayOfWeek;
            })
            ->values();

        // 7. Upcoming Events
        $upcomingEvents = Event::where('event_date', '>=', now()->toDateString())
            ->with(['type:id,name', 'coach:id,name', 'participants.user', 'participants.point'])
            ->orderBy('event_date')
            ->take(5)
            ->get();

        return Inertia::render('manajemen/Dashboard', [
            'stats' => [
                'total_athletes' => $totalAthletes,
                'total_coaches' => $totalCoaches,
                'verified_ratio_percent' => $verifiedRatio,
                'total_sessions' => $totalSessions,
                'total_logs' => $totalLogs,
            ],
            'recentLogs' => $recentLogs,
            'performanceTrend' => $trenPerforma,
            'athleteRanking' => $atletRanking,
            'runningSessions' => $runningSessions,
            'upcomingEvents' => $upcomingEvents,
        ]);
    }

    /**
     * UC-02: Lihat Dashboard
     * Turunan: Menyajikan dashboard visual untuk role Pelatih.
     */
    private function tampilDashboardPelatih(Request $permintaan)
    {
        $pelatih = $permintaan->user();
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        // 1. Athletes Performance Stat
        $daftarAtlet = User::where('coach_id', $pelatih->id)->with('category')->get();
        $atletIds = $daftarAtlet->pluck('id');

        // 2. Weekly Stats Aggregate
        $weeklyStats = LogLatihan::whereIn('athlete_id', $atletIds)
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->selectRaw('SUM(distance_km) as total_distance, SUM(duration_minutes) as total_duration, SUM(calories) as total_calories, COUNT(*) as log_count')
            ->first();

        // 3. Upcoming Sessions (Coach specific)
        $upcomingSessions = SesiLatihan::where('coach_id', $pelatih->id)
            ->where('scheduled_date', '>=', now()->toDateString())
            ->with('exerciseType')
            ->orderBy('scheduled_date')
            ->orderBy('scheduled_time')
            ->take(5)
            ->get();

        // 4. Recent Athlete Activity
        $recentLogs = LogLatihan::whereIn('athlete_id', $atletIds)
            ->with(['athlete.category', 'exerciseType'])
            ->latest('date')
            ->latest('created_at')
            ->take(10)
            ->get();

        // 5. Performance Trend (Last 7 days, overall squad distance)
        $trenPerforma = LogLatihan::whereIn('athlete_id', $atletIds)
            ->where('date', '>=', now()->subDays(7)->toDateString())
            ->selectRaw('date, SUM(distance_km) as distance_km')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 6. Category Distribution
        $kategoriDistribution = $daftarAtlet->groupBy('category.name')->map(function ($group) {
            return $group->count();
        });

        // 7. Recent Messages Sent
        $recentMessages = Pesan::where('sender_id', $pelatih->id)
            ->with(['receiver' => function ($q) {
                $q->select('id', 'name');
            }])
            ->latest()
            ->take(5)
            ->get();

        // 8. Athlete Ranking (Coach specific)
        $atletRanking = $this->logRepository->getAthleteRanking($atletIds->toArray());

        return Inertia::render('pelatih/Dashboard', [
            'stats' => [
                'total_athletes' => $daftarAtlet->count(),
                'upcoming_sessions_count' => SesiLatihan::where('coach_id', $pelatih->id)
                    ->where('scheduled_date', now()->toDateString())
                    ->count(),
                'weekly_distance' => round($weeklyStats->total_distance ?? 0, 2),
                'weekly_duration' => round($weeklyStats->total_duration ?? 0, 0),
            ],
            'upcomingSessions' => $upcomingSessions,
            'recentLogs' => $recentLogs,
            'performanceTrend' => $trenPerforma,
            'categoryDistribution' => $kategoriDistribution,
            'athletesList' => $daftarAtlet->map(fn ($a) => ['id' => $a->id, 'name' => $a->name]),
            'recentMessages' => $recentMessages,
            'athleteRanking' => $atletRanking,
        ]);
    }

    /**
     * UC-02: Lihat Dashboard
     * Turunan: Menyajikan dashboard visual untuk role Atlet.
     */
    private function tampilDashboardAtlet(Request $permintaan)
    {
        $pengguna = $permintaan->user();
        $pengguna->load(['category', 'latestPhysicalMetric']);

        $now = Carbon::now();

        // Weekly Stats (last 7 days)
        $weeklyLogs = $pengguna->trainingLogs()
            ->where('date', '>=', $now->copy()->subDays(7))
            ->get();

        $weeklyStats = [
            'distance' => (float) $weeklyLogs->sum('distance_km'),
            'duration' => (int) $weeklyLogs->sum('duration_minutes'),
            'calories' => (int) $weeklyLogs->sum('calories'),
            'count' => $weeklyLogs->count(),
        ];

        // Upcoming Events (Missions)
        $upcomingEvents = $pengguna->athleteEvents()
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
        $trenPerforma = $pengguna->trainingLogs()
            ->orderBy('date', 'desc')
            ->take(7)
            ->get()
            ->reverse()
            ->values();

        return Inertia::render('atlet/Dashboard', [
            'user' => $pengguna,
            'weeklyStats' => $weeklyStats,
            'upcomingEvents' => $upcomingEvents,
            'performanceTrend' => $trenPerforma,
            'exerciseTypes' => JenisLatihan::all(),
            'recentMessages' => Pesan::where('receiver_id', $pengguna->id)
                ->with('sender:id,name')
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }

    /**
     * UC-02: Lihat Dashboard
     * Turunan: Menyajikan dashboard visual untuk role Laporan/Report.
     */
    private function tampilDashboardLaporan(Request $permintaan)
    {
        $daftarLaporanBug = LaporanBug::latest('created_at')->get();

        return Inertia::render('report/Dashboard', [
            'bugReports' => $daftarLaporanBug,
            'stats' => [
                'total' => $daftarLaporanBug->count(),
                'pending' => $daftarLaporanBug->where('status', 'pending')->count(),
                'in_progress' => $daftarLaporanBug->where('status', 'sedang dikerjakan')->count(),
                'resolved' => $daftarLaporanBug->where('status', 'tuntas diperbaiki')->count(),
            ],
        ]);
    }

    /**
     * UC-06: Kelola data Metrik Fisik (BMI) & UC-07 (Latihan)
     * Turunan: Fitur update cepat untuk menyimpan data fisik dan log latihan atlet dari dashboard.
     */
    public function perbaruiDataCepat(Request $permintaan)
    {
        $pengguna = $permintaan->user();
        Log::info('QuickUpdate Started', ['user_id' => $pengguna->id]);

        $dataTervalidasi = $permintaan->validate([
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
            DB::transaction(function () use ($pengguna, $dataTervalidasi) {
                // 1. Create or Update Physical Metric
                if (! empty($dataTervalidasi['weight']) || ! empty($dataTervalidasi['height'])) {
                    $waktuPencatatan = Carbon::now();
                    $usia = $pengguna->date_of_birth ? $waktuPencatatan->diffInYears($pengguna->date_of_birth) : 0;

                    $latestMetric = $pengguna->physicalMetrics()
                        ->whereDate('recorded_at', Carbon::today())
                        ->first();

                    if ($latestMetric) {
                        $latestMetric->update([
                            'weight' => $dataTervalidasi['weight'] ?? $latestMetric->weight,
                            'height' => $dataTervalidasi['height'] ?? $latestMetric->height,
                            'age' => $usia,
                        ]);
                    } else {
                        $pengguna->physicalMetrics()->create([
                            'weight' => $dataTervalidasi['weight'] ?? ($pengguna->latestPhysicalMetric->weight ?? 0),
                            'height' => $dataTervalidasi['height'] ?? ($pengguna->latestPhysicalMetric->height ?? 0),
                            'age' => $usia,
                            'category' => $pengguna->category->name ?? 'Uncategorized',
                            'recorded_at' => $waktuPencatatan,
                        ]);
                    }
                }

                // 2. Create or Update Training Log
                if (! empty($dataTervalidasi['title']) || (! empty($dataTervalidasi['distance_km']) && $dataTervalidasi['distance_km'] > 0)) {
                    $catatanData = [
                        'title' => $dataTervalidasi['title'] ?? 'Latihan Mandiri (Quick Update)',
                        'exercise_type_id' => $dataTervalidasi['exercise_type_id'] ?? 1,
                        'date' => now(),
                        'distance_km' => $dataTervalidasi['distance_km'] ?? 0,
                        'duration_minutes' => $dataTervalidasi['duration_minutes'] ?? 0,
                        'avg_speed' => $dataTervalidasi['avg_speed'] ?? null,
                        'rpm' => $dataTervalidasi['rpm'] ?? null,
                        'avg_heart_rate' => $dataTervalidasi['avg_heart_rate'] ?? null,
                        'calories' => $dataTervalidasi['calories'] ?? null,
                        'elevation_m' => $dataTervalidasi['elevation_m'] ?? null,
                        'temperature_c' => $dataTervalidasi['temperature_c'] ?? null,
                        'intensity' => $dataTervalidasi['intensity'] ?? 'medium',
                        'athlete_notes' => $dataTervalidasi['notes'] ?? '',
                        'type' => 'manual',
                        'attendance_status' => 'present',
                        'completion_status' => 'completed',
                    ];

                    $this->logService->create($pengguna->id, $catatanData);
                }
            });

            return redirect()->route('atlet.dashboard')->with('success', 'Data fisik dan latihan berhasil disimpan sekaligus!');
        } catch (\Exception $e) {
            return redirect()->route('atlet.dashboard')->withErrors(['error' => 'Gagal menyimpan data: '.$e->getMessage()]);
        }
    }
}
