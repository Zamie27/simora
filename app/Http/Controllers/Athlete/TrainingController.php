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

        // Check if updating an existing log (manual or session)
        $logId = $request->input('id');
        if ($logId) {
            $log = TrainingLog::where('id', $logId)
                ->where('athlete_id', $athleteId)
                ->first();

            if ($log) {
                $this->logService->update($log, $validated, $attachments);

                return back()->with('success', 'Log latihan berhasil diperbarui.');
            }
        }

        // Athlete specifies a session
        if (! empty($validated['training_session_id'])) {
            $log = TrainingLog::where('training_session_id', $validated['training_session_id'])
                ->where('athlete_id', $athleteId)
                ->first();

            if ($log) {
                // Update the system-generated log for that session
                $this->logService->update($log, $validated, $attachments);

                return back()->with('success', 'Data latihan sesi berhasil diperbarui.');
            }

            // Verify if the athlete is actually assigned to this session
            $session = TrainingSession::where('id', $validated['training_session_id'])
                ->whereHas('athletes', fn ($q) => $q->where('users.id', $athleteId))
                ->first();

            if (! $session) {
                abort(403, 'Anda tidak terdaftar dalam sesi latihan ini.');
            }

            // If it's a valid session but somehow no log exists yet, create it
            $this->logService->create($athleteId, $validated, $attachments);

            return back()->with('success', 'Data latihan sesi berhasil dicatat.');
        }

        // Independent/Manual log (no session_id)
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
