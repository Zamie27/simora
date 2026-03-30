<?php

namespace App\Http\Controllers\Athlete;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTrainingLogRequest;
use App\Models\ExerciseType;
use App\Repositories\TrainingLogRepository;
use App\Services\TrainingLogService;
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
     * Store a new training log entry.
     */
    public function storeLog(StoreTrainingLogRequest $request)
    {
        $athlete = $request->user();
        $validated = $request->validated();
        $attachments = $request->file('attachments');

        $this->logService->create($athlete->id, $validated, $attachments);

        return back()->with('success', 'Data latihan berhasil dicatat.');
    }
}
