<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\User;
use App\Repositories\TrainingLogRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KelolaDataMetrikFisikController extends Controller
{
    public function __construct(
        private TrainingLogRepository $catatanRepository
    ) {}

    /**
     * Display the index view based on role.
     */
    public function tampilData(Request $permintaan)
    {
        $role = $permintaan->user()->role->name ?? '';

        if ($role === 'Atlet') {
            return $this->athleteIndex($permintaan);
        }

        abort(403, 'Akses ditolak.');
    }

    /**
     * Store data based on role.
     */
    public function simpanData(Request $permintaan)
    {
        $role = $permintaan->user()->role->name ?? '';

        if ($role === 'Atlet') {
            return $this->athleteStore($permintaan);
        }

        abort(403, 'Akses ditolak.');
    }

    /**
     * Show detailed physical metrics.
     */
    public function show(Request $permintaan, User $atlet)
    {
        $role = $permintaan->user()->role->name ?? '';

        if ($role === 'Manajemen') {
            return $this->managementShow($permintaan, $atlet);
        }

        abort(403, 'Akses ditolak.');
    }

    /**
     * -----------------------------------------------------------------
     * ATLET METHODS
     * -----------------------------------------------------------------
     */
    private function athleteIndex(Request $permintaan): Response
    {
        $pengguna = $permintaan->user();

        $metrik = $pengguna->physicalMetrics()
            ->orderBy('recorded_at', 'desc')
            ->get();

        $pengguna->load('category');

        return Inertia::render('atlet/PhysicalProfile', [
            'metrics' => $metrik,
            'categories' => Kategori::orderBy('name')->get(),
        ]);
    }

    private function athleteStore(Request $permintaan)
    {
        $pengguna = $permintaan->user();

        if (! $pengguna->date_of_birth || ! $pengguna->gender) {
            $dataHilang = [];
            if (! $pengguna->date_of_birth) {
                $dataHilang[] = 'tanggal lahir';
            }
            if (! $pengguna->gender) {
                $dataHilang[] = 'jenis kelamin';
            }

            $pesan = 'Harap lengkapi '.implode(' dan ', $dataHilang).' di halaman Profil terlebih dahulu sebelum menginput data fisik.';

            return back()->withErrors(['profile_incomplete' => $pesan]);
        }

        if (! $pengguna->category_id) {
            return back()->withErrors(['category' => 'Harap beritahu pelatih untuk menentukan kategori Anda terlebih dahulu.']);
        }

        $dataTervalidasi = $permintaan->validate([
            'height' => 'required|numeric|min:50|max:250',
            'weight' => 'required|numeric|min:20|max:200',
            'recorded_at' => 'required|date',
        ]);

        $waktuPencatatan = Carbon::parse($dataTervalidasi['recorded_at'])->startOfDay();
        $tanggalLahir = $pengguna->date_of_birth;

        $usia = $waktuPencatatan->year - $tanggalLahir->year;
        if ($waktuPencatatan->month < $tanggalLahir->month || ($waktuPencatatan->month === $tanggalLahir->month && $waktuPencatatan->day < $tanggalLahir->day)) {
            $usia--;
        }
        $dataTervalidasi['age'] = max(0, $usia);
        $dataTervalidasi['category'] = $pengguna->category->name;

        $pengguna->physicalMetrics()->create($dataTervalidasi);

        return back()->with('success', 'Data fisik berhasil disimpan.');
    }

    /**
     * -----------------------------------------------------------------
     * MANAJEMEN METHODS
     * -----------------------------------------------------------------
     */
    private function managementShow(Request $permintaan, User $atlet)
    {
        $atlet->load(['physicalMetrics' => function ($kueri) {
            $kueri->orderBy('recorded_at', 'desc');
        }]);

        return Inertia::render('manajemen/AthletePhysicalReview', [
            'athlete' => $atlet,
        ]);
    }
}
