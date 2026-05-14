<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTrainingSessionRequest;
use App\Http\Requests\UpdateEvaluationRequest;
use App\Models\JenisLatihan;
use App\Models\LogLatihan;
use App\Models\SesiLatihan;
use App\Models\User;
use App\Services\TrainingLogService;
use App\Services\TrainingSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KelolaEvaluasiDanUmpanBalikLatihanController extends Controller
{
    public function __construct(
        private TrainingSessionService $sesiService,
        private TrainingLogService $catatanService
    ) {}

    /**
     * List sessions created by the coach.
     */
    public function tampilData(Request $permintaan): Response
    {
        $pelatihId = $permintaan->user()->id;

        return Inertia::render('pelatih/TrainingSessions', [
            'sessions' => SesiLatihan::where('coach_id', $pelatihId)
                ->with(['exerciseType', 'athletes' => fn ($q) => $q->with('athleteProfile')])
                ->withCount('athletes')
                ->orderBy('scheduled_date', 'desc')
                ->get(),
            'exerciseTypes' => JenisLatihan::all(),
            'athletes' => User::where('coach_id', $pelatihId)
                ->where('is_verified', true)
                ->with(['athleteProfile'])
                ->get(['id', 'name', 'avatar']),
        ]);
    }

    /**
     * Store a new training session.
     */
    public function simpanData(StoreTrainingSessionRequest $permintaan): RedirectResponse
    {
        $dataTervalidasi = $permintaan->validated();
        $atletIds = $dataTervalidasi['athlete_ids'];
        unset($dataTervalidasi['athlete_ids']);

        $dataTervalidasi['coach_id'] = $permintaan->user()->id;

        // Verify athletes belong to this coach
        $validAthleteIds = User::whereIn('id', $atletIds)
            ->where('coach_id', $permintaan->user()->id)
            ->pluck('id')
            ->toArray();

        if (empty($validAthleteIds)) {
            return back()->with('error', 'Tidak ada atlet valid yang dipilih.');
        }

        $this->sessionService->createSession($dataTervalidasi, $validAthleteIds);

        return back()->with('success', 'Sesi latihan berhasil ditambahkan.');
    }

    /**
     * Display the detail of a training session.
     */
    public function tampilDetail(SesiLatihan $sesi): Response
    {
        // Check authorization
        if ($sesi->coach_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke sesi latihan ini.');
        }

        $sesi->load([
            'exerciseType',
            'athletes:id,name,email,avatar',
            'logs' => fn ($q) => $q->with(['athlete:id,name,email,avatar', 'attachments'])->orderBy('date', 'desc'),
        ]);
        $sesi->athletes->load('athleteProfile');
        $sesi->logs->pluck('athlete')->each->load('athleteProfile');

        return Inertia::render('pelatih/TrainingSessionDetail', [
            'session' => $sesi,
        ]);
    }

    /**
     * Update coach evaluation on a training log.
     */
    public function perbaruiEvaluasi(UpdateEvaluationRequest $permintaan, LogLatihan $catatan): RedirectResponse
    {
        // Verify authorization
        $catatan->load('session');
        if ($catatan->session && $catatan->session->coach_id !== $permintaan->user()->id) {
            abort(403, 'Anda tidak memiliki akses ke data latihan ini.');
        }

        if (! $catatan->session && $catatan->athlete->coach_id !== $permintaan->user()->id) {
            abort(403, 'Anda tidak memiliki akses ke data latihan ini.');
        }

        $this->logService->updateEvaluation($catatan, $permintaan->validated());

        return back()->with('success', 'Evaluasi berhasil disimpan.');
    }

    /**
     * Delete a session.
     */
    public function hapusData(SesiLatihan $sesi): RedirectResponse
    {
        if ($sesi->coach_id !== auth()->id()) {
            abort(403);
        }

        $this->sessionService->deleteSession($sesi);

        return back()->with('success', 'Sesi latihan berhasil dihapus.');
    }
}
