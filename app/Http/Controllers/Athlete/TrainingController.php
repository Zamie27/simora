<?php

namespace App\Http\Controllers\Athlete;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTrainingLogRequest;
use App\Models\ExerciseType;
use App\Models\TrainingLog;
use App\Models\TrainingSession;
use App\Repositories\TrainingLogRepository;
use App\Services\TrainingLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TrainingController extends Controller
{
    public function __construct(
        private TrainingLogService $logService,
        private TrainingLogRepository $logRepository
    ) {}

    /**
     * Display the athlete's training dashboard.
     */
    public function index(Request $request): Response
    {
        $athlete = $request->user();
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $logs = $this->logRepository->getForAthlete($athlete->id, $startDate, $endDate);
        $statistics = $this->logRepository->getStatistics($athlete->id, $startDate, $endDate);
        $performanceTrend = $this->logRepository->getPerformanceTrend($athlete->id, $startDate, $endDate);
        $upcomingSessions = $this->logRepository->getUpcomingSessions($athlete->id);
        $exerciseTypes = ExerciseType::all();

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
     * Store or update a training log entry.
     */
    public function storeLog(StoreTrainingLogRequest $request): RedirectResponse
    {
        $athleteId = $request->user()->id;
        $validated = $request->validated();
        $attachments = $request->file('attachments');

        // Athlete specifies a session
        if (! empty($validated['training_session_id'])) {
            $session = TrainingSession::where('id', $validated['training_session_id'])
                ->whereHas('athletes', fn ($q) => $q->where('users.id', $athleteId))
                ->first();

            if (! $session) {
                abort(403, 'Anda tidak terdaftar dalam sesi latihan ini.');
            }

            // Strict instance check: ensure they are logging for the current active instance
            $instanceDate = $session->getActiveInstanceDate();
            if (! $instanceDate->isToday()) {
                abort(403, 'Sesi ini hanya dapat diisi pada hari jadwal latihan.');
            }

            // Check if log already exists for TODAY for this session
            $log = TrainingLog::where('training_session_id', $session->id)
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

        // Independent/Manual log (no session_id)
        if (! empty($validated['id'])) {
            $log = TrainingLog::where('id', $validated['id'])
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
     * Remove the specified training log from storage.
     */
    public function destroy(TrainingLog $log): RedirectResponse
    {
        // Ensure the log belongs to the authenticated athlete
        if ($log->athlete_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus log ini.');
        }

        $log->delete();

        return back()->with('success', 'Log latihan berhasil dihapus.');
    }
}
