<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateEvaluationRequest;
use App\Models\LogLatihan;
use App\Services\TrainingLogService;
use App\Services\TrainingSessionService;
use Illuminate\Http\RedirectResponse;

class KelolaEvaluasiDanUmpanBalikLatihanController extends Controller
{
    public function __construct(
        private TrainingSessionService $sesiService,
        private TrainingLogService $catatanService
    ) {}

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

        $this->catatanService->updateEvaluation($catatan, $permintaan->validated());

        return back()->with('success', 'Evaluasi berhasil disimpan.');
    }
}
