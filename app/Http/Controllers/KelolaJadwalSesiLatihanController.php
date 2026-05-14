<?php

namespace App\Http\Controllers;

use App\Models\JenisLatihan;
use App\Models\SesiLatihan;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KelolaJadwalSesiLatihanController extends Controller
{

    /**
     * UC-07: Kelola Jadwal Sesi Latihan
     * Turunan: Menampilkan daftar sesi latihan
     */
    public function tampilDaftar(Request $permintaan): Response
    {
        $pelatihId = $permintaan->user()->id;

        return Inertia::render('pelatih/TrainingSessions', [
            'sessions' => SesiLatihan::where('coach_id', $pelatihId)
                ->with(['exerciseType', 'athletes' => fn ($q) => $q->with('athleteProfile')])
                ->withCount('athletes')
                ->orderBy('scheduled_date', 'desc')
                ->get(),
            'exerciseTypes' => JenisLatihan::all(),
            'athletes' => User::whereRole('Atlet')
                ->where('coach_id', $pelatihId)
                ->where('is_verified', true)
                ->with(['athleteProfile'])
                ->get(['id', 'name', 'avatar']),
        ]);
    }

    /**
     * UC-07: Kelola Jadwal Sesi Latihan
     * Turunan: Menyimpan sesi latihan baru
     */
    public function simpan(Request $permintaan)
    {
        $dataTervalidasi = $permintaan->validate([
            'title' => 'required|string|max:255',
            'exercise_type_id' => 'required|exists:exercise_types,id',
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'required',
            'location' => 'nullable|string|max:255',
            'repeat_weekly' => 'boolean',
            'athlete_ids' => 'required|array',
            'athlete_ids.*' => 'exists:users,id',
        ]);

        $atletIds = $dataTervalidasi['athlete_ids'];
        unset($dataTervalidasi['athlete_ids']);

        $dataTervalidasi['coach_id'] = $permintaan->user()->id;

        $sesi = SesiLatihan::create($dataTervalidasi);
        $sesi->athletes()->sync($atletIds);

        return back()->with('success', 'Sesi latihan berhasil ditambahkan.');
    }

    /**
     * UC-07: Kelola Jadwal Sesi Latihan
     * Turunan: Menampilkan detail sesi latihan
     */
    public function tampilDetail(SesiLatihan $sesiLatihan): Response
    {
        if ($sesiLatihan->coach_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke sesi latihan ini.');
        }

        $sesiLatihan->load([
            'exerciseType',
            'athletes:id,name,email,avatar',
            'logs' => fn ($q) => $q->with(['athlete:id,name,email,avatar', 'attachments'])->orderBy('date', 'desc'),
        ]);
        $sesiLatihan->athletes->load('athleteProfile');
        $sesiLatihan->logs->pluck('athlete')->each->load('athleteProfile');

        return Inertia::render('pelatih/TrainingSessionDetail', [
            'session' => $sesiLatihan,
        ]);
    }

    /**
     * UC-07: Kelola Jadwal Sesi Latihan
     * Turunan: Menghapus sesi latihan
     */
    public function hapus(SesiLatihan $sesiLatihan)
    {
        if ($sesiLatihan->coach_id !== auth()->id()) {
            abort(403);
        }

        $sesiLatihan->delete();

        return back()->with('success', 'Sesi latihan berhasil dihapus.');
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
