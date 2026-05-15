<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\TrainingLogRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class LihatLaporanRiwayatPerformaController extends Controller
{
    public function __construct(
        private TrainingLogRepository $catatanRepository
    ) {}

    /**
     * Display the reports page based on role.
     */
    public function tampilData(Request $permintaan): Response
    {
        $role = $permintaan->user()->role->name ?? '';

        if ($role === 'Manajemen') {
            return $this->managementIndex($permintaan);
        }

        if ($role === 'Pelatih') {
            return $this->coachIndex($permintaan);
        }

        abort(403, 'Akses ditolak.');
    }

    /**
     * Export report data.
     */
    public function eksporData(Request $permintaan)
    {
        $role = $permintaan->user()->role->name ?? '';

        if ($role === 'Manajemen') {
            return $this->managementExport($permintaan);
        }

        if ($role === 'Pelatih') {
            return $this->coachExport($permintaan);
        }

        abort(403, 'Akses ditolak.');
    }

    /**
     * -----------------------------------------------------------------
     * MANAJEMEN METHODS
     * -----------------------------------------------------------------
     */
    private function managementIndex(Request $permintaan): Response
    {
        $period = $permintaan->input('period', 'this_month');
        [$tanggalMulai, $tanggalSelesai] = $this->getRangeFromPreset($period);

        if ($permintaan->filled('start_date') && $permintaan->filled('end_date')) {
            $tanggalMulai = Carbon::parse($permintaan->input('start_date'))->startOfDay();
            $tanggalSelesai = Carbon::parse($permintaan->input('end_date'))->endOfDay();
        }

        $daftarAtlet = User::whereRole('Atlet')
            ->with(['coach:id,name', 'athleteProfile'])
            ->select('id', 'name', 'email', 'coach_id', 'avatar')
            ->get();

        $daftarPelatih = User::whereRole('Pelatih')
            ->select('id', 'name')
            ->get();

        $reportData = [];
        foreach ($daftarAtlet as $atlet) {
            $stats = $this->catatanRepository->getStatistics(
                $atlet->id,
                $tanggalMulai?->toDateString(),
                $tanggalSelesai?->toDateString()
            );
            $reportData[] = [
                'athlete' => $atlet,
                'statistics' => $stats,
            ];
        }

        return Inertia::render('laporan-performa/Manajemen', [
            'athletes' => $daftarAtlet,
            'coaches' => $daftarPelatih,
            'reportData' => $reportData,
            'filters' => [
                'period' => $period,
                'start_date' => $tanggalMulai?->toDateString(),
                'end_date' => $tanggalSelesai?->toDateString(),
            ],
        ]);
    }

    private function managementExport(Request $permintaan)
    {
        $permintaan->validate([
            'athlete_id' => ['nullable', 'exists:users,id'],
            'period' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'format' => ['required', 'in:csv'],
        ]);

        $atletId = $permintaan->input('athlete_id');
        $period = $permintaan->input('period', 'custom');
        [$tanggalMulai, $tanggalSelesai] = $this->getRangeFromPreset($period);

        if ($permintaan->filled('start_date') && $permintaan->filled('end_date')) {
            $tanggalMulai = Carbon::parse($permintaan->input('start_date'))->startOfDay();
            $tanggalSelesai = Carbon::parse($permintaan->input('end_date'))->endOfDay();
        }

        $startStr = $tanggalMulai?->toDateString();
        $endStr = $tanggalSelesai?->toDateString();

        if ($atletId) {
            $atlet = User::findOrFail($atletId);
            $daftarCatatan = $this->catatanRepository->getForAthlete($atletId, $startStr, $endStr);

            return $this->exportCsv($daftarCatatan, $atlet->name);
        }

        $daftarAtlet = User::whereRole('Atlet')->get();
        $allLogs = collect();

        foreach ($daftarAtlet as $atlet) {
            $daftarCatatan = $this->catatanRepository->getForAthlete($atlet->id, $startStr, $endStr);
            $allLogs = $allLogs->merge($daftarCatatan);
        }

        return $this->exportCsv($allLogs, 'Laporan_Seluruh_Atlet');
    }

    /**
     * -----------------------------------------------------------------
     * COACH (PELATIH) METHODS
     * -----------------------------------------------------------------
     */
    private function coachIndex(Request $permintaan): Response
    {
        $pelatih = $permintaan->user();
        $period = $permintaan->input('period', 'this_month');
        [$tanggalMulai, $tanggalSelesai] = $this->getRangeFromPreset($period);

        if ($permintaan->filled('start_date') && $permintaan->filled('end_date')) {
            $tanggalMulai = Carbon::parse($permintaan->input('start_date'))->startOfDay();
            $tanggalSelesai = Carbon::parse($permintaan->input('end_date'))->endOfDay();
        }

        $daftarAtlet = User::whereRole('Atlet')
            ->where('coach_id', $pelatih->id)
            ->with(['athleteProfile'])
            ->get();

        $reportData = [];
        foreach ($daftarAtlet as $atlet) {
            $stats = $this->catatanRepository->getStatistics(
                $atlet->id,
                $tanggalMulai?->toDateString(),
                $tanggalSelesai?->toDateString()
            );
            $reportData[] = [
                'athlete' => $atlet,
                'statistics' => $stats,
            ];
        }

        return Inertia::render('laporan-performa/Pelatih', [
            'athletes' => $daftarAtlet,
            'reportData' => $reportData,
            'filters' => [
                'period' => $period,
                'start_date' => $tanggalMulai?->toDateString(),
                'end_date' => $tanggalSelesai?->toDateString(),
            ],
        ]);
    }

    private function coachExport(Request $permintaan)
    {
        $permintaan->validate([
            'athlete_id' => ['nullable', 'exists:users,id'],
            'period' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'format' => ['required', 'in:csv'],
        ]);

        $pelatih = $permintaan->user();
        $atletId = $permintaan->input('athlete_id');

        $period = $permintaan->input('period', 'custom');
        [$tanggalMulai, $tanggalSelesai] = $this->getRangeFromPreset($period);

        if ($permintaan->filled('start_date') && $permintaan->filled('end_date')) {
            $tanggalMulai = Carbon::parse($permintaan->input('start_date'))->startOfDay();
            $tanggalSelesai = Carbon::parse($permintaan->input('end_date'))->endOfDay();
        }

        $startStr = $tanggalMulai?->toDateString();
        $endStr = $tanggalSelesai?->toDateString();

        if ($atletId) {
            $atlet = User::findOrFail($atletId);
            if ($atlet->coach_id !== $pelatih->id) {
                abort(403, 'Atlet ini bukan binaan Anda.');
            }

            $daftarCatatan = $this->catatanRepository->getForAthlete($atletId, $startStr, $endStr);

            return $this->exportCsv($daftarCatatan, $atlet->name);
        }

        $daftarAtlet = User::whereRole('Atlet')
            ->where('coach_id', $pelatih->id)
            ->get();

        $allLogs = collect();
        foreach ($daftarAtlet as $atlet) {
            $daftarCatatan = $this->catatanRepository->getForAthlete($atlet->id, $startStr, $endStr);
            $allLogs = $allLogs->merge($daftarCatatan);
        }

        return $this->exportCsv($allLogs, 'Semua_Atlet');
    }

    /**
     * -----------------------------------------------------------------
     * SHARED METHODS
     * -----------------------------------------------------------------
     */
    private function getRangeFromPreset(string $preset): array
    {
        return match ($preset) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'this_week' => [now()->startOfWeek(), now()->endOfWeek()],
            'this_month' => [now()->startOfMonth(), now()->endOfMonth()],
            'this_4_months' => [now()->subMonths(4)->startOfDay(), now()->endOfDay()],
            'this_year' => [now()->startOfYear(), now()->endOfYear()],
            default => [null, null],
        };
    }

    private function exportCsv($daftarCatatan, string $name)
    {
        $fileName = 'Laporan_Performance_'.str_replace(' ', '_', $name).'_'.now()->format('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function () use ($daftarCatatan) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Tanggal',
                'Atlet',
                'Judul Latihan',
                'Jenis Latihan',
                'Jarak (km)',
                'Durasi (menit)',
                'Kecepatan Rata-rata (km/h)',
                'Cadence (RPM)',
                'Intensitas',
                'Status',
                'Rating Pelatih',
                'Evaluasi Pelatih',
            ]);

            foreach ($daftarCatatan as $catatan) {
                fputcsv($file, [
                    $catatan->date->format('Y-m-d'),
                    $catatan->athlete->name ?? '-',
                    $catatan->session->title ?? $catatan->type.' Session',
                    $catatan->session->exerciseType->name ?? $catatan->type,
                    $catatan->distance_km,
                    $catatan->duration_minutes,
                    $catatan->avg_speed,
                    $catatan->rpm,
                    $catatan->intensity,
                    $catatan->completion_status ?? $catatan->attendance_status,
                    $catatan->coach_rating,
                    $catatan->coach_evaluation,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
