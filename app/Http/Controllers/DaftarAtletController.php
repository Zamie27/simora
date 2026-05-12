<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\LogLatihan;
use App\Models\ProfilAtlet;
use App\Models\Role;
use App\Models\User;
use App\Repositories\TrainingLogRepository;
use App\Services\TrainingLogService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DaftarAtletController extends Controller
{
    public function __construct(
        private TrainingLogRepository $logRepository,
        private TrainingLogService $logService
    ) {}

    /**
     * Display a listing of the athletes.
     */
    public function index(Request $request): Response
    {
        $role = $request->user()->role->name ?? '';

        if ($role === 'Manajemen') {
            return $this->managementIndex($request);
        }

        if ($role === 'Pelatih') {
            return $this->coachIndex($request);
        }

        abort(403, 'Akses ditolak.');
    }

    /**
     * Display the specified athlete details.
     */
    public function show(Request $request, User $athlete): Response
    {
        $role = $request->user()->role->name ?? '';

        if ($role === 'Manajemen') {
            return $this->managementShow($request, $athlete);
        }

        if ($role === 'Pelatih') {
            return $this->coachShow($request, $athlete);
        }

        abort(403, 'Akses ditolak.');
    }

    /**
     * -----------------------------------------------------------------
     * MANAJEMEN METHODS
     * -----------------------------------------------------------------
     */
    private function managementIndex(Request $request): Response
    {
        $athletes = User::whereRole('Atlet')
            ->with(['latestPhysicalMetric', 'coach', 'athleteProfile'])
            ->get();

        return Inertia::render('management/Athletes', [
            'athletes' => $athletes,
            'categories' => Kategori::orderBy('name')->get(),
        ]);
    }

    private function managementShow(Request $request, User $athlete): Response
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $athlete->load(['category', 'athleteProfile', 'physicalMetrics' => function ($query) {
            $query->orderBy('recorded_at', 'desc');
        }]);

        $coachRole = Role::where('name', 'Pelatih')->first();

        $logs = $this->logRepository->getForAthlete($athlete->id, $startDate, $endDate);
        $statistics = $this->logRepository->getStatistics($athlete->id, $startDate, $endDate);
        $performanceTrend = $this->logRepository->getPerformanceTrend($athlete->id, $startDate, $endDate);

        return Inertia::render('management/AthleteDetail', [
            'athlete' => $athlete,
            'coaches' => User::where('role_id', $coachRole?->id)->get(),
            'trainingLogs' => $logs,
            'statistics' => $statistics,
            'performanceTrend' => $performanceTrend,
            'categories' => Kategori::orderBy('name')->get(),
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    /**
     * Upload license and update UCI ID (Manajemen only).
     */
    public function uploadLicense(Request $request, User $athlete)
    {
        if ($request->user()->role->name !== 'Manajemen') {
            abort(403);
        }

        $request->validate([
            'uci_id' => 'required|string|max:50',
            'license_valid_until' => 'required|date',
            'license_file' => 'nullable|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $profile = $athlete->athleteProfile ?? new ProfilAtlet(['user_id' => $athlete->id]);

        $profile->uci_id = $request->uci_id;
        $profile->license_valid_until = $request->license_valid_until;

        if ($request->hasFile('license_file')) {
            if ($profile->license_path) {
                Storage::disk('local')->delete($profile->license_path);
            }

            $extension = $request->file('license_file')->getClientOriginalExtension();
            $filename = 'UCI_'.$request->uci_id.'_'.time().'.'.$extension;

            $path = $request->file('license_file')->storeAs('private_documents/'.$athlete->id, $filename, 'local');
            $profile->license_path = $path;
        }

        $profile->save();

        return back()->with('success', 'Lisensi dan UCI ID berhasil diperbarui.');
    }

    /**
     * -----------------------------------------------------------------
     * COACH (PELATIH) METHODS
     * -----------------------------------------------------------------
     */
    private function coachIndex(Request $request): Response
    {
        $coach = $request->user();

        $athletes = User::whereRole('Atlet')
            ->where('coach_id', $coach->id)
            ->with(['latestPhysicalMetric', 'athleteProfile'])
            ->get();

        return Inertia::render('coach/Athletes', [
            'athletes' => $athletes,
            'categories' => Kategori::orderBy('name')->get(),
        ]);
    }

    private function coachShow(Request $request, User $athlete): Response
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
            'categories' => Kategori::orderBy('name')->get(),
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

    public function updateLog(Request $request, LogLatihan $log): RedirectResponse
    {
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

    public function destroyLog(LogLatihan $log): RedirectResponse
    {
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
