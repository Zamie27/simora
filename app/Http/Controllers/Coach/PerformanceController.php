<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\TrainingLogRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PerformanceController extends Controller
{
    public function __construct(
        private TrainingLogRepository $logRepository
    ) {}

    /**
     * Display the performance comparison page.
     */
    public function comparison(Request $request): Response
    {
        $coach = $request->user();

        $athletes = User::whereRole('Atlet')
            ->where('coach_id', $coach->id)
            ->select('id', 'name', 'email')
            ->get();

        return Inertia::render('coach/PerformanceComparison', [
            'athletes' => $athletes,
        ]);
    }

    /**
     * Get comparison data for selected athletes.
     */
    public function getComparisonData(Request $request): JsonResponse
    {
        $request->validate([
            'athlete_ids' => ['required', 'array', 'min:2'],
            'athlete_ids.*' => ['exists:users,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $coach = $request->user();
        $athleteIds = $request->input('athlete_ids');

        // Verify all athletes belong to this coach
        $validAthletes = User::whereIn('id', $athleteIds)
            ->where('coach_id', $coach->id)
            ->pluck('id')
            ->toArray();

        if (count($validAthletes) !== count($athleteIds)) {
            abort(403, 'Beberapa atlet bukan binaan Anda.');
        }

        $comparison = $this->logRepository->getComparisonData(
            $athleteIds,
            $request->input('start_date'),
            $request->input('end_date')
        );

        // Get trend data per athlete
        $trends = [];
        foreach ($athleteIds as $athleteId) {
            $trends[$athleteId] = $this->logRepository->getPerformanceTrend(
                $athleteId,
                $request->input('start_date'),
                $request->input('end_date')
            );
        }

        $athletes = User::whereIn('id', $athleteIds)->select('id', 'name')->get()->keyBy('id');

        return response()->json([
            'comparison' => $comparison,
            'trends' => $trends,
            'athletes' => $athletes,
        ]);
    }
}
