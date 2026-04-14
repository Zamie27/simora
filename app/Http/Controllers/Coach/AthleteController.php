<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\TrainingLog;
use App\Models\User;
use App\Repositories\TrainingLogRepository;
use App\Services\TrainingLogService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AthleteController extends Controller
{
    public function __construct(
        private TrainingLogRepository $logRepository,
        private TrainingLogService $logService
    ) {}

    public function index(Request $request): Response
    {
        $coach = $request->user();

        $athletes = User::whereRole('Atlet')
            ->where('coach_id', $coach->id)
            ->with(['latestPhysicalMetric', 'athleteProfile'])
            ->get();

        return Inertia::render('coach/Athletes', [
            'athletes' => $athletes,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function show(Request $request, User $athlete): Response
    {
        $this->authorizeAccess($athlete);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $athlete->load(['category', 'athleteProfile', 'physicalMetrics' => function ($query) {
            $query->orderBy('recorded_at', 'desc');
        }]);

        $logs = $this->logRepository->getForAthlete($athlete->id, $startDate, $endDate);
        $statistics = $this->logRepository->getStatistics($athlete->id, $startDate, $endDate);
        $performanceTrend = $this->logRepository->getPerformanceTrend($athlete->id, $startDate, $endDate);

        return Inertia::render('coach/AthleteDetail', [
            'athlete' => $athlete,
            'categories' => Category::orderBy('name')->get(),
            'trainingLogs' => $logs,
            'statistics' => $statistics,
            'performanceTrend' => $performanceTrend,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    public function storeMetric(Request $request, User $athlete)
    {
        $this->authorizeAccess($athlete);

        if (! $athlete->date_of_birth || ! $athlete->gender) {
            $missing = [];
            if (! $athlete->date_of_birth) {
                $missing[] = 'tanggal lahir';
            }
            if (! $athlete->gender) {
                $missing[] = 'jenis kelamin';
            }

            $message = 'Atlet belum mengisi '.implode(' dan ', $missing).' di profil mereka. Harap beritahu atlet untuk melengkapi profil.';

            return back()->withErrors(['profile_incomplete' => $message]);
        }

        if (! $athlete->category_id) {
            return back()->withErrors(['category' => 'Harap tentukan kategori atlet terlebih dahulu.']);
        }

        $validated = $request->validate([
            'height' => 'required|numeric|min:50|max:250',
            'weight' => 'required|numeric|min:20|max:200',
            'recorded_at' => 'required|date',
        ]);

        $recordedAt = Carbon::parse($validated['recorded_at'])->startOfDay();
        $dob = $athlete->date_of_birth;

        $age = $recordedAt->year - $dob->year;
        if ($recordedAt->month < $dob->month || ($recordedAt->month === $dob->month && $recordedAt->day < $dob->day)) {
            $age--;
        }
        $validated['age'] = max(0, $age);
        $validated['category'] = $athlete->category->name;

        $athlete->physicalMetrics()->create($validated);

        return back()->with('success', 'Data fisik atlet berhasil diperbarui.');
    }

    public function updateCategory(Request $request, User $athlete)
    {
        $this->authorizeAccess($athlete);

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
        ]);

        $athlete->update($validated);

        return back()->with('success', 'Kategori atlet berhasil diperbarui.');
    }

    /**
     * Coach fully updates an athlete's training log.
     */
    public function updateLog(Request $request, TrainingLog $log): RedirectResponse
    {
        // Verify authorization (log belongs to an athlete trained by this coach)
        if ($log->athlete->coach_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke data latihan ini.');
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'distance_km' => 'nullable|numeric|min:0',
            'duration_minutes' => 'nullable|integer|min:0',
            'avg_speed' => 'nullable|numeric|min:0',
            'rpm' => 'nullable|numeric|min:0',
            'intensity' => 'nullable|in:low,medium,high,very_high',
            'attendance_status' => 'required|in:present,absent,late,excused',
            'completion_status' => 'required|in:not_started,in_progress,completed,incomplete',
            'athlete_notes' => 'nullable|string',
            'coach_rating' => 'nullable|integer|min:1|max:5',
            'coach_evaluation' => 'nullable|string',
            'coach_comments' => 'nullable|string',
        ]);

        $this->logService->update($log, $validated);

        return back()->with('success', 'Log latihan atlet berhasil diperbarui.');
    }

    /**
     * Remove the specified training log from storage.
     */
    public function destroyLog(TrainingLog $log): RedirectResponse
    {
        // Verify authorization (log belongs to an athlete trained by this coach)
        if ($log->athlete->coach_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke data latihan ini.');
        }

        $log->delete();

        return back()->with('success', 'Log latihan atlet berhasil dihapus.');
    }

    private function authorizeAccess(User $athlete)
    {
        if ($athlete->coach_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke data atlet ini.');
        }
    }
}
