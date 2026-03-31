<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTrainingSessionRequest;
use App\Http\Requests\UpdateEvaluationRequest;
use App\Models\ExerciseType;
use App\Models\TrainingLog;
use App\Models\TrainingSession;
use App\Models\User;
use App\Services\TrainingLogService;
use App\Services\TrainingSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TrainingSessionController extends Controller
{
    public function __construct(
        private TrainingSessionService $sessionService,
        private TrainingLogService $logService
    ) {}

    /**
     * List sessions created by the coach.
     */
    public function index(Request $request): Response
    {
        $coachId = $request->user()->id;

        return Inertia::render('coach/TrainingSessions', [
            'sessions' => TrainingSession::where('coach_id', $coachId)
                ->with(['exerciseType', 'athletes'])
                ->withCount('athletes')
                ->orderBy('scheduled_date', 'desc')
                ->get(),
            'exerciseTypes' => ExerciseType::all(),
            'athletes' => User::where('coach_id', $coachId)
                ->where('is_verified', true)
                ->get(['id', 'name']),
        ]);
    }

    /**
     * Store a new training session.
     */
    public function store(StoreTrainingSessionRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $athleteIds = $validated['athlete_ids'];
        unset($validated['athlete_ids']);

        $validated['coach_id'] = $request->user()->id;

        // Verify athletes belong to this coach
        $validAthleteIds = User::whereIn('id', $athleteIds)
            ->where('coach_id', $request->user()->id)
            ->pluck('id')
            ->toArray();

        if (empty($validAthleteIds)) {
            return back()->with('error', 'Tidak ada atlet valid yang dipilih.');
        }

        $this->sessionService->createSession($validated, $validAthleteIds);

        return back()->with('success', 'Sesi latihan berhasil ditambahkan.');
    }

    /**
     * Display the detail of a training session.
     */
    public function show(TrainingSession $session): Response
    {
        // Check authorization
        if ($session->coach_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke sesi latihan ini.');
        }

        $session->load([
            'exerciseType',
            'athletes:id,name,email',
            'logs' => fn ($q) => $q->with(['athlete:id,name,email', 'attachments'])->orderBy('date', 'desc'),
        ]);

        return Inertia::render('coach/TrainingSessionDetail', [
            'session' => $session,
        ]);
    }

    /**
     * Update coach evaluation on a training log.
     */
    public function updateEvaluation(UpdateEvaluationRequest $request, TrainingLog $log): RedirectResponse
    {
        // Verify authorization
        $log->load('session');
        if ($log->session && $log->session->coach_id !== $request->user()->id) {
            abort(403, 'Anda tidak memiliki akses ke data latihan ini.');
        }

        if (! $log->session && $log->athlete->coach_id !== $request->user()->id) {
            abort(403, 'Anda tidak memiliki akses ke data latihan ini.');
        }

        $this->logService->updateEvaluation($log, $request->validated());

        return back()->with('success', 'Evaluasi berhasil disimpan.');
    }

    /**
     * Delete a session.
     */
    public function destroy(TrainingSession $session): RedirectResponse
    {
        if ($session->coach_id !== auth()->id()) {
            abort(403);
        }

        $this->sessionService->deleteSession($session);

        return back()->with('success', 'Sesi latihan berhasil dihapus.');
    }
}
