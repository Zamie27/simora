<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTrainingPlanRequest;
use App\Http\Requests\UpdateTrainingPlanRequest;
use App\Models\JenisLatihan;
use App\Models\TrainingPlan;
use App\Models\User;
use App\Repositories\TrainingPlanRepository;
use App\Services\TrainingPlanService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KelolaJadwalSesiLatihanController extends Controller
{
    public function __construct(
        private TrainingPlanService $service,
        private TrainingPlanRepository $repository
    ) {}

    /**
     * Display the list of training plans for the coach.
     */
    public function tampilData(Request $permintaan): Response
    {
        $pelatih = $permintaan->user();
        $plans = $this->repository->getForCoach($pelatih->id);

        $daftarAtlet = User::whereRole('Atlet')
            ->where('coach_id', $pelatih->id)
            ->select('id', 'name', 'email')
            ->get();

        return Inertia::render('pelatih/TrainingPlans', [
            'plans' => $plans,
            'athletes' => $daftarAtlet,
        ]);
    }

    /**
     * Store a new training plan.
     */
    public function simpanData(StoreTrainingPlanRequest $permintaan)
    {
        $pelatih = $permintaan->user();
        $atlet = User::findOrFail($permintaan->validated('athlete_id'));

        // Verify the athlete belongs to this coach
        if ($atlet->coach_id !== $pelatih->id) {
            abort(403, 'Atlet ini bukan binaan Anda.');
        }

        $this->service->create($pelatih->id, $permintaan->validated());

        return back()->with('success', 'Rencana latihan berhasil dibuat.');
    }

    /**
     * Display the detail of a training plan.
     */
    public function show(TrainingPlan $trainingPlan): Response
    {
        $this->service->authorizeCoach($trainingPlan, auth()->id());

        $plan = $this->repository->getWithDetails($trainingPlan->id);

        return Inertia::render('pelatih/TrainingPlanDetail', [
            'plan' => $plan,
        ]);
    }

    /**
     * Update a training plan.
     */
    public function perbaruiData(UpdateTrainingPlanRequest $permintaan, TrainingPlan $trainingPlan)
    {
        $this->service->authorizeCoach($trainingPlan, $permintaan->user()->id);
        $this->service->update($trainingPlan, $permintaan->validated());

        return back()->with('success', 'Rencana latihan berhasil diperbarui.');
    }

    /**
     * Delete a training plan.
     */
    public function hapusData(TrainingPlan $trainingPlan)
    {
        $this->service->authorizeCoach($trainingPlan, auth()->id());
        $this->service->delete($trainingPlan);

        return redirect()->route('pelatih.training-plans.index')
            ->with('success', 'Rencana latihan berhasil dihapus.');
    }

    /**
     * UC-07: Kelola Jadwal Sesi Latihan
     * Turunan: Menampilkan daftar jenis latihan
     */
    public function tampilDaftarJenisLatihan(): Response
    {
        return Inertia::render('manajemen/ExerciseTypes', [
            'exerciseTypes' => JenisLatihan::orderBy('name')->get(),
        ]);
    }

    /**
     * UC-07: Kelola Jadwal Sesi Latihan
     * Turunan: Menyimpan jenis latihan baru
     */
    public function simpanJenisLatihan(Request $permintaan)
    {
        $dataTervalidasi = $permintaan->validate([
            'name' => 'required|string|max:255|unique:exercise_types,name',
            'description' => 'nullable|string|max:1000',
        ]);

        JenisLatihan::create($dataTervalidasi);

        return back()->with('success', 'Jenis Latihan berhasil ditambahkan.');
    }

    /**
     * UC-07: Kelola Jadwal Sesi Latihan
     * Turunan: Memperbarui jenis latihan
     */
    public function perbaruiJenisLatihan(Request $permintaan, JenisLatihan $jenisLatihan)
    {
        $dataTervalidasi = $permintaan->validate([
            'name' => 'required|string|max:255|unique:exercise_types,name,'.$jenisLatihan->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $jenisLatihan->update($dataTervalidasi);

        return back()->with('success', 'Jenis Latihan berhasil diperbarui.');
    }

    /**
     * UC-07: Kelola Jadwal Sesi Latihan
     * Turunan: Menghapus jenis latihan
     */
    public function hapusJenisLatihan(JenisLatihan $jenisLatihan)
    {
        $jenisLatihan->delete();

        return back()->with('success', 'Jenis Latihan berhasil dihapus.');
    }
}
