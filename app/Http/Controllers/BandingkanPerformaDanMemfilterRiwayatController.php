<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\TrainingLogRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BandingkanPerformaDanMemfilterRiwayatController extends Controller
{
    public function __construct(
        private TrainingLogRepository $catatanRepository
    ) {}

    /**
     * Display the performance comparison page.
     */
    public function tampilHalaman(Request $permintaan): Response
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
     * Get comparison data for selected athletes.
     */
    public function ambilDataKomparasi(Request $permintaan): JsonResponse
    {
        $role = $permintaan->user()->role->name ?? '';

        if ($role === 'Manajemen') {
            return $this->managementGetComparisonData($permintaan);
        }

        if ($role === 'Pelatih') {
            return $this->coachGetComparisonData($permintaan);
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
        return Inertia::render('manajemen/PerformanceComparison');
    }

    private function managementGetComparisonData(Request $permintaan): JsonResponse
    {
        $permintaan->validate([
            'athlete_ids' => ['required', 'array', 'min:2'],
            'athlete_ids.*' => ['exists:users,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $atletIds = $permintaan->input('athlete_ids');

        $comparison = $this->logRepository->getComparisonData(
            $atletIds,
            $permintaan->input('start_date'),
            $permintaan->input('end_date')
        );

        $trends = [];
        foreach ($atletIds as $atletId) {
            $trends[$atletId] = $this->logRepository->getPerformanceTrend(
                $atletId,
                $permintaan->input('start_date'),
                $permintaan->input('end_date')
            );
        }

        $daftarAtlet = User::whereIn('id', $atletIds)->select('id', 'name')->get()->keyBy('id');

        return response()->json([
            'comparison' => $comparison,
            'trends' => $trends,
            'athletes' => $daftarAtlet,
        ]);
    }

    /**
     * -----------------------------------------------------------------
     * COACH (PELATIH) METHODS
     * -----------------------------------------------------------------
     */
    private function coachIndex(Request $permintaan): Response
    {
        $pelatih = $permintaan->user();

        $daftarAtlet = User::whereRole('Atlet')
            ->where('coach_id', $pelatih->id)
            ->select('id', 'name', 'email')
            ->get();

        return Inertia::render('pelatih/PerformanceComparison', [
            'athletes' => $daftarAtlet,
        ]);
    }

    private function coachGetComparisonData(Request $permintaan): JsonResponse
    {
        $permintaan->validate([
            'athlete_ids' => ['required', 'array', 'min:2'],
            'athlete_ids.*' => ['exists:users,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $pelatih = $permintaan->user();
        $atletIds = $permintaan->input('athlete_ids');

        $validAthletes = User::whereIn('id', $atletIds)
            ->where('coach_id', $pelatih->id)
            ->pluck('id')
            ->toArray();

        if (count($validAthletes) !== count($atletIds)) {
            abort(403, 'Beberapa atlet bukan binaan Anda.');
        }

        $comparison = $this->logRepository->getComparisonData(
            $atletIds,
            $permintaan->input('start_date'),
            $permintaan->input('end_date')
        );

        $trends = [];
        foreach ($atletIds as $atletId) {
            $trends[$atletId] = $this->logRepository->getPerformanceTrend(
                $atletId,
                $permintaan->input('start_date'),
                $permintaan->input('end_date')
            );
        }

        $daftarAtlet = User::whereIn('id', $atletIds)->select('id', 'name')->get()->keyBy('id');

        return response()->json([
            'comparison' => $comparison,
            'trends' => $trends,
            'athletes' => $daftarAtlet,
        ]);
    }
}
