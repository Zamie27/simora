<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTrainingLogRequest;
use App\Models\JenisLatihan;
use App\Models\LogLatihan;
use App\Models\SesiLatihan;
use App\Repositories\TrainingLogRepository;
use App\Services\TrainingLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StatistikLatihanController extends Controller
{
    public function __construct(
        private TrainingLogService $logService,
        private TrainingLogRepository $logRepository
    ) {}

    /**
     * Display the training statistics.
     */
    public function index(Request $request): Response
    {
        $role = $request->user()->role->name ?? '';

        if ($role === 'Atlet') {
            return $this->athleteIndex($request);
        }

        if ($role === 'Manajemen') {
            return $this->managementIndex($request);
        }

        if ($role === 'Pelatih') {
            return $this->coachIndex($request);
        }

        abort(403, 'Akses ditolak.');
    }

    /**
     * Store or update a training log entry (Atlet only).
     */
    public function storeLog(StoreTrainingLogRequest $request): RedirectResponse
    {
        if ($request->user()->role->name !== 'Atlet') {
            abort(403);
        }

        $athleteId = $request->user()->id;
        $validated = $request->validated();
        $attachments = $request->file('attachments');

        // Athlete specifies a session
        if (! empty($validated['training_session_id'])) {
            $session = SesiLatihan::where('id', $validated['training_session_id'])
                ->whereHas('athletes', fn ($q) => $q->where('users.id', $athleteId))
                ->first();

            if (! $session) {
                abort(403, 'Anda tidak terdaftar dalam sesi latihan ini.');
            }

            $instanceDate = $session->getActiveInstanceDate();
            if (! $instanceDate->isToday()) {
                abort(403, 'Sesi ini hanya dapat diisi pada hari jadwal latihan.');
            }

            $log = LogLatihan::where('training_session_id', $session->id)
                ->where('athlete_id', $athleteId)
                ->whereDate('date', now()->toDateString())
                ->first();

            if ($log) {
                $this->logService->update($log, $validated, $attachments);

                return back()->with('success', 'Data latihan sesi berhasil diperbarui.');
            }

            $this->logService->create($athleteId, $validated, $attachments);

            return back()->with('success', 'Data latihan sesi berhasil dicatat.');
        }

        // Independent/Manual log
        if (! empty($validated['id'])) {
            $log = LogLatihan::where('id', $validated['id'])
                ->where('athlete_id', $athleteId)
                ->first();

            if ($log) {
                $this->logService->update($log, $validated, $attachments);

                return back()->with('success', 'Data latihan manual berhasil diperbarui.');
            }
        }

        $this->logService->create($athleteId, $validated, $attachments);

        return back()->with('success', 'Data latihan manual berhasil dicatat.');
    }

    /**
     * Remove the specified training log from storage (Atlet only).
     */
    public function destroy(LogLatihan $log): RedirectResponse
    {
        if (auth()->user()->role->name !== 'Atlet') {
            abort(403);
        }

        if ($log->athlete_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus log ini.');
        }

        $log->delete();

        return back()->with('success', 'Log latihan berhasil dihapus.');
    }

    /**
     * -----------------------------------------------------------------
     * ATLET METHODS
     * -----------------------------------------------------------------
     */
    private function athleteIndex(Request $request): Response
    {
        $athlete = $request->user();
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $logs = $this->logRepository->getForAthlete($athlete->id, $startDate, $endDate);
        $statistics = $this->logRepository->getStatistics($athlete->id, $startDate, $endDate);
        $performanceTrend = $this->logRepository->getPerformanceTrend($athlete->id, $startDate, $endDate);
        $upcomingSessions = $this->logRepository->getUpcomingSessions($athlete->id);
        $exerciseTypes = JenisLatihan::all();

        return Inertia::render('athlete/Training', [
            'logs' => $logs,
            'statistics' => $statistics,
            'performanceTrend' => $performanceTrend,
            'upcomingSessions' => $upcomingSessions,
            'exerciseTypes' => $exerciseTypes,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    /**
     * -----------------------------------------------------------------
     * MANAJEMEN METHODS
     * -----------------------------------------------------------------
     */
    private function managementIndex(Request $request): Response
    {
        return Inertia::render('management/GlobalStatistics');
    }

    /**
     * -----------------------------------------------------------------
     * COACH (PELATIH) METHODS
     * -----------------------------------------------------------------
     */
    private function coachIndex(Request $request): Response
    {
        return Inertia::render('coach/StatisticsAnalysis');
    }
}
