<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\TrainingLogRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function __construct(
        private TrainingLogRepository $logRepository
    ) {}

    /**
     * Display the management reports page.
     */
    public function index(Request $request): Response
    {
        $period = $request->input('period', 'this_month');
        [$startDate, $endDate] = $this->getRangeFromPreset($period);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
        }

        $athletes = User::whereRole('Atlet')
            ->with(['coach:id,name', 'athleteProfile'])
            ->select('id', 'name', 'email', 'coach_id', 'avatar')
            ->get();

        $coaches = User::whereRole('Pelatih')
            ->select('id', 'name')
            ->get();

        $reportData = [];
        foreach ($athletes as $athlete) {
            $stats = $this->logRepository->getStatistics(
                $athlete->id,
                $startDate?->toDateString(),
                $endDate?->toDateString()
            );
            $reportData[] = [
                'athlete' => $athlete,
                'statistics' => $stats,
            ];
        }

        return Inertia::render('management/Reports', [
            'athletes' => $athletes,
            'coaches' => $coaches,
            'reportData' => $reportData,
            'filters' => [
                'period' => $period,
                'start_date' => $startDate?->toDateString(),
                'end_date' => $endDate?->toDateString(),
            ],
        ]);
    }

    /**
     * Export management report.
     */
    public function export(Request $request)
    {
        $request->validate([
            'athlete_id' => ['nullable', 'exists:users,id'],
            'period' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'format' => ['required', 'in:csv'],
        ]);

        $athleteId = $request->input('athlete_id');
        $period = $request->input('period', 'custom');
        [$startDate, $endDate] = $this->getRangeFromPreset($period);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
        }

        $startStr = $startDate?->toDateString();
        $endStr = $endDate?->toDateString();

        if ($athleteId) {
            $athlete = User::findOrFail($athleteId);
            $logs = $this->logRepository->getForAthlete($athleteId, $startStr, $endStr);

            return $this->exportCsv($logs, $athlete->name);
        }

        $athletes = User::whereRole('Atlet')->get();
        $allLogs = collect();

        foreach ($athletes as $athlete) {
            $logs = $this->logRepository->getForAthlete($athlete->id, $startStr, $endStr);
            $allLogs = $allLogs->merge($logs);
        }

        return $this->exportCsv($allLogs, 'Laporan_Seluruh_Atlet');
    }

    /**
     * Get date range from preset.
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

    /**
     * Generate CSV export.
     */
    private function exportCsv($logs, string $name)
    {
        $fileName = 'Laporan_Manajemen_'.str_replace(' ', '_', $name).'_'.now()->format('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function () use ($logs) {
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
                'Status Kehadiran',
                'Status Penyelesaian',
                'Rating',
                'Evaluasi Pelatih',
            ]);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->date->format('Y-m-d'),
                    $log->athlete->name ?? '-',
                    $log->session->title ?? $log->type.' Session',
                    $log->session->exerciseType->name ?? $log->type,
                    $log->distance_km,
                    $log->duration_minutes,
                    $log->avg_speed,
                    $log->rpm,
                    $log->intensity,
                    $log->attendance_status,
                    $log->completion_status,
                    $log->coach_rating,
                    $log->coach_evaluation,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
