# Rencana Implementasi: Cakupan PHPUnit Test Komprehensif (Simora)

## Latar Belakang

Berdasarkan analisis menyeluruh terhadap:
- **SRS Fungsional (18 poin)** dan **Non-Fungsional (10 poin)**
- **15 Use Case Utama + 43 Use Case Turunan**
- **16 Controller** yang sudah ada
- **Routes** di `web.php` dan `settings.php`
- **Seluruh file test** yang sudah ada (14 file, 53 test)

---

## Hasil Analisis: Test yang Sudah Ada vs yang Belum Ada

### ✅ Use Case yang SUDAH Tercakup (Pertahankan Apa Adanya)

| File Test | UC yang Dicakup | Test Method |
|---|---|---|
| `Auth/AuthenticationTest.php` | UC-01.1 Login | Login OK, Invalid, Rate Limit, Logout, 2FA |
| `Auth/RegistrationTest.php` | UC-01.2 Register | Register OK |
| `Auth/PasswordResetTest.php` | UC-01.3 Lupa Password | Request & Reset |
| `Auth/EmailVerificationTest.php` | Email Verification | Verified, Redirect |
| `Auth/TwoFactorChallengeTest.php` | UC-01.1 (2FA) | Challenge |
| `Settings/ProfileUpdateTest.php` | UC-03.1, UC-03.2, UC-03.3 | View, Update, OTP Email, Delete Akun |
| `Settings/SecurityTest.php` | UC-03 (Password) | Update Password, 2FA |
| `AthleteDashboardTest.php` | UC-02.1, UC-02.2 | Akses Dashboard Atlet, Quick Update |
| `CoachDashboardTest.php` | UC-02.3 | Redirect & Akses Dashboard Pelatih |
| `BandingkanPerformaDanMemfilterRiwayatControllerTest.php` | UC-09.1, UC-09.2 | Manajer lihat semua, Pelatih lihat miliknya, RBAC |
| `RecurringTrainingLogTest.php` | UC-07 (Command) | Recurring Session Logic (4 skenario) |
| `DashboardTest.php` | UC-02 (general) | Guest redirect |

---

### ❌ Use Case yang BELUM Tercakup (Perlu Dibuat)

#### GRUP 1: UC-04 — Memverifikasi Pendaftaran & Menetapkan Pelatih
**Controller:** `MemverifikasiPendaftaranDanMenetapkanPelatihController`
- UC-04.1: Manajer dapat lihat daftar atlet belum terverifikasi
- UC-04.2: Manajer dapat verifikasi atlet dan tetapkan pelatih
- UC-04: Non-manajer ditolak akses (RBAC)
- UC-04: Manajer dapat tambah user baru (CRUD)
- UC-04: Manajer dapat perbarui dan hapus user

#### GRUP 2: UC-05 — Lihat Ringkasan Daftar Atlet
**Controller:** `LihatRingkasanDaftarAtletController`
- UC-05.1: Manajer dan Pelatih lihat daftar atlet
- UC-05.2: Manajer dan Pelatih lihat detail atlet
- UC-05.3: Manajer dapat ubah pelatih pembina atlet
- UC-05.4: Pelatih dapat update kategori atlet
- RBAC: Pelatih tidak bisa lihat atlet yang bukan binaannya

#### GRUP 3: UC-06 — Kelola Data Metrik Fisik (BMI)
**Controller:** `KelolaDataMetrikFisikController`
- UC-06.1: Atlet simpan data fisik (tinggi, berat, BMI)
- UC-06.2: Atlet lihat statistik BMI
- UC-06: Validasi profil incomplete (belum isi tanggal lahir/gender)
- UC-06: Non-atlet ditolak akses

#### GRUP 4: UC-07 — Kelola Jadwal Sesi Latihan
**Controller:** `KelolaJadwalSesiLatihanController`
- UC-07.1: Pelatih buat sesi latihan baru (termasuk validasi)
- UC-07.2: Pelatih lihat daftar & detail sesi latihannya
- UC-07.3: Pelatih hapus sesi latihannya; Tidak bisa hapus milik pelatih lain
- Manajemen: CRUD Jenis Latihan (exercise types)

#### GRUP 5: UC-08 — Analisa Grafik & Statistik Latihan (Atlet)
**Controller:** `AnalisaGrafikDanStatistikLatihanController`
- UC-08.1: Atlet lihat grafik & statistik latihan (view halaman)
- Atlet simpan log latihan manual (happy path + validasi)
- Atlet hapus log latihan miliknya; tidak bisa hapus milik atlet lain
- Filter berdasarkan rentang tanggal

#### GRUP 6: UC-10 — Lihat Laporan Riwayat Performa
**Controller:** `LihatLaporanRiwayatPerformaController`
- UC-10.1: Manajer & Pelatih lihat ringkasan laporan performa
- UC-10.2: Manajer export CSV semua atlet
- UC-10.3: Manajer/Pelatih export CSV satu atlet
- RBAC: Pelatih hanya bisa export data atletnya sendiri (403 jika atlet lain)
- Filter berdasarkan period/tanggal

#### GRUP 7: UC-11 — Kelola Evaluasi & Umpan Balik Latihan
**Controller:** `KelolaEvaluasiDanUmpanBalikLatihanController`
- UC-11.2: Pelatih update evaluasi pada log latihan atletnya
- Pelatih tidak bisa evaluasi log atlet yang bukan binaannya (403)

#### GRUP 8: UC-12 — Kelola Pesan
**Controller:** `KelolaPesanController`
- UC-12.1: Pelatih/Manajer kirim pesan ke atlet
- Atlet tandai pesan sudah dibaca (hanya receiver yang bisa)
- UC-12.3: Pengirim hapus pesan miliknya; Bukan pengirim ditolak (403)

#### GRUP 9: UC-13 — Kelola Event & Partisipasi
**Controller:** `KelolaEventDanPartisipasiController`
- UC-13.1: Pelatih/Manajer buat event baru dengan partisipasi atlet
- UC-13.2: View event per role (Manajer, Pelatih, Atlet)
- UC-13.3: Pelatih/Manajer hapus event; RBAC (Pelatih hanya event miliknya)
- Pelatih update partisipasi atlet pada event miliknya
- CRUD event type dan event point

#### GRUP 10: UC-14 — Kelola Dokumen Lisensi UCI
**Controller:** `KelolaDokumenLisensiUciController`
- UC-14.1/14.2: Manajer, Pelatih, Atlet lihat halaman dokumen UCI
- UC-14.3: Atlet upload dokumen pribadi (birth certificate, family card, ID)
- UC-14.4: Manajer update data UCI License (uci_id, valid date)
- Manajer hanya yang bisa update UCI; Pelatih ditolak (403)

#### GRUP 11: UC-02.4 — Dashboard Manajemen
**Controller:** `LihatDashboardController`
- UC-02.4: Manajer dapat akses dashboard manajemen (redirect + view)

#### GRUP 12: RBAC Access Control (SRS-NF-5)
- Test bahwa **Guest** tidak bisa akses protected routes
- Test bahwa **Atlet** tidak bisa akses route Pelatih dan Manajer
- Test bahwa **Pelatih** tidak bisa akses route Manajer

---

## Proposed Changes

### Tidak Ada Perubahan pada File yang Sudah Ada
Semua 14 file test yang ada dipertahankan apa adanya.

---

### File Baru yang Akan Dibuat (9 file)

#### [NEW] `tests/Feature/MemverifikasiPendaftaranDanMenetapkanPelatihTest.php`
Mencakup UC-04 (lengkap), termasuk CRUD user oleh Manajer dan flow verifikasi atlet.

#### [NEW] `tests/Feature/LihatRingkasanDaftarAtletTest.php`
Mencakup UC-05 (lengkap), termasuk RBAC antara Manajer dan Pelatih.

#### [NEW] `tests/Feature/KelolaDataMetrikFisikTest.php`
Mencakup UC-06 (lengkap), termasuk validasi profil incomplete.

#### [NEW] `tests/Feature/KelolaJadwalSesiLatihanTest.php`
Mencakup UC-07 CRUD (tidak termasuk recurring/command yang sudah ada).

#### [NEW] `tests/Feature/AnalisaGrafikDanStatistikLatihanTest.php`
Mencakup UC-08, termasuk simpan/hapus log latihan manual (Atlet).

#### [NEW] `tests/Feature/LihatLaporanRiwayatPerformaTest.php`
Mencakup UC-10, termasuk export CSV dan filter period.

#### [NEW] `tests/Feature/KelolaEvaluasiDanUmpanBalikLatihanTest.php`
Mencakup UC-11, khususnya perbaruiEvaluasi oleh Pelatih.

#### [NEW] `tests/Feature/KelolaPesanTest.php`
Mencakup UC-12, termasuk kirim, baca, dan hapus pesan.

#### [NEW] `tests/Feature/KelolaEventDanPartisipasiTest.php`
Mencakup UC-13, termasuk CRUD event dan partisipasi atlet.

> [!NOTE]
> UC-14 (Kelola Dokumen Lisensi UCI) dan UC-15 (Kalkulator Gear) tidak dijadikan test terpisah karena:
> - UC-14: Melibatkan file upload (Storage fake) — bisa ditambahkan sebagai test tambahan di iterasi berikutnya.
> - UC-15: Kalkulator Gear adalah fitur frontend murni (kalkulasi di sisi klien), tidak ada endpoint backend yang perlu ditest.

---

## Estimasi Total Test

| Kondisi | Jumlah |
|---|---|
| **Existing tests (dipertahankan)** | 53 test |
| **New test methods (estimasi)** | ~45–55 test baru |
| **Total estimasi akhir** | **~98–108 test** |

---

## Verification Plan

### Automated Tests
```bash
docker exec -it simora-app php artisan test --compact
```

### Per-File Verification (setelah setiap file selesai dibuat)
```bash
docker exec -it simora-app php artisan test --compact tests/Feature/[NamaFile].php
```

### Kriteria Selesai
- Semua 53 test yang sudah ada tetap `PASS`
- Semua test baru `PASS`
- Total tests `Tests: X passed (Y assertions)` tanpa `FAILED`
