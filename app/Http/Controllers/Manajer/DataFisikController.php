<?php

namespace App\Http\Controllers\Manajer;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\TrainingLogRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DataFisikController extends Controller
{
    public function __construct(
        private TrainingLogRepository $logRepository
    ) {}

    /**
     * Display athlete physical metrics for management review.
     */
    public function show(Request $request, User $athlete)
    {
        // This functionality is already integrated into the athlete detail view
        // But we provide a specific controller if needed for standalone physical review
        $athlete->load(['physicalMetrics' => function ($query) {
            $query->orderBy('recorded_at', 'desc');
        }]);

        return Inertia::render('management/AthletePhysicalReview', [
            'athlete' => $athlete,
        ]);
    }
}
