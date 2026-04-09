<?php

namespace App\Http\Controllers\Athlete;

use App\Http\Controllers\Controller;
use App\Models\ExerciseType;
use App\Models\Message;
use App\Models\TrainingLog;
use App\Services\TrainingLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(private TrainingLogService $logService) {}

    /**
     * Display the athlete dashboard with aggregated data.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $user->load(['category', 'latestPhysicalMetric']);

        $now = Carbon::now();
        $startOfWeek = $now->copy()->startOfWeek();

        // Weekly Stats (last 7 days)
        $weeklyLogs = $user->trainingLogs()
            ->where('date', '>=', $now->copy()->subDays(7))
            ->get();

        $weeklyStats = [
            'distance' => (float) $weeklyLogs->sum('distance_km'),
            'duration' => (int) $weeklyLogs->sum('duration_minutes'),
            'calories' => (int) $weeklyLogs->sum('calories'), // Assuming calories exists, checking model
            'count' => $weeklyLogs->count(),
        ];

        // Upcoming Events (Missions)
        $upcomingEvents = $user->athleteEvents()
            ->where('event_date', '>=', $now->copy()->startOfDay())
            ->with(['type', 'participants' => fn ($q) => $q->where('user_id', $user->id)])
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
            'exerciseTypes' => ExerciseType::all(),
            'recentMessages' => Message::where('receiver_id', $user->id)
                ->with('sender:id,name')
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }

    /**
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
                // 1. Create or Update Physical Metric record if any physical field is provided
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

                // 2. Create or Update Training Log if it has a title or distance
                if (! empty($validated['title']) || (! empty($validated['distance_km']) && $validated['distance_km'] > 0)) {
                    $logData = [
                        'title' => $validated['title'] ?? 'Latihan Mandiri (Quick Update)',
                        'exercise_type_id' => $validated['exercise_type_id'] ?? 1,
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
