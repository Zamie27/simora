<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\LogLatihan;
use App\Models\ProfilAtlet;
use App\Models\Role;
use App\Models\User;
use App\Repositories\TrainingLogRepository;
use App\Services\TrainingLogService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class LihatRingkasanDaftarAtletController extends Controller
{
    public function __construct(
        private TrainingLogRepository $logRepository,
        private TrainingLogService $logService
    ) {}

    /**
     * Display a listing of the athletes.
     */
    public function tampilDaftar(Request $permintaan): Response
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
     * Display the specified athlete details.
     */
    public function tampilDetail(Request $permintaan, User $atlet): Response
    {
        $role = $permintaan->user()->role->name ?? '';

        if ($role === 'Manajemen') {
            return $this->managementShow($permintaan, $atlet);
        }

        if ($role === 'Pelatih') {
            return $this->coachShow($permintaan, $atlet);
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
        $daftarAtlet = User::whereRole('Atlet')
            ->with(['latestPhysicalMetric', 'coach', 'athleteProfile'])
            ->get();

        return Inertia::render('ringkasan-atlet/DaftarAtletManajemen', [
            'athletes' => $daftarAtlet,
            'categories' => Kategori::orderBy('name')->get(),
        ]);
    }

    private function managementShow(Request $permintaan, User $atlet): Response
    {
        $tanggalMulai = $permintaan->input('start_date');
        $tanggalSelesai = $permintaan->input('end_date');

        $atlet->load(['category', 'coach', 'athleteProfile', 'physicalMetrics' => function ($kueri) {
            $kueri->orderBy('recorded_at', 'desc');
        }]);

        $pelatihRole = Role::where('name', 'Pelatih')->first();

        $daftarCatatan = $this->logRepository->getForAthlete($atlet->id, $tanggalMulai, $tanggalSelesai);
        $statistik = $this->logRepository->getStatistics($atlet->id, $tanggalMulai, $tanggalSelesai);
        $trenPerforma = $this->logRepository->getPerformanceTrend($atlet->id, $tanggalMulai, $tanggalSelesai);

        return Inertia::render('ringkasan-atlet/DetailAtletManajemen', [
            'athlete' => $atlet,
            'coaches' => User::where('role_id', $pelatihRole?->id)->get(),
            'trainingLogs' => $daftarCatatan,
            'statistics' => $statistik,
            'performanceTrend' => $trenPerforma,
            'categories' => Kategori::orderBy('name')->get(),
            'filters' => [
                'start_date' => $tanggalMulai,
                'end_date' => $tanggalSelesai,
            ],
        ]);
    }

    /**
     * Update the assigned coach for an athlete (Manajemen only).
     */
    public function perbaruiPelatih(Request $permintaan, User $atlet): RedirectResponse
    {
        if ($permintaan->user()->role->name !== 'Manajemen') {
            abort(403);
        }

        $dataTervalidasi = $permintaan->validate([
            'coach_id' => 'nullable|exists:users,id',
        ]);

        $atlet->update([
            'coach_id' => $dataTervalidasi['coach_id'],
        ]);

        return back()->with('success', 'Pelatih pembina berhasil diperbarui.');
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
            ->with(['latestPhysicalMetric', 'athleteProfile'])
            ->get();

        return Inertia::render('ringkasan-atlet/DaftarAtletPelatih', [
            'athletes' => $daftarAtlet,
            'categories' => Kategori::orderBy('name')->get(),
        ]);
    }

    private function coachShow(Request $permintaan, User $atlet): Response
    {
        $this->authorizeAccess($atlet);

        $tanggalMulai = $permintaan->input('start_date');
        $tanggalSelesai = $permintaan->input('end_date');

        $atlet->load(['category', 'athleteProfile', 'physicalMetrics' => function ($kueri) {
            $kueri->orderBy('recorded_at', 'desc');
        }]);

        $daftarCatatan = $this->logRepository->getForAthlete($atlet->id, $tanggalMulai, $tanggalSelesai);
        $statistik = $this->logRepository->getStatistics($atlet->id, $tanggalMulai, $tanggalSelesai);
        $trenPerforma = $this->logRepository->getPerformanceTrend($atlet->id, $tanggalMulai, $tanggalSelesai);

        return Inertia::render('ringkasan-atlet/DetailAtletPelatih', [
            'athlete' => $atlet,
            'categories' => Kategori::orderBy('name')->get(),
            'trainingLogs' => $daftarCatatan,
            'statistics' => $statistik,
            'performanceTrend' => $trenPerforma,
            'filters' => [
                'start_date' => $tanggalMulai,
                'end_date' => $tanggalSelesai,
            ],
        ]);
    }

    public function simpanMetrikFisik(Request $permintaan, User $atlet)
    {
        $this->authorizeAccess($atlet);

        if (! $atlet->date_of_birth || ! $atlet->gender) {
            $dataHilang = [];
            if (! $atlet->date_of_birth) {
                $dataHilang[] = 'tanggal lahir';
            }
            if (! $atlet->gender) {
                $dataHilang[] = 'jenis kelamin';
            }

            $pesan = 'Atlet belum mengisi '.implode(' dan ', $dataHilang).' di profil mereka. Harap beritahu atlet untuk melengkapi profil.';

            return back()->withErrors(['profile_incomplete' => $pesan]);
        }

        if (! $atlet->category_id) {
            return back()->withErrors(['category' => 'Harap tentukan kategori atlet terlebih dahulu.']);
        }

        $dataTervalidasi = $permintaan->validate([
            'height' => 'required|numeric|min:50|max:250',
            'weight' => 'required|numeric|min:20|max:200',
            'recorded_at' => 'required|date',
        ]);

        $waktuPencatatan = Carbon::parse($dataTervalidasi['recorded_at'])->startOfDay();
        $tanggalLahir = $atlet->date_of_birth;

        $usia = $waktuPencatatan->year - $tanggalLahir->year;
        if ($waktuPencatatan->month < $tanggalLahir->month || ($waktuPencatatan->month === $tanggalLahir->month && $waktuPencatatan->day < $tanggalLahir->day)) {
            $usia--;
        }
        $dataTervalidasi['age'] = max(0, $usia);
        $dataTervalidasi['category'] = $atlet->category->name;

        $atlet->physicalMetrics()->create($dataTervalidasi);

        return back()->with('success', 'Data fisik atlet berhasil diperbarui.');
    }

    public function perbaruiKategori(Request $permintaan, User $atlet)
    {
        $this->authorizeAccess($atlet);

        $dataTervalidasi = $permintaan->validate([
            'category_id' => 'required|exists:categories,id',
        ]);

        $atlet->update($dataTervalidasi);

        return back()->with('success', 'Kategori atlet berhasil diperbarui.');
    }

    public function perbaruiLogLatihan(Request $permintaan, LogLatihan $catatan): RedirectResponse
    {
        if ($catatan->athlete->coach_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke data latihan ini.');
        }

        $dataTervalidasi = $permintaan->validate([
            'title' => 'nullable|string|max:255',
            'distance_km' => 'nullable|numeric|min:0',
            'duration_minutes' => 'nullable|integer|min:0',
            'avg_speed' => 'nullable|numeric|min:0',
            'rpm' => 'nullable|numeric|min:0',
            'intensity' => 'nullable|in:low,medium,high,very_high',
            'attendance_status' => 'required|in:present,absent,late,excused',
            'completion_status' => 'required|in:not_started,in_progress,completed,incomplete',
            'athlete_notes' => 'nullable|string',
            'coach_rating' => 'nullable|integer|min:1|max:5',
            'coach_evaluation' => 'nullable|string',
            'coach_comments' => 'nullable|string',
        ]);

        $this->logService->update($catatan, $dataTervalidasi);

        return back()->with('success', 'Log latihan atlet berhasil diperbarui.');
    }

    public function hapusLogLatihan(LogLatihan $catatan): RedirectResponse
    {
        if ($catatan->athlete->coach_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke data latihan ini.');
        }

        $catatan->delete();

        return back()->with('success', 'Log latihan atlet berhasil dihapus.');
    }

    private function authorizeAccess(User $atlet)
    {
        if ($atlet->coach_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke data atlet ini.');
        }
    }

    /**
     * UC-05: Lihat Ringkasan Daftar Atlet
     * Turunan: Menampilkan daftar kategori
     */
    public function tampilDaftarKategori(): Response
    {
        return Inertia::render('ringkasan-atlet/Kategori', [
            'categories' => Kategori::orderBy('name')->get(),
        ]);
    }

    /**
     * UC-05: Lihat Ringkasan Daftar Atlet
     * Turunan: Menyimpan kategori baru
     */
    public function simpanKategori(Request $permintaan)
    {
        $dataTervalidasi = $permintaan->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:1000',
        ]);

        Kategori::create($dataTervalidasi);

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * UC-05: Lihat Ringkasan Daftar Atlet
     * Turunan: Memperbarui kategori
     */
    public function perbaruiKategoriData(Request $permintaan, Kategori $kategori)
    {
        $dataTervalidasi = $permintaan->validate([
            'name' => 'required|string|max:255|unique:categories,name,'.$kategori->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $kategori->update($dataTervalidasi);

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * UC-05: Lihat Ringkasan Daftar Atlet
     * Turunan: Menghapus kategori
     */
    public function hapusKategori(Kategori $kategori)
    {
        $kategori->delete();

        return back()->with('success', 'Kategori berhasil dihapus.');
    }
}
