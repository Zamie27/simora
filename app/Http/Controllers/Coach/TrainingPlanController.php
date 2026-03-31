<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTrainingPlanRequest;
use App\Http\Requests\UpdateTrainingPlanRequest;
use App\Models\TrainingPlan;
use App\Models\User;
use App\Repositories\TrainingPlanRepository;
use App\Services\TrainingPlanService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TrainingPlanController extends Controller
{
    public function __construct(
        private TrainingPlanService $service,
        private TrainingPlanRepository $repository
    ) {}

    /**
     * Display the list of training plans for the coach.
     */
    public function index(Request $request): Response
    {
        $coach = $request->user();
        $plans = $this->repository->getForCoach($coach->id);

        $athletes = User::whereRole('Atlet')
            ->where('coach_id', $coach->id)
            ->select('id', 'name', 'email')
            ->get();

        return Inertia::render('coach/TrainingPlans', [
            'plans' => $plans,
            'athletes' => $athletes,
        ]);
    }

    /**
     * Store a new training plan.
     */
    public function store(StoreTrainingPlanRequest $request)
    {
        $coach = $request->user();
        $athlete = User::findOrFail($request->validated('athlete_id'));

        // Verify the athlete belongs to this coach
        if ($athlete->coach_id !== $coach->id) {
            abort(403, 'Atlet ini bukan binaan Anda.');
        }

        $this->service->create($coach->id, $request->validated());

        return back()->with('success', 'Rencana latihan berhasil dibuat.');
    }

    /**
     * Display the detail of a training plan.
     */
    public function show(TrainingPlan $trainingPlan): Response
    {
        $this->service->authorizeCoach($trainingPlan, auth()->id());

        $plan = $this->repository->getWithDetails($trainingPlan->id);

        return Inertia::render('coach/TrainingPlanDetail', [
            'plan' => $plan,
        ]);
    }

    /**
     * Update a training plan.
     */
    public function update(UpdateTrainingPlanRequest $request, TrainingPlan $trainingPlan)
    {
        $this->service->authorizeCoach($trainingPlan, $request->user()->id);
        $this->service->update($trainingPlan, $request->validated());

        return back()->with('success', 'Rencana latihan berhasil diperbarui.');
    }

    /**
     * Delete a training plan.
     */
    public function destroy(TrainingPlan $trainingPlan)
    {
        $this->service->authorizeCoach($trainingPlan, auth()->id());
        $this->service->delete($trainingPlan);

        return redirect()->route('coach.training-plans.index')
            ->with('success', 'Rencana latihan berhasil dihapus.');
    }
}
