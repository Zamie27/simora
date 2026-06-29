# Hasil Analisis Use Case & Alur Sistem SIMORA

Dokumen ini berisi daftar Use Case Utama, Use Case Turunan, dan Skenario Use Case yang diekstrak dari dokumen `UsecaseBab3.pdf`, dilengkapi dengan perbandingan implementasi di codebase SIMORA saat ini.

---

## 1. Daftar Use Case & Use Case Turunan

Berikut adalah daftar lengkap Use Case yang ditemukan di dokumen `UsecaseBab3.pdf` beserta turunannya:

### **UC-01 Autentikasi Pengguna**
*   **Aktor Utama**: Atlet, Pelatih, Manajemen
*   **Kondisi**: Menyediakan mekanisme masuk, pendaftaran, dan pemulihan kata sandi.
*   *   **UC-01.1 Login**
        *   **Aktor**: Atlet, Pelatih, Manajemen
        *   **Skenario**: Membuka halaman login -> Memasukkan email & password -> Sistem memvalidasi kredensial -> (Khusus Atlet) Sistem memeriksa status verifikasi -> Membuat token sesi -> Redirect ke dashboard masing-masing role.
    *   **UC-01.2 Register**
        *   **Aktor**: Calon Atlet
        *   **Skenario**: Membuka halaman registrasi -> Mengisi data (Nama, Email, Password, Data Diri) -> Sistem memvalidasi & menyimpan data baru dengan status "Unverified" dan role "Atlet" -> Menampilkan pesan sukses registrasi (menunggu verifikasi Manajemen).
    *   **UC-01.3 Lupa Password**
        *   **Aktor**: Atlet, Pelatih, Manajemen
        *   **Skenario**: Klik "Lupa Password" -> Input email pemulihan -> Sistem memvalidasi email -> Sistem mengirimkan token & link reset password -> Aktor membuka tautan -> Memasukkan password baru -> Sistem memperbarui password di database.

### **UC-02 Lihat Dashboard**
*   **Aktor Utama**: Atlet, Pelatih, Manajemen
*   **Kondisi**: Menampilkan ringkasan aktivitas, visualisasi performa, dan rekapitulasi personal.
*   *   **UC-02.1 Lihat Dashboard Atlet**
        *   **Aktor**: Atlet
        *   **Skenario**: Masuk halaman dashboard -> Sistem mengambil statistik latihan 7 hari terakhir (Jarak, Durasi, Kalori), tren performa (7 log terakhir), dan 3 event mendatang -> Merender grafik interaktif ApexCharts.
    *   **UC-02.2 Update Cepat (atlet)**
        *   **Aktor**: Atlet
        *   **Skenario**: Mengisi form "Update Cepat" (Berat/Tinggi badan atau Data Latihan) langsung di dashboard -> Sistem memvalidasi & menyimpan data fisik (PhysicalMetric harian) -> Dashboard diperbarui secara instan.
    *   **UC-02.3 Lihat Dashboard Pelatih**
        *   **Aktor**: Pelatih
        *   **Skenario**: Masuk halaman dashboard pelatih -> Sistem menghitung statistik mingguan agregat seluruh atlet bimbingan, memuat 5 sesi latihan mendatang, dan 10 log aktivitas terbaru -> Menampilkan ranking atlet & pengelompokan kategori dalam grafik.
    *   **UC-02.4 Lihat Dashboard Manajemen**
        *   **Aktor**: Manajemen
        *   **Skenario**: Masuk halaman dashboard manajemen -> Sistem mengambil total statistik atlet, pelatih, rasio verifikasi pengguna, tren performa global (jarak total 7 hari terakhir), 5 aktivitas terbaru, dan sesi latihan aktif -> Menampilkan ringkasan eksekutif secara visual.

### **UC-03 Kelola Profil Pengguna**
*   **Aktor Utama**: Atlet, Pelatih, Manajemen
*   **Kondisi**: Pengelolaan data diri, foto profil, dan penghapusan akun.
*   *   **UC-03.1 Lihat Profil Pengguna**
        *   **Aktor**: Atlet, Pelatih, Manajemen
        *   **Skenario**: Klik menu Pengaturan Profil -> Sistem memuat data profil aktif -> (Khusus Atlet) Menampilkan Kategori Atlet (Read-only).
    *   **UC-03.2 Ubah Profil Pengguna**
        *   **Aktor**: Atlet, Pelatih, Manajemen
        *   **Skenario**: Unggah foto profil baru (Avatar) -> Mengubah data diri -> (Opsional) Klik "Ubah Email" untuk memicu pengiriman OTP ke email -> Memasukkan OTP -> Validasi OTP berhasil -> Simpan perubahan data ke database.
    *   **UC-03.3 Hapus Akun Pengguna**
        *   **Aktor**: Atlet, Pelatih, Manajemen
        *   **Skenario**: Klik "Delete Account" -> Dialog konfirmasi -> Memasukkan password sebagai verifikasi -> Sistem memverifikasi password -> Menghapus user dari database -> Logout otomatis.

### **UC-04 Memverifikasi Pendaftaran & Menetapkan Pelatih**
*   **Aktor Utama**: Manajemen
*   **Kondisi**: Meninjau pendaftaran atlet baru, menetapkan status aktif, dan memilih pelatih pendamping.
*   *   **UC-04.1 Lihat daftar Atlet Belum Terverifikasi**
        *   **Aktor**: Manajemen
        *   **Skenario**: Klik navigasi Verifikasi User Baru -> Sistem mengambil data atlet dengan status `is_verified = false` dan memuat daftar seluruh pelatih -> Menampilkan dalam bentuk kartu (card).
    *   **UC-04.2 Ubah Status Terverifikasi dan Menentukan Pelatih**
        *   **Aktor**: Manajemen
        *   **Skenario**: Klik "Verifikasi & Assign Coach" -> Menampilkan modal -> Memilih Pelatih dari dropdown -> Klik "Setujui Pendaftaran" -> Sistem mengubah `is_verified` menjadi `true` & mengisi `coach_id` -> Mengirim notifikasi `AccountActivated` ke email atlet -> Menghapus atlet dari antrean verifikasi.

### **UC-05 Lihat Ringkasan Daftar Atlet**
*   **Aktor Utama**: Pelatih, Manajemen
*   **Kondisi**: Pemantauan populasi atlet serta penyesuaian administratif (pelatih/kategori).
*   *   **UC-05.1 Lihat Daftar Atlet**
        *   **Aktor**: Pelatih, Manajemen
        *   **Skenario**: Masuk menu "Daftar Atlet" -> Sistem memeriksa role (jika Manajemen: memuat semua atlet; jika Pelatih: hanya memuat atlet dengan `coach_id` miliknya) -> Menampilkan tabel daftar atlet.
    *   **UC-05.2 Lihat Detail Atlet**
        *   **Aktor**: Pelatih, Manajemen
        *   **Skenario**: Klik "Lihat Detail" -> (Khusus Pelatih) Verifikasi relasi binaan (jika tidak cocok, tolak 403) -> Memuat relasi data (profil, kategori, metrik fisik terbaru) -> Memuat grafik performa dan tabel riwayat.
    *   **UC-05.3 Perbarui Pelatih Pembina**
        *   **Aktor**: Manajemen
        *   **Skenario**: Di halaman detail atlet -> Memilih pelatih baru dari dropdown -> Klik simpan -> Sistem memvalidasi otorisasi & keberadaan `coach_id` -> Memperbarui `coach_id` di database -> Alert sukses.
    *   **UC-05.4 Perbarui Kategori Atlet**
        *   **Aktor**: Pelatih
        *   **Skenario**: Di halaman detail atlet binaan -> Memilih kategori baru dari dropdown -> Klik "Perbarui Kategori" -> Sistem memvalidasi akses & ID Kategori -> Memperbarui data kategori atlet di database -> Alert sukses.

### **UC-06 Kelola Data Metrik Fisik (BMI)**
*   **Aktor Utama**: Atlet, Pelatih, Manajemen
*   **Kondisi**: Pencatatan perkembangan fisik, berat, tinggi, usia, dan kalkulasi BMI otomatis.
*   *   **UC-06.1 Update BMI Atlet**
        *   **Aktor**: Atlet
        *   **Skenario**: Klik "Update Fisik Baru" -> Mengisi Tinggi (cm), Berat (kg), dan Tanggal Perekaman -> Sistem memvalidasi profil (Tanggal Lahir & Gender) -> Menghitung usia atlet pada tanggal perekaman -> Menyimpan ke tabel `physical_metrics` -> Grafik & tabel riwayat terupdate secara otomatis.
    *   **UC-06.2 Lihat Statistik BMI Atlet**
        *   **Aktor**: Atlet, Pelatih, Manajemen
        *   **Skenario**: Membuka halaman Profil Fisik / Detail Atlet -> Sistem mengambil riwayat fisik -> Merender grafik garis ApexCharts (Berat vs Tinggi) -> Menampilkan kartu "Pengukuran Terakhir" dengan nilai BMI & Status (Normal, Overweight, dsb).
    *   **UC-06.3 Update Tanggal Lahir Atlet**
        *   **Aktor**: Atlet
        *   **Skenario**: Di halaman Pengaturan Profil -> Memilih tanggal lahir di datepicker -> Klik Simpan -> Memperbarui `date_of_birth` di database -> Menghilangkan warning "Lengkapi Profil" pada halaman Profil Fisik.
    *   **UC-06.4 Lihat Tanggal Lahir Atlet**
        *   **Aktor**: Atlet, Pelatih
        *   **Skenario**: Membuka halaman Profil Fisik / Detail Atlet -> Sistem memuat data dan menampilkan usia atlet saat ini secara real-time berdasarkan tanggal lahir.

### **UC-07 Kelola Jadwal Sesi Latihan**
*   **Aktor Utama**: Pelatih, Atlet
*   **Kondisi**: Pembuatan program latihan, distribusi ke atlet, dan monitoring sesi latihan.
*   *   **UC-07.1 Buat Sesi Latihan**
        *   **Aktor**: Pelatih
        *   **Skenario**: Klik "Tambah Sesi Latihan" -> Isi form (Judul, Tanggal, Jenis Latihan, Target Performa) -> Klik "Simpan" -> Sistem memvalidasi & menyimpan sesi baru -> Sinkronisasi ke kalender atlet terpilih -> Alert sukses.
    *   **UC-07.2 Lihat Sesi Latihan**
        *   **Aktor**: Pelatih, Atlet
        *   **Skenario**: Atlet membuka "Latihan Saya" / Pelatih membuka "Jadwal Latihan" -> Sistem mengambil dan menampilkan sesi latihan yang tersedia.
    *   **UC-07.3 Hapus Sesi Latihan**
        *   **Aktor**: Pelatih
        *   **Skenario**: Klik "Hapus" pada sesi latihan -> Tampil konfirmasi -> Klik "Ya, Lanjutkan" -> Sistem memvalidasi hak kepemilikan sesi -> Menghapus sesi beserta relasi peserta -> Alert sukses.

### **UC-08 Analisa Grafik & Statistik Latihan**
*   **Aktor Utama**: Atlet, Pelatih, Manajemen
*   **Kondisi**: Visualisasi progres latihan berbasis tanggal.
*   *   **UC-08.1 Lihat Grafik & Statistik Atlet**
        *   **Aktor**: Atlet, Pelatih, Manajemen
        *   **Skenario**: Klik menu Latihan -> (Untuk Pelatih/Manajemen: Klik nama atlet dari daftar terlebih dahulu) -> Sistem memuat data log latihan dan menyajikan dalam grafik ApexCharts (Speed, RPM, Detak Jantung, Jarak, Kalori) serta widget ringkasan.

### **UC-09 Bandingkan Performa & Memfilter Riwayat**
*   **Aktor Utama**: Pelatih, Manajemen
*   **Kondisi**: Komparasi parameter performa secara berdampingan dan penyaringan riwayat.
*   *   **UC-09.1 Lihat Daftar Performa Atlet**
        *   **Aktor**: Pelatih, Manajemen
        *   **Skenario**: Buka menu "Perbandingan Performa" -> Sistem mengambil daftar atlet (jika Pelatih: atlet binaannya; jika Manajemen: semua atlet) -> Menampilkan dalam tombol pilihan (selection chips).
    *   **UC-09.2 Bandingkan Performa Atlet**
        *   **Aktor**: Pelatih, Manajemen
        *   **Skenario**: Memilih minimal 2 atlet -> Klik "Bandingkan" -> Sistem memproses data tren & perbandingan rata-rata -> Menampilkan kartu ringkasan performa masing-masing atlet dan grafik batang perbandingan (Bar Comparison) untuk Jarak, Kecepatan, dan Durasi.

### **UC-10 Lihat Laporan Riwayat Performa**
*   **Aktor Utama**: Pelatih, Manajemen
*   **Kondisi**: Rekapitulasi performa dan ekspor data ke file CSV.
*   *   **UC-10.1 Lihat Ringkasan Performa Atlet**
        *   **Aktor**: Pelatih, Manajemen
        *   **Skenario**: Masuk menu "Laporan Performa" -> Sistem memuat ringkasan performa agregat atlet -> Menampilkan tabel ringkasan per atlet.
    *   **UC-10.2 Export CSV Semua Performa Atlet**
        *   **Aktor**: Pelatih, Manajemen
        *   **Skenario**: Menentukan periode laporan (misal: tahun ini) -> Klik "Export CSV" tanpa memilih atlet -> Sistem memuat log semua atlet dalam rentang tanggal -> Menyusun format CSV -> Mengunduh berkas `Laporan_Seluruh_Atlet.csv`.
    *   **UC-10.3 Export CSV Salah Satu Performa Atlet**
        *   **Aktor**: Pelatih, Manajemen
        *   **Skenario**: Memilih atlet dari dropdown -> Menentukan tanggal -> Klik "Export CSV" -> Sistem memvalidasi data -> Streaming download file ZIP/CSV dengan nama mencantumkan nama atlet terkait.

### **UC-11 Kelola Evaluasi & Umpan Balik Latihan**
*   **Aktor Utama**: Pelatih, Atlet
*   **Kondisi**: Penilaian kualitatif dan kuantitatif terhadap log latihan atlet.
*   *   **UC-11.1 Lihat Evaluasi & Umpan Balik Latihan**
        *   **Aktor**: Atlet, Pelatih
        *   **Skenario**: Aktor membuka detail riwayat latihan/log -> Sistem memuat data penilaian pelatih (`coach_rating`, `coach_evaluation`, `coach_comments`) -> Menampilkan indikator rating bintang/angka (1-10) serta teks evaluasi teknis.
    *   **UC-11.2 Update Evaluasi & Umpan Balik Latihan**
        *   **Aktor**: Pelatih
        *   **Skenario**: Di halaman detail sesi latihan/log atlet -> Klik "Evaluasi" -> Form panel evaluasi tampil -> Mengisi/memperbarui data -> Klik "Simpan Evaluasi" -> Sistem menyimpan evaluasi -> Alert sukses.

### **UC-12 Kelola Pesan**
*   **Aktor Utama**: Pelatih, Manajemen, Atlet
*   **Kondisi**: Komunikasi satu arah berupa catatan instruksi/motivasi dari staf ke atlet.
*   *   **UC-12.1 Kirim Pesan**
        *   **Aktor**: Pelatih, Manajemen
        *   **Skenario**: Buka form "Kirim Pesan/Catatan" -> Pilih Atlet -> Tulis pesan (maks 1000 karakter) -> Klik "Kirim" -> Sistem memvalidasi dan menyimpan ke tabel `messages` -> Alert sukses & muncul notifikasi di dashboard atlet.
    *   **UC-12.2 Lihat Pesan**
        *   **Aktor**: Atlet
        *   **Skenario**: Atlet masuk Dashboard -> Sistem memuat daftar pesan di mana `receiver_id` adalah ID Atlet -> Menampilkan pesan dengan status "Belum Dibaca" (`is_read = false`) -> Klik "Tandai Dibaca" -> Sistem memperbarui `is_read = true` -> Tampilan pesan meredup/transparan.
    *   **UC-12.3 Hapus Pesan**
        *   **Aktor**: Pelatih
        *   **Skenario**: Pelatih melihat daftar pesan yang dikirim -> Klik ikon "Hapus" (X) -> Konfirmasi -> Klik "Ya, Lanjutkan" -> Sistem menghapus pesan secara permanen -> Alert sukses.

### **UC-13 Kelola Event & Partisipasi**
*   **Aktor Utama**: Pelatih, Manajemen, Atlet
*   **Kondisi**: Pengelolaan kalender perlombaan (event) dan partisipasi atlet.
*   *   **UC-13.1 Buat Event & Menetapkan Partisipasi**
        *   **Aktor**: Pelatih (Catatan: Kode program juga mengizinkan role Manajemen)
        *   **Skenario**: Klik "Tambah Event Baru" -> Isi form event -> Simpan -> Event tersimpan di database dan muncul di jadwal atlet terpilih.
    *   **UC-13.2 Lihat Event & Menetapkan Partisipasi**
        *   **Aktor**: Pelatih, Atlet, Manajemen
        *   **Skenario**: Membuka menu Event -> Sistem mengambil daftar event (mendatang/lampau) -> Menampilkan data event dan detail hasil partisipasi.
    *   **UC-13.3 Hapus Event & Menetapkan Partisipasi**
        *   **Aktor**: Pelatih
        *   **Skenario**: Klik "Hapus" pada event -> Dialog konfirmasi -> Klik "Ya, Lanjutkan" -> Sistem menghapus event beserta relasi partisipasi atlet -> Alert sukses.

### **UC-14 Kelola Dokumen Lisensi UCI**
*   **Aktor Utama**: Atlet, Pelatih, Manajemen
*   **Kondisi**: Penyimpanan berkas identitas pribadi dan nomor lisensi resmi Union Cycliste Internationale (UCI).
*   *   **UC-14.1 Lihat Dokumen Atlet dan Lihat Lisensi UCI**
        *   **Aktor**: Atlet, Pelatih, Manajemen
        *   **Skenario**: Klik menu "Lisensi & Dokumen" -> Sistem memuat daftar dokumen -> Memilih dokumen -> Menampilkan status dan pratinjau dokumen beserta ID UCI, masa berlaku, dan berkas digital.
    *   **UC-14.2 Update Dokumen Atlet**
        *   **Aktor**: Atlet
        *   **Skenario**: Memilih berkas (Akte Kelahiran, KK, atau KTP) -> Klik "Unggah Dokumen" -> Sistem memvalidasi tipe berkas (PDF/JPG/PNG max 5MB) -> Menyimpan file ke direktori privat (`storage/app/private_documents`) agar tidak bisa diakses publik -> Alert sukses.
    *   **UC-14.3 Update Lisensi UCI**
        *   **Aktor**: Manajemen
        *   **Skenario**: Di profil atlet terkait, klik "Edit Lisensi UCI" -> Form tampil -> Mengisi nomor ID UCI dan mengunggah lisensi resmi -> Klik "Simpan" -> Sistem memperbarui tabel `athlete_profiles` -> Alert sukses.
    *   **UC-14.4 Unduh Dokumen Atlet**
        *   **Aktor**: Manajemen
        *   **Skenario**: Di halaman dokumen atlet, klik "Unduh Semua Dokumen" -> Sistem mengumpulkan seluruh berkas privat atlet -> Mengompresi berkas ke format `.ZIP` -> Streaming file download `[nama_atlet]_dokumen.zip`.

### **UC-15 Gunakan Kalkulator Gear Sepeda**
*   **Aktor Utama**: Atlet, Pelatih, Manajemen
*   **Kondisi**: Kalkulasi rasio gigi sepeda dan parameter mekanik secara real-time.
*   *   **UC-15.1 Gunakan Kalkulator Gear Sepeda**
        *   **Aktor**: Atlet, Pelatih, Manajemen
        *   **Skenario**: Buka menu "Tools" > "Gear Calculator" -> Memilih ukuran Gir Depan (Chainring), Gir Belakang (Rear Cog), Ukuran Ban, dan Target Kadens (RPM) -> Sistem menghitung Gear Ratio, Meters Development, dan Estimated Speed -> Hasil ditampilkan secara instan tanpa perlu klik simpan.

---

## 2. Penjelasan & Bagian yang Dipahami

Kami memahami dengan sangat baik struktur proyek ini yang diimplementasikan dengan:
1.  **Arsitektur Terpisah (Decoupled & Modular)**:
    *   **Backend**: Menggunakan Laravel 13, dengan pembagian tanggung jawab yang jelas. Route didefinisikan di `routes/web.php` dan dilindungi oleh middleware `auth` dan check `role` berbasis kustom middleware. Controller bertugas mengelola request/response (menggunakan data yang divalidasi lewat Request), lalu memanggil logic dari layer lain atau langsung menggunakan Repository seperti `TrainingLogRepository.php` untuk manipulasi data query.
    *   **Frontend**: Berbasis Vue 3 SPA + Vuetify + InertiaJS v2 (sehingga tidak ada file `.blade` untuk view inti, melainkan menggunakan `Inertia::render('Path/Ke/Component')`).
2.  **Sistem Role & Otorisasi**:
    *   Terdapat 3 peran (role) utama: `Atlet`, `Pelatih`, dan `Manajemen`.
    *   Tiap role memiliki prefix rute dan controller yang disesuaikan (misal: `/manajemen/*`, `/pelatih/*`, `/atlet/*`).
3.  **Metrik Fisik & BMI**:
    *   Kami memahami logika di model `User.php` yang memiliki event `booted()` dengan hook `static::saved`. Saat data `date_of_birth` diubah oleh user, sistem otomatis menghitung ulang kolom `age` pada semua baris data di tabel metrik fisik (`DataFisik`) yang terekam.
4.  **Keamanan Berkas UCI**:
    *   Lisensi UCI dan dokumen administratif (KTP, KK, Akte) disimpan dalam folder privat (`private_documents/[user_id]/`) menggunakan disk `local` agar tidak dapat diakses secara publik lewat tautan URL langsung. Hak akses unduhan diatur ketat via controller (`DocumentAccessController` dan `KelolaDokumenLisensiUciController`).

---

## 3. Bagian yang Perlu Dikonfirmasi (Belum Dipahami/Inkonsistensi)

Berdasarkan pemeriksaan rute `routes/web.php` dan dokumen `UsecaseBab3.pdf`, kami menemukan beberapa inkonsistensi kecil yang perlu dikonfirmasi sebelum pembuatan sequence diagram:

1.  **Role "Report" & Fitur Bug Report**:
    *   *Temuan di Code*: Di `routes/web.php` baris 174-177 terdapat grup middleware khusus untuk role `Report` yang bertugas memperbarui status Bug Report (`bug-reports/{laporanBug}/status`). Jalur penyimpanan bug report (`bug-reports`) juga didefinisikan secara publik di luar middleware login.
    *   *Temuan di PDF*: Dokumen Bab 3 **tidak menyebutkan** adanya role `Report` atau fitur pengelolaan Bug Report.
    *   *Pertanyaan*: Apakah sequence diagram untuk Bug Report dan role `Report` ini perlu dibuat atau cukup fokus pada 15 Use Case utama di PDF?
2.  **Role Manajemen vs Pelatih pada Kelola Event (UC-13)**:
    *   *Temuan di Code*: Route `/manajemen/acara` dan `/pelatih/acara` sama-sama mengarah ke `KelolaEventDanPartisipasiController`. Keduanya dapat membuat, memperbarui, dan menghapus event.
    *   *Temuan di PDF*: Pada diagram UC-13, yang digambarkan dapat membuat event dan mengelola partisipasi adalah **Pelatih**, sedangkan Manajemen tidak terhubung ke use case tersebut (meski di text deskripsi UAT Skenario 13 menyebutkan Manajemen yang melakukannya).
    *   *Pertanyaan*: Di sequence diagram nanti, apakah pembuatan event akan dibuat untuk kedua aktor tersebut, atau salah satunya saja?
3.  **Tabel `physical_metrics` vs Model `DataFisik`**:
    *   *Temuan di Code*: Tabel di database dinamakan `physical_metrics`, namun model Eloquent-nya dinamakan `DataFisik`. Hubungan relasi di model `User` menggunakan nama `physicalMetrics` namun mengacu ke class `DataFisik`.
    *   *Pemahaman*: Kami memahami pemetaan ini secara teknis, namun perlu diperhatikan saat penulisan Sequence Diagram agar nama entitas di diagram sinkron dengan nama kelas database.
