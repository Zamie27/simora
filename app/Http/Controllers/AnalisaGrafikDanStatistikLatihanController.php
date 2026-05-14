<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTrainingLogRequest;
use App\Models\JenisLatihan;
use App\Models\LogLatihan;
use App\Models\SesiLatihan;
use App\Repositories\TrainingLogRepository;
use App\Services\TrainingLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnalisaGrafikDanStatistikLatihanController extends Controller
{
    public function __construct(
        private TrainingLogService $catatanService,
        private TrainingLogRepository $catatanRepository
    ) {}

    /**
     * Display the training statistics.
     */
    public function tampilData(Request $permintaan): Response
    {
        $role = $permintaan->user()->role->name ?? '';

        if ($role === 'Atlet') {
            return $this->athleteIndex($permintaan);
        }

        if ($role === 'Manajemen') {
            return $this->managementIndex($permintaan);
        }

        if ($role === 'Pelatih') {
            return $this->coachIndex($permintaan);
        }

        abort(403, 'Akses ditolak.');
    }

    /**
     * Store or update a training log entry (Atlet only).
     */
    public function simpanLogLatihan(StoreTrainingLogRequest $permintaan): RedirectResponse
    {
        if ($permintaan->user()->role->name !== 'Atlet') {
            abort(403);
        }

        $atletId = $permintaan->user()->id;
        $dataTervalidasi = $permintaan->validated();
        $attachments = $permintaan->file('attachments');

        // Athlete specifies a session
        if (! empty($dataTervalidasi['training_session_id'])) {
            $sesi = SesiLatihan::where('id', $dataTervalidasi['training_session_id'])
                ->whereHas('athletes', fn ($q) => $q->where('users.id', $atletId))
                ->first();

            if (! $sesi) {
                abort(403, 'Anda tidak terdaftar dalam sesi latihan ini.');
            }

            $instanceDate = $sesi->getActiveInstanceDate();
            if (! $instanceDate->isToday()) {
                abort(403, 'Sesi ini hanya dapat diisi pada hari jadwal latihan.');
            }

            $catatan = LogLatihan::where('training_session_id', $sesi->id)
                ->where('athlete_id', $atletId)
                ->whereDate('date', now()->toDateString())
                ->first();

            if ($catatan) {
                $this->logService->update($catatan, $dataTervalidasi, $attachments);

                return back()->with('success', 'Data latihan sesi berhasil diperbarui.');
            }

            $this->logService->create($atletId, $dataTervalidasi, $attachments);

            return back()->with('success', 'Data latihan sesi berhasil dicatat.');
        }

        // Independent/Manual log
        if (! empty($dataTervalidasi['id'])) {
            $catatan = LogLatihan::where('id', $dataTervalidasi['id'])
                ->where('athlete_id', $atletId)
                ->first();

            if ($catatan) {
                $this->logService->update($catatan, $dataTervalidasi, $attachments);

                return back()->with('success', 'Data latihan manual berhasil diperbarui.');
            }
        }

        $this->logService->create($atletId, $dataTervalidasi, $attachments);

        return back()->with('success', 'Data latihan manual berhasil dicatat.');
    }

    /**
     * Remove the specified training log from storage (Atlet only).
     */
    public function hapusLogLatihan(LogLatihan $catatan): RedirectResponse
    {
        if (auth()->user()->role->name !== 'Atlet') {
            abort(403);
        }

        if ($catatan->athlete_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus log ini.');
        }

        $catatan->delete();

        return back()->with('success', 'Log latihan berhasil dihapus.');
    }

    /**
     * -----------------------------------------------------------------
     * ATLET METHODS
     * -----------------------------------------------------------------
     */
    private function athleteIndex(Request $permintaan): Response
    {
        $atlet = $permintaan->user();
        $tanggalMulai = $permintaan->input('start_date');
        $tanggalSelesai = $permintaan->input('end_date');

        $daftarCatatan = $this->logRepository->getForAthlete($atlet->id, $tanggalMulai, $tanggalSelesai);
        $statistik = $this->logRepository->getStatistics($atlet->id, $tanggalMulai, $tanggalSelesai);
        $trenPerforma = $this->logRepository->getPerformanceTrend($atlet->id, $tanggalMulai, $tanggalSelesai);
        $upcomingSessions = $this->logRepository->getUpcomingSessions($atlet->id);
        $daftarJenisLatihan = JenisLatihan::all();

        return Inertia::render('atlet/Training', [
            'logs' => $daftarCatatan,
            'statistics' => $statistik,
            'performanceTrend' => $trenPerforma,
            'upcomingSessions' => $upcomingSessions,
            'exerciseTypes' => $daftarJenisLatihan,
            'filters' => [
                'start_date' => $tanggalMulai,
                'end_date' => $tanggalSelesai,
            ],
        ]);
    }

    /**
     * -----------------------------------------------------------------
     * MANAJEMEN METHODS
     * -----------------------------------------------------------------
     */
    private function managementIndex(Request $permintaan): Response
    {
        return Inertia::render('manajemen/GlobalStatistics');
    }

    /**
     * -----------------------------------------------------------------
     * COACH (PELATIH) METHODS
     * -----------------------------------------------------------------
     */
    private function coachIndex(Request $permintaan): Response
    {
        return Inertia::render('pelatih/StatisticsAnalysis');
    }
}
