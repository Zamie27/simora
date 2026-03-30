<?php

namespace App\Http\Controllers\Athlete;

use App\Http\Controllers\Controller;
use App\Models\ExerciseType;
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
            'recentMessages' => \App\Models\Message::where('receiver_id', $user->id)
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
            'calories' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($user, $validated) {
                // 1. Create Physical Metric record if any physical field is provided
                if (! empty($validated['weight']) || ! empty($validated['height'])) {
                    $recordedAt = Carbon::now();
                    $age = $user->date_of_birth ? $recordedAt->diffInYears($user->date_of_birth) : 0;

                    $user->physicalMetrics()->create([
                        'weight' => $validated['weight'] ?? ($user->latestPhysicalMetric->weight ?? 0),
                        'height' => $validated['height'] ?? ($user->latestPhysicalMetric->height ?? 0),
                        'age' => $age,
                        'category' => $user->category->name ?? 'Uncategorized',
                        'recorded_at' => $recordedAt,
                    ]);
                }

                // 2. Create Training Log if it has a title or distance
                if (! empty($validated['title']) || (! empty($validated['distance_km']) && $validated['distance_km'] > 0)) {
                    $this->logService->create($user->id, [
                        'title' => $validated['title'] ?? 'Latihan Mandiri (Quick Update)',
                        'exercise_type_id' => $validated['exercise_type_id'] ?? 1, // Defaulting to 1 if not provided
                        'distance_km' => $validated['distance_km'] ?? 0,
                        'duration_minutes' => $validated['duration_minutes'] ?? 0,
                        'avg_speed' => $validated['avg_speed'] ?? 0,
                        'rpm' => $validated['rpm'] ?? 0,
                        'calories' => $validated['calories'] ?? 0,
                        'athlete_notes' => $validated['notes'] ?? '',
                        'type' => 'manual', // Distinguish from coach-assigned sessions
                        'attendance_status' => 'present',
                        'completion_status' => 'completed',
                    ]);
                }
            });

            return redirect()->route('athlete.dashboard')->with('success', 'Data fisik dan latihan berhasil disimpan sekaligus!');
        } catch (\Exception $e) {
            return redirect()->route('athlete.dashboard')->withErrors(['error' => 'Gagal menyimpan data: '.$e->getMessage()]);
        }
    }
}
