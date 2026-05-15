<?php

use App\Http\Controllers\AnalisaGrafikDanStatistikLatihanController;
use App\Http\Controllers\Auth\DocumentAccessController;
use App\Http\Controllers\Auth\LihatDashboardController as DashboardDispatcher;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\BandingkanPerformaDanMemfilterRiwayatController;
use App\Http\Controllers\BugReportController;
use App\Http\Controllers\GunakanKalkulatorGearSepedaController;
use App\Http\Controllers\KelolaDataMetrikFisikController;
use App\Http\Controllers\KelolaDokumenLisensiUciController;
use App\Http\Controllers\KelolaEvaluasiDanUmpanBalikLatihanController;
use App\Http\Controllers\KelolaEventDanPartisipasiController;
use App\Http\Controllers\KelolaJadwalSesiLatihanController;
use App\Http\Controllers\KelolaPesanController;
use App\Http\Controllers\LihatDashboardController;
use App\Http\Controllers\LihatLaporanRiwayatPerformaController;
use App\Http\Controllers\LihatRingkasanDaftarAtletController;
use App\Http\Controllers\MemverifikasiPendaftaranDanMenetapkanPelatihController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

// Custom Email Verification Notification Route (Manual)
Route::post('email/verification-notification', [VerificationController::class, 'store'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verifikasi.send');

// Bug Report - accessible from all pages (even without login)
Route::post('bug-reports', [BugReportController::class, 'store'])->name('bug-reports.store');

Route::middleware(['auth', 'verified', 'verified-user'])->group(function () {
    Route::get('dashboard', DashboardDispatcher::class)->name('dashboard');

    // Tools
    Route::get('tools/gear-calculator', [GunakanKalkulatorGearSepedaController::class, 'tampilHalamanKalkulator'])->name('tools.gear-calculator');

    // Shared Document Access
    Route::get('documents/{atlet}/{tipe}', [DocumentAccessController::class, 'show'])->name('dokumen.show');

    // Consolidated Document & License Management
    Route::get('lisensi-uci', [KelolaDokumenLisensiUciController::class, 'tampilHalamanLisensi'])->name('lisensi-uci.index');
    Route::post('lisensi-uci/upload', [KelolaDokumenLisensiUciController::class, 'simpanDokumenPribadi'])->name('lisensi-uci.upload');
    Route::post('lisensi-uci/update/{atlet}', [KelolaDokumenLisensiUciController::class, 'perbaruiLisensiUci'])->name('lisensi-uci.update');
    Route::get('lisensi-uci/download-all/{atlet}', [KelolaDokumenLisensiUciController::class, 'unduhSemuaDokumen'])->name('lisensi-uci.download-all');

    // Management Routes
    Route::middleware(['role:Manajemen'])->prefix('manajemen')->name('manajemen.')->group(function () {
        Route::get('dashboard', [LihatDashboardController::class, 'tampilDashboard'])->name('dashboard');
        Route::get('users', [MemverifikasiPendaftaranDanMenetapkanPelatihController::class, 'tampilDaftar'])->name('pengguna.index');
        Route::post('users', [MemverifikasiPendaftaranDanMenetapkanPelatihController::class, 'simpanData'])->name('pengguna.store');
        Route::patch('users/{pengguna}', [MemverifikasiPendaftaranDanMenetapkanPelatihController::class, 'perbaruiData'])->name('pengguna.update');
        Route::delete('users/{pengguna}', [MemverifikasiPendaftaranDanMenetapkanPelatihController::class, 'hapusData'])->name('pengguna.destroy');

        // Athlete Verification & Coaching
        Route::get('pending', [MemverifikasiPendaftaranDanMenetapkanPelatihController::class, 'tampilDaftarTertunda'])->name('pengguna.tertunda');
        Route::post('users/{pengguna}/verify', [MemverifikasiPendaftaranDanMenetapkanPelatihController::class, 'verifikasiPendaftaran'])->name('pengguna.verifikasi');
        Route::get('atlet', [MemverifikasiPendaftaranDanMenetapkanPelatihController::class, 'tampilDaftarAtlet'])->name('atlet.index');
        Route::get('atlet/{atlet}', [LihatRingkasanDaftarAtletController::class, 'tampilDetail'])->name('atlet.show');
        Route::patch('atlet/{atlet}/coach', [LihatRingkasanDaftarAtletController::class, 'perbaruiPelatih'])->name('atlet.coach.update');

        // Category Management
        Route::get('kategori', [LihatRingkasanDaftarAtletController::class, 'tampilDaftarKategori'])->name('kategori.index');
        Route::post('kategori', [LihatRingkasanDaftarAtletController::class, 'simpanKategori'])->name('kategori.store');
        Route::put('kategori/{kategori}', [LihatRingkasanDaftarAtletController::class, 'perbaruiKategoriData'])->name('kategori.update');
        Route::delete('kategori/{kategori}', [LihatRingkasanDaftarAtletController::class, 'hapusKategori'])->name('kategori.destroy');

        // Exercise Type Management
        Route::get('jenis-latihan', [KelolaJadwalSesiLatihanController::class, 'tampilDaftarJenisLatihan'])->name('jenis-latihan.index');
        Route::post('jenis-latihan', [KelolaJadwalSesiLatihanController::class, 'simpanJenisLatihan'])->name('jenis-latihan.store');
        Route::put('jenis-latihan/{jenisLatihan}', [KelolaJadwalSesiLatihanController::class, 'perbaruiJenisLatihan'])->name('jenis-latihan.update');
        Route::delete('jenis-latihan/{jenisLatihan}', [KelolaJadwalSesiLatihanController::class, 'hapusJenisLatihan'])->name('jenis-latihan.destroy');

        // Reports
        Route::get('laporan', [LihatLaporanRiwayatPerformaController::class, 'tampilData'])->name('laporan.index');
        Route::post('laporan/export', [LihatLaporanRiwayatPerformaController::class, 'eksporData'])->name('laporan.export');

        // Event Settings
        Route::get('pengaturan-acara', [KelolaEventDanPartisipasiController::class, 'tampilData'])->name('pengaturan-acara.index');
        Route::post('tipe-acara', [KelolaEventDanPartisipasiController::class, 'simpanTipe'])->name('tipe-acara.store');
        Route::patch('tipe-acara/{tipe}', [KelolaEventDanPartisipasiController::class, 'perbaruiTipe'])->name('tipe-acara.update');
        Route::delete('tipe-acara/{tipe}', [KelolaEventDanPartisipasiController::class, 'hapusTipe'])->name('tipe-acara.destroy');
        Route::post('poin-acara', [KelolaEventDanPartisipasiController::class, 'simpanPoin'])->name('poin-acara.store');
        Route::patch('poin-acara/{poin}', [KelolaEventDanPartisipasiController::class, 'perbaruiPoin'])->name('poin-acara.update');
        Route::delete('poin-acara/{poin}', [KelolaEventDanPartisipasiController::class, 'hapusPoin'])->name('poin-acara.destroy');

        // Messages
        Route::post('pesan', [KelolaPesanController::class, 'simpanPesan'])->name('pesan.store');
        Route::delete('pesan/{pesan}', [KelolaPesanController::class, 'hapusPesan'])->name('pesan.destroy');
    });

    // Coach specific routes
    Route::middleware(['role:Pelatih'])->prefix('pelatih')->name('pelatih.')->group(function () {
        Route::get('dashboard', [LihatDashboardController::class, 'tampilDashboard'])->name('dashboard');
        Route::get('atlet', [LihatRingkasanDaftarAtletController::class, 'tampilDaftar'])->name('atlet.index');
        Route::get('atlet/{atlet}', [LihatRingkasanDaftarAtletController::class, 'tampilDetail'])->name('atlet.show');
        Route::patch('atlet/{atlet}/category', [LihatRingkasanDaftarAtletController::class, 'perbaruiKategori'])->name('atlet.category.update');
        Route::post('atlet/{atlet}/metrics', [LihatRingkasanDaftarAtletController::class, 'simpanMetrikFisik'])->name('atlet.metrics.store');

        // Training Sessions (UC-07: Kelola Jadwal Sesi Latihan)
        Route::get('sesi-latihan', [KelolaJadwalSesiLatihanController::class, 'tampilDaftar'])->name('sesi-latihan.index');
        Route::post('sesi-latihan', [KelolaJadwalSesiLatihanController::class, 'simpan'])->name('sesi-latihan.store');
        Route::get('sesi-latihan/{sesiLatihan}', [KelolaJadwalSesiLatihanController::class, 'tampilDetail'])->name('sesi-latihan.show');
        Route::delete('sesi-latihan/{sesiLatihan}', [KelolaJadwalSesiLatihanController::class, 'hapus'])->name('sesi-latihan.destroy');

        Route::patch('riwayat-latihan/{catatan}/evaluation', [KelolaEvaluasiDanUmpanBalikLatihanController::class, 'perbaruiEvaluasi'])->name('riwayat-latihan.evaluation');
        Route::patch('riwayat-latihan/{catatan}', [LihatRingkasanDaftarAtletController::class, 'perbaruiLogLatihan'])->name('riwayat-latihan.update');
        Route::delete('riwayat-latihan/{catatan}', [LihatRingkasanDaftarAtletController::class, 'hapusLogLatihan'])->name('riwayat-latihan.destroy');

        // Performance Comparison
        Route::get('komparasi-performa', [BandingkanPerformaDanMemfilterRiwayatController::class, 'tampilHalaman'])->name('komparasi.comparison');
        Route::get('komparasi-performa/data', [BandingkanPerformaDanMemfilterRiwayatController::class, 'ambilDataKomparasi'])->name('komparasi.comparison.data');

        // Reports
        Route::get('laporan', [LihatLaporanRiwayatPerformaController::class, 'tampilData'])->name('laporan.index');
        Route::post('laporan/export', [LihatLaporanRiwayatPerformaController::class, 'eksporData'])->name('laporan.export');

        // Events
        Route::get('acara', [KelolaEventDanPartisipasiController::class, 'tampilData'])->name('acara.index');
        Route::post('acara', [KelolaEventDanPartisipasiController::class, 'simpanData'])->name('acara.store');
        Route::patch('acara/{acara}', [KelolaEventDanPartisipasiController::class, 'perbaruiData'])->name('acara.update');
        Route::delete('acara/{acara}', [KelolaEventDanPartisipasiController::class, 'hapusData'])->name('acara.destroy');
        Route::patch('acara/{acara}/athletes/{atlet}', [KelolaEventDanPartisipasiController::class, 'perbaruiPartisipasi'])->name('acara.participation.update');

        // Event Settings
        Route::post('tipe-acara', [KelolaEventDanPartisipasiController::class, 'simpanTipeEvent'])->name('tipe-acara.store');
        Route::patch('tipe-acara/{tipe}', [KelolaEventDanPartisipasiController::class, 'perbaruiTipeEvent'])->name('tipe-acara.update');
        Route::delete('tipe-acara/{tipe}', [KelolaEventDanPartisipasiController::class, 'hapusTipeEvent'])->name('tipe-acara.destroy');
        Route::post('poin-acara', [KelolaEventDanPartisipasiController::class, 'simpanPoinEvent'])->name('poin-acara.store');
        Route::patch('poin-acara/{poin}', [KelolaEventDanPartisipasiController::class, 'perbaruiPoinEvent'])->name('poin-acara.update');
        Route::delete('poin-acara/{poin}', [KelolaEventDanPartisipasiController::class, 'hapusPoinEvent'])->name('poin-acara.destroy');

        // Messages
        Route::post('pesan', [KelolaPesanController::class, 'simpanPesan'])->name('pesan.store');
        Route::delete('pesan/{pesan}', [KelolaPesanController::class, 'hapusPesan'])->name('pesan.destroy');
    });

    // Athlete specific routes
    Route::middleware(['role:Atlet'])->prefix('atlet')->name('atlet.')->group(function () {
        Route::get('dashboard', [LihatDashboardController::class, 'tampilDashboard'])->name('dashboard');
        Route::post('dashboard/quick-update', [LihatDashboardController::class, 'perbaruiDataCepat'])->name('dashboard.quick-update');

        Route::get('fisik', [KelolaDataMetrikFisikController::class, 'tampilData'])->name('fisik.index');
        Route::post('fisik', [KelolaDataMetrikFisikController::class, 'simpanData'])->name('fisik.store');

        // Training
        Route::get('latihan', [AnalisaGrafikDanStatistikLatihanController::class, 'tampilData'])->name('latihan.index');
        Route::post('latihan/riwayat', [AnalisaGrafikDanStatistikLatihanController::class, 'simpanLogLatihan'])->name('latihan.log.store');
        Route::delete('latihan/riwayat/{catatan}', [AnalisaGrafikDanStatistikLatihanController::class, 'hapusLogLatihan'])->name('latihan.log.destroy');

        // Events
        Route::get('acara', [KelolaEventDanPartisipasiController::class, 'tampilData'])->name('acara.index');

        // Documents (Lisensi UCI)
        Route::get('dokumen', [KelolaDokumenLisensiUciController::class, 'tampilData'])->name('dokumen.index');
        Route::post('dokumen', [KelolaDokumenLisensiUciController::class, 'simpanData'])->name('dokumen.store');

        // Messages
        Route::patch('pesan/{pesan}/read', [KelolaPesanController::class, 'tandaiSudahDibaca'])->name('pesan.read');
    });

    // Report specific routes
    Route::middleware(['role:Report'])->prefix('report')->name('report.')->group(function () {
        Route::get('dashboard', [LihatDashboardController::class, 'tampilDashboard'])->name('dashboard');
        Route::patch('bug-reports/{laporanBug}/status', [BugReportController::class, 'updateStatus'])->name('bug-reports.status');
    });

    // Verification Pending Route (for athletes)
    Route::inertia('menunggu-verifikasi', 'auth/PendingVerification')->name('verifikasi.pending');
});

require __DIR__.'/settings.php';
