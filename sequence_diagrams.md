# Sequence Diagram SIMORA (Mermaid Format)

Dokumen ini berisi seluruh *Sequence Diagram* untuk Sistem Informasi Monitoring Atlet Sepeda (SIMORA) berdasarkan 15 Use Case Utama beserta turunan dan skenarionya. Diagram ditulis menggunakan sintaks **Mermaid** agar dapat langsung dirender di editor Markdown atau di [Mermaid Live Editor](https://mermaid.live).

---

## UC-01 Autentikasi Pengguna

### UC-01.1 Login
```mermaid
sequenceDiagram
    autonumber
    actor Aktor as Atlet / Pelatih / Manajemen
    participant Browser as Browser (Vue Page)
    participant Route as Laravel Route & Middleware
    participant Controller as Fortify\AuthenticatedSessionController
    participant User as User (Model)
    participant DB as MySQL Database

    Aktor->>Browser: Membuka Halaman Login & Input Kredensial
    Browser->>Route: POST /login
    activate Route
    Route->>Route: Validasi CSRF Token
    Route->>Controller: store(Request)
    activate Controller
    Controller->>User: attemptLogin()
    activate User
    User->>DB: SELECT * FROM users WHERE email = ?
    activate DB
    DB-->>User: Data Kredensial & Password Hash
    deactivate DB
    User->>User: Hash Check (Password Valid)
    
    alt Aktor adalah Atlet
        User->>DB: SELECT is_verified FROM users WHERE id = ?
        activate DB
        DB-->>User: is_verified (true/false)
        deactivate DB
        alt is_verified == false
            User-->>Controller: Authentication Exception (Unverified Athlete)
            Controller-->>Browser: Redirect to /menunggu-verifikasi
            Browser-->>Aktor: Tampilkan halaman "Menunggu Verifikasi"
        end
    end

    User-->>Controller: Login Success (Session Generated)
    deactivate User
    Controller-->>Browser: Redirect to /dashboard
    deactivate Controller
    deactivate Route
    Browser->>Route: GET /dashboard (with Session Cookie)
    activate Route
    Route->>Route: Middleware: auth, verified, verified-user
    Route->>Browser: Render Dashboard Page (Inertia View)
    deactivate Route
    Browser-->>Aktor: Tampilkan Dashboard sesuai Role
```

### UC-01.2 Register
```mermaid
sequenceDiagram
    autonumber
    actor Atlet as Calon Atlet
    participant Browser as Halaman Registrasi (Vue)
    participant Route as Laravel Route
    participant Controller as Fortify\RegisteredUserController
    participant User as User (Model)
    participant DB as MySQL Database

    Atlet->>Browser: Input Nama, Email, Password, & Konfirmasi
    Browser->>Route: POST /register
    activate Route
    Route->>Controller: store(Request)
    activate Controller
    Controller->>Controller: Validasi Form Request (Nama, Email unik, dll)
    Controller->>User: create([data, role_id => Atlet, is_verified => false])
    activate User
    User->>DB: INSERT INTO users (role_id, is_verified, ...) VALUES (...)
    activate DB
    DB-->>User: User Created
    deactivate DB
    User-->>Controller: User Instance
    deactivate User
    Controller-->>Browser: Redirect ke halaman /menunggu-verifikasi dengan pesan sukses
    deactivate Controller
    deactivate Route
    Browser-->>Atlet: Tampilkan Halaman "Menunggu Verifikasi dari Manajemen"
```

### UC-01.3 Lupa Password
```mermaid
sequenceDiagram
    autonumber
    actor Aktor as Atlet / Pelatih / Manajemen
    participant Browser as Halaman Lupa Password (Vue)
    participant Route as Laravel Route
    participant Controller as Fortify\PasswordResetLinkController / ResetPasswordController
    participant User as User (Model)
    participant Notification as MailNotification
    participant DB as MySQL Database

    Aktor->>Browser: Klik "Lupa Password?" & Input Email
    Browser->>Route: POST /forgot-password
    activate Route
    Route->>Controller: store(Request)
    activate Controller
    Controller->>User: findByEmail()
    activate User
    User->>DB: SELECT id FROM users WHERE email = ?
    activate DB
    DB-->>User: User Found
    deactivate DB
    User-->>Controller: User Instance
    deactivate User
    Controller->>Controller: Generate Reset Token
    Controller->>DB: INSERT INTO password_reset_tokens (email, token) VALUES (...)
    activate DB
    DB-->>Controller: Token Saved
    deactivate DB
    Controller->>Notification: Send Reset Password Email (CustomResetPassword)
    activate Notification
    Notification-->>Aktor: Terima email berisi link reset
    deactivate Notification
    Controller-->>Browser: JSON Response (Tautan reset berhasil dikirim)
    deactivate Controller
    deactivate Route

    Aktor->>Browser: Buka tautan reset & Input Password Baru
    Browser->>Route: POST /reset-password
    activate Route
    Route->>Controller: update(Request)
    activate Controller
    Controller->>DB: SELECT * FROM password_reset_tokens WHERE email = ? AND token = ?
    activate DB
    DB-->>Controller: Token Valid
    deactivate DB
    Controller->>User: updatePassword()
    activate User
    User->>DB: UPDATE users SET password = ? WHERE email = ?
    activate DB
    DB-->>User: Password Updated
    deactivate DB
    User-->>Controller: Success
    deactivate User
    Controller->>DB: DELETE FROM password_reset_tokens WHERE email = ?
    activate DB
    deactivate DB
    Controller-->>Browser: Redirect ke /login dengan alert sukses
    deactivate Controller
    deactivate Route
    Browser-->>Aktor: Tampilkan form login dengan pesan sukses
```

---

## UC-02 Lihat Dashboard

### UC-02.1 Lihat Dashboard Atlet
```mermaid
sequenceDiagram
    autonumber
    actor Atlet as Atlet
    participant Browser as Dashboard Atlet (Vue)
    participant Route as Laravel Route
    participant Controller as LihatDashboardController
    participant Repo as TrainingLogRepository
    participant DB as MySQL Database

    Atlet->>Browser: Klik menu Dashboard / Login berhasil
    Browser->>Route: GET /atlet/dashboard
    activate Route
    Route->>Controller: tampilDashboard()
    activate Controller
    Controller->>Repo: getStatistics(atlet_id, 7_days_ago, now)
    activate Repo
    Repo->>DB: SELECT SUM(distance_km), SUM(duration_minutes) FROM training_logs...
    activate DB
    DB-->>Repo: Data Statistik
    deactivate DB
    Repo-->>Controller: Array statistik
    deactivate Repo

    Controller->>Repo: getPerformanceTrend(atlet_id, 7_logs)
    activate Repo
    Repo->>DB: SELECT date, avg_speed, rpm, intensity FROM training_logs...
    activate DB
    DB-->>Repo: Data Tren
    deactivate DB
    Repo-->>Controller: Array tren
    deactivate Repo

    Controller->>Repo: getUpcomingSessions(atlet_id)
    activate Repo
    Repo->>DB: SELECT * FROM training_sessions... WHERE date >= today
    activate DB
    DB-->>Repo: Data Sesi Latihan
    deactivate DB
    Repo-->>Controller: Collection Sesi Latihan
    deactivate Repo

    Controller-->>Browser: Render Page with Inertia Props
    deactivate Controller
    deactivate Route
    Browser->>Browser: Inisialisasi ApexCharts dengan data tren
    Browser-->>Atlet: Tampilkan visualisasi statistik & jadwal latihan
```

### UC-02.2 Update Cepat (atlet)
```mermaid
sequenceDiagram
    autonumber
    actor Atlet as Atlet
    participant Browser as Dashboard Atlet (Vue)
    participant Route as Laravel Route
    participant Controller as LihatDashboardController
    participant ModelFisik as DataFisik (Model)
    participant ModelLog as LogLatihan (Model)
    participant DB as MySQL Database

    Atlet->>Browser: Isi widget "Update Cepat" & Klik Simpan
    Browser->>Route: POST /atlet/dashboard/quick-update
    activate Route
    Route->>Controller: perbaruiDataCepat(Request)
    activate Controller
    Controller->>Controller: Validasi data fisik / latihan
    
    opt Input Data Fisik
        Controller->>ModelFisik: updateOrCreate([berat, tinggi])
        activate ModelFisik
        ModelFisik->>DB: INSERT/UPDATE physical_metrics
        activate DB
        DB-->>ModelFisik: Success
        deactivate DB
        ModelFisik-->>Controller: DataFisik Instance
        deactivate ModelFisik
    end

    opt Input Log Latihan
        Controller->>ModelLog: create([distance_km, duration_minutes, ...])
        activate ModelLog
        ModelLog->>DB: INSERT INTO training_logs
        activate DB
        DB-->>ModelLog: Success
        deactivate DB
        ModelLog-->>Controller: LogLatihan Instance
        deactivate ModelLog
    end

    Controller-->>Browser: JSON Success Response & Trigger Reload Props
    deactivate Controller
    deactivate Route
    Browser->>Browser: Refresh data dashboard
    Browser-->>Atlet: Tampilkan alert sukses & perbarui widget dashboard
```

### UC-02.3 Lihat Dashboard Pelatih
```mermaid
sequenceDiagram
    autonumber
    actor Pelatih as Pelatih
    participant Browser as Dashboard Pelatih (Vue)
    participant Route as Laravel Route
    participant Controller as LihatDashboardController
    participant Repo as TrainingLogRepository
    participant User as User (Model)
    participant DB as MySQL Database

    Pelatih->>Browser: Klik menu Dashboard
    Browser->>Route: GET /pelatih/dashboard
    activate Route
    Route->>Controller: tampilDashboard()
    activate Controller
    Controller->>User: whereRole('Atlet')->where('coach_id', pelatih_id)->count()
    activate User
    User->>DB: SELECT COUNT(*) FROM users WHERE coach_id = ?
    activate DB
    DB-->>User: Total Atlet Binaan
    deactivate DB
    User-->>Controller: Count
    deactivate User

    Controller->>Repo: getAthleteRanking(atlet_binaan_ids, 30_days)
    activate Repo
    Repo->>DB: SELECT avg_speed, user_id FROM training_logs... GROUP BY user_id ORDER BY avg_speed DESC
    activate DB
    DB-->>Repo: Data Ranking
    deactivate DB
    Repo-->>Controller: Array Ranking
    deactivate Repo

    Controller-->>Browser: Render Page with Inertia Props (Ranking, Total Atlet, Sesi)
    deactivate Controller
    deactivate Route
    Browser-->>Pelatih: Tampilkan Dashboard Pelatih & Grafik Ranking Regu
```

### UC-02.4 Lihat Dashboard Manajemen
```mermaid
sequenceDiagram
    autonumber
    actor Manajemen as Manajemen
    participant Browser as Dashboard Manajemen (Vue)
    participant Route as Laravel Route
    participant Controller as LihatDashboardController
    participant User as User (Model)
    participant Log as LogLatihan (Model)
    participant DB as MySQL Database

    Manajemen->>Browser: Klik menu Dashboard
    Browser->>Route: GET /manajemen/dashboard
    activate Route
    Route->>Controller: tampilDashboard()
    activate Controller
    Controller->>User: Hitung Total Atlet, Pelatih, & Pending Users
    activate User
    User->>DB: SELECT COUNT(*) FROM users GROUP BY role_id
    activate DB
    DB-->>User: Data Statistik User
    deactivate DB
    User-->>Controller: Total users
    deactivate User

    Controller->>Log: Ambil statistik global jarak latihan 7 hari terakhir
    activate Log
    Log->>DB: SELECT SUM(distance_km) FROM training_logs WHERE date >= 7_days_ago
    activate DB
    DB-->>Log: Data Jarak Global
    deactivate DB
    Log-->>Controller: total_distance
    deactivate Log

    Controller-->>Browser: Render Page dengan Inertia Props
    deactivate Controller
    deactivate Route
    Browser-->>Manajemen: Tampilkan statistik sistem & grafik performa global
```

---

## UC-03 Kelola Profil Pengguna

### UC-03.1 Lihat Profil Pengguna
```mermaid
sequenceDiagram
    autonumber
    actor Aktor as Atlet / Pelatih / Manajemen
    participant Browser as Pengaturan Profil (Vue)
    participant Route as Laravel Route
    participant Controller as UserProfileController
    participant User as User (Model)
    participant DB as MySQL Database

    Aktor->>Browser: Klik menu "Pengaturan Profil"
    Browser->>Route: GET /profile
    activate Route
    Route->>Controller: show()
    activate Controller
    Controller->>User: getProfileWithRelations(auth_id)
    activate User
    User->>DB: SELECT * FROM users LEFT JOIN athlete_profiles ON ... WHERE id = ?
    activate DB
    DB-->>User: Data Profil
    deactivate DB
    User-->>Controller: User Instance
    deactivate User
    Controller-->>Browser: Render Page dengan Inertia Props
    deactivate Controller
    deactivate Route
    Browser-->>Aktor: Tampilkan detail profil (Nama, Email, Avatar, dll)
```

### UC-03.2 Ubah Profil Pengguna
```mermaid
sequenceDiagram
    autonumber
    actor Aktor as Atlet / Pelatih / Manajemen
    participant Browser as Pengaturan Profil (Vue)
    participant Route as Laravel Route
    participant Controller as UserProfileController
    participant User as User (Model)
    participant Mail as MailService
    participant DB as MySQL Database

    Aktor->>Browser: Ubah data diri, unggah avatar baru & klik Simpan
    Browser->>Route: POST /profile/update
    activate Route
    Route->>Controller: update(Request)
    activate Controller
    Controller->>Controller: Validasi data input (Nama, dll)
    
    opt Aktor mengunggah Avatar Baru
        Controller->>User: updateProfilePhoto(file)
        activate User
        User->>User: Store file to storage disk
        User->>DB: UPDATE users SET avatar = ? WHERE id = ?
        activate DB
        DB-->>User: Success
        deactivate DB
        User-->>Controller: Avatar Updated
        deactivate User
    end

    opt Aktor mengubah alamat Email (Memicu verifikasi OTP)
        Controller->>Mail: sendOtpCode(email_baru)
        activate Mail
        Mail-->>Aktor: Kirim email kode OTP
        deactivate Mail
        Controller-->>Browser: Kirim status "OTP_REQUIRED"
        Aktor->>Browser: Masukkan Kode OTP & Klik Verifikasi
        Browser->>Route: POST /profile/verify-email-otp
        Route->>Controller: verifyOtp(Request)
        Controller->>Controller: Cocokkan OTP di DB/Session
        Controller->>User: update([email])
        activate User
        User->>DB: UPDATE users SET email = ? WHERE id = ?
        activate DB
        DB-->>User: Success
        deactivate DB
        User-->>Controller: Email Updated
        deactivate User
    end

    Controller->>User: update([name, date_of_birth, gender])
    activate User
    User->>DB: UPDATE users SET name = ?, ... WHERE id = ?
    activate DB
    DB-->>User: Profile Updated
    deactivate DB
    User-->>Controller: Success
    deactivate User

    Controller-->>Browser: JSON Success Response
    deactivate Controller
    deactivate Route
    Browser-->>Aktor: Tampilkan notifikasi "Tersimpan" & perbarui foto/nama
```

### UC-03.3 Hapus Akun Pengguna
```mermaid
sequenceDiagram
    autonumber
    actor Aktor as Atlet / Pelatih / Manajemen
    participant Browser as Pengaturan Profil (Vue)
    participant Route as Laravel Route
    participant Controller as UserProfileController
    participant User as User (Model)
    participant DB as MySQL Database

    Aktor->>Browser: Klik "Delete Account" -> Input Password -> Klik Konfirmasi
    Browser->>Route: DELETE /profile/destroy
    activate Route
    Route->>Controller: destroy(Request)
    activate Controller
    Controller->>Controller: Validasi Password saat ini
    Controller->>User: delete()
    activate User
    User->>DB: DELETE FROM users WHERE id = ?
    activate DB
    DB-->>User: Success
    deactivate DB
    User-->>Controller: Success
    deactivate User
    Controller->>Controller: Invalidate Session (Auth::logout)
    Controller-->>Browser: Redirect ke halaman Utama / Landing Page
    deactivate Controller
    deactivate Route
    Browser-->>Aktor: Tampilkan Landing Page (Sesi terhapus)
```

---

## UC-04 Memverifikasi Pendaftaran & Menetapkan Pelatih

### UC-04.1 Lihat daftar Atlet Belum Terverifikasi
```mermaid
sequenceDiagram
    autonumber
    actor Manajemen as Manajemen
    participant Browser as Daftar Pendaftaran Tertunda (Vue)
    participant Route as Laravel Route
    participant Controller as MemverifikasiPendaftaranDanMenetapkanPelatihController
    participant User as User (Model)
    participant DB as MySQL Database

    Manajemen->>Browser: Klik menu "Pendaftaran Tertunda"
    Browser->>Route: GET /manajemen/pending
    activate Route
    Route->>Controller: tampilDaftarTertunda()
    activate Controller
    Controller->>User: whereRole('Atlet')->where('is_verified', false)->get()
    activate User
    User->>DB: SELECT * FROM users WHERE role_id = (role Atlet) AND is_verified = false
    activate DB
    DB-->>User: Daftar Atlet Pending
    deactivate DB
    User-->>Controller: Collection Atlet Pending
    deactivate User

    Controller->>User: whereRole('Pelatih')->get()
    activate User
    User->>DB: SELECT id, name FROM users WHERE role_id = (role Pelatih)
    activate DB
    DB-->>User: Daftar Pelatih
    deactivate DB
    User-->>Controller: Collection Pelatih
    deactivate User

    Controller-->>Browser: Render Page dengan Inertia Props (atlet pending & daftar pelatih)
    deactivate Controller
    deactivate Route
    Browser-->>Manajemen: Tampilkan daftar atlet dalam bentuk kartu
```

### UC-04.2 Ubah Status Terverifikasi dan Menentukan Pelatih
```mermaid
sequenceDiagram
    autonumber
    actor Manajemen as Manajemen
    participant Browser as Daftar Pendaftaran Tertunda (Vue)
    participant Route as Laravel Route
    participant Controller as MemverifikasiPendaftaranDanMenetapkanPelatihController
    participant User as User (Model)
    participant Notification as MailNotification
    participant DB as MySQL Database

    Manajemen->>Browser: Klik "Verifikasi", Pilih Pelatih, Klik "Setujui Pendaftaran"
    Browser->>Route: POST /manajemen/users/{atlet_id}/verify
    activate Route
    Route->>Controller: verifikasiPendaftaran(atlet_id, Request)
    activate Controller
    Controller->>Controller: Validasi data input (coach_id)
    Controller->>User: find(atlet_id)
    activate User
    User->>DB: SELECT * FROM users WHERE id = ?
    activate DB
    DB-->>User: Atlet Data
    deactivate DB
    User-->>Controller: User Instance
    deactivate User

    Controller->>User: update([is_verified => true, coach_id => coach_id])
    activate User
    User->>DB: UPDATE users SET is_verified = true, coach_id = ? WHERE id = ?
    activate DB
    DB-->>User: Success
    deactivate DB
    User-->>Controller: Success
    deactivate User

    Controller->>Notification: sendAccountActivatedNotification()
    activate Notification
    Notification-->>Browser: (Async) Kirim email notifikasi ke atlet
    deactivate Notification

    Controller-->>Browser: JSON Success Response & Hapus Atlet dari list
    deactivate Controller
    deactivate Route
    Browser-->>Manajemen: Tampilkan alert verifikasi berhasil
```

---

## UC-05 Lihat Ringkasan Daftar Atlet

### UC-05.1 Lihat Daftar Atlet
```mermaid
sequenceDiagram
    autonumber
    actor Aktor as Pelatih / Manajemen
    participant Browser as Daftar Atlet (Vue)
    participant Route as Laravel Route
    participant Controller as LihatRingkasanDaftarAtletController
    participant User as User (Model)
    participant DB as MySQL Database

    Aktor->>Browser: Klik menu "Daftar Atlet"
    Browser->>Route: GET /atlet (prefix pelatih/manajemen)
    activate Route
    Route->>Controller: tampilDaftar()
    activate Controller
    
    alt Aktor adalah Manajemen
        Controller->>User: whereRole('Atlet')->get()
        activate User
        User->>DB: SELECT * FROM users LEFT JOIN Kategori... (All Athletes)
        activate DB
        DB-->>User: All Athletes List
        deactivate DB
        User-->>Controller: Collection Atlet
        deactivate User
    else Aktor adalah Pelatih
        Controller->>User: whereRole('Atlet')->where('coach_id', auth_id)->get()
        activate User
        User->>DB: SELECT * FROM users LEFT JOIN Kategori... WHERE coach_id = ? (Auth Coach ID)
        activate DB
        DB-->>User: Coached Athletes List
        deactivate DB
        User-->>Controller: Collection Atlet
        deactivate User
    end

    Controller-->>Browser: Render Page dengan Inertia Props (athletes list)
    deactivate Controller
    deactivate Route
    Browser-->>Aktor: Tampilkan tabel daftar atlet
```

### UC-05.2 Lihat Detail Atlet
```mermaid
sequenceDiagram
    autonumber
    actor Aktor as Pelatih / Manajemen
    participant Browser as Detail Atlet (Vue)
    participant Route as Laravel Route
    participant Controller as LihatRingkasanDaftarAtletController
    participant User as User (Model)
    participant Repo as TrainingLogRepository
    participant DB as MySQL Database

    Aktor->>Browser: Klik tombol "Lihat Detail" pada baris atlet
    Browser->>Route: GET /atlet/{atlet_id}
    activate Route
    Route->>Controller: tampilDetail(atlet_id)
    activate Controller
    Controller->>User: find(atlet_id)
    activate User
    User->>DB: SELECT * FROM users WHERE id = ?
    activate DB
    DB-->>User: Atlet Data
    deactivate DB
    User-->>Controller: Atlet Instance
    deactivate User

    alt Aktor adalah Pelatih
        Controller->>Controller: Verifikasi relasi (atlet->coach_id === auth_id)
        note over Controller: Jika tidak cocok, abort(403)
    end

    Controller->>Repo: getStatistics(atlet_id)
    activate Repo
    Repo->>DB: SELECT SUM(distance_km)... WHERE user_id = ?
    activate DB
    DB-->>Repo: Statistik Latihan
    deactivate DB
    Repo-->>Controller: Array statistik
    deactivate Repo

    Controller->>User: physicalMetrics()
    activate User
    User->>DB: SELECT * FROM physical_metrics WHERE user_id = ? ORDER BY recorded_at DESC
    activate DB
    DB-->>User: Riwayat Fisik
    deactivate DB
    User-->>Controller: Collection DataFisik
    deactivate User

    Controller-->>Browser: Render Page dengan Inertia Props (profil, statistik, riwayat fisik, log)
    deactivate Controller
    deactivate Route
    Browser-->>Aktor: Tampilkan detail profil, statistik latihan, & riwayat fisik atlet
```

### UC-05.3 Perbarui Pelatih Pembina
```mermaid
sequenceDiagram
    autonumber
    actor Manajemen as Manajemen
    participant Browser as Detail Atlet (Vue)
    participant Route as Laravel Route
    participant Controller as LihatRingkasanDaftarAtletController
    participant User as User (Model)
    participant DB as MySQL Database

    Manajemen->>Browser: Pilih Pelatih baru dari dropdown & klik Simpan Pelatih
    Browser->>Route: PATCH /manajemen/atlet/{atlet_id}/coach
    activate Route
    Route->>Controller: perbaruiPelatih(atlet_id, Request)
    activate Controller
    Controller->>Controller: Validasi data input (coach_id)
    Controller->>User: find(atlet_id)
    activate User
    User->>DB: SELECT * FROM users WHERE id = ?
    activate DB
    DB-->>User: Atlet Data
    deactivate DB
    User-->>Controller: Atlet Instance
    deactivate User

    Controller->>User: update([coach_id => coach_id])
    activate User
    User->>DB: UPDATE users SET coach_id = ? WHERE id = ?
    activate DB
    DB-->>User: Success
    deactivate DB
    User-->>Controller: Success
    deactivate User

    Controller-->>Browser: JSON Success Response
    deactivate Controller
    deactivate Route
    Browser-->>Manajemen: Tampilkan alert sukses penggantian pelatih
```

### UC-05.4 Perbarui Kategori Atlet
```mermaid
sequenceDiagram
    autonumber
    actor Pelatih as Pelatih
    participant Browser as Detail Atlet (Vue)
    participant Route as Laravel Route
    participant Controller as LihatRingkasanDaftarAtletController
    participant User as User (Model)
    participant DB as MySQL Database

    Pelatih->>Browser: Pilih Kategori baru dari dropdown & klik Simpan Kategori
    Browser->>Route: PATCH /pelatih/atlet/{atlet_id}/category
    activate Route
    Route->>Controller: perbaruiKategori(atlet_id, Request)
    activate Controller
    Controller->>Controller: Validasi data input (category_id)
    Controller->>User: find(atlet_id)
    activate User
    User->>DB: SELECT * FROM users WHERE id = ?
    activate DB
    DB-->>User: Atlet Data
    deactivate DB
    User-->>Controller: Atlet Instance
    deactivate User

    Controller->>Controller: Verifikasi otorisasi pelatih binaan
    Controller->>User: update([category_id => category_id])
    activate User
    User->>DB: UPDATE users SET category_id = ? WHERE id = ?
    activate DB
    DB-->>User: Success
    deactivate DB
    User-->>Controller: Success
    deactivate User

    Controller-->>Browser: JSON Success Response
    deactivate Controller
    deactivate Route
    Browser-->>Pelatih: Tampilkan alert sukses pembaruan kategori atlet
```

---

## UC-06 Kelola Data Metrik Fisik (BMI)

### UC-06.1 Update BMI Atlet
```mermaid
sequenceDiagram
    autonumber
    actor Atlet as Atlet
    participant Browser as Halaman Metrik Fisik (Vue)
    participant Route as Laravel Route
    participant Controller as KelolaDataMetrikFisikController
    participant ModelFisik as DataFisik (Model)
    participant User as User (Model)
    participant DB as MySQL Database

    Atlet->>Browser: Klik "Update Fisik Baru" -> Input Tinggi, Berat, & Tanggal -> Simpan
    Browser->>Route: POST /atlet/fisik
    activate Route
    Route->>Controller: simpanData(Request)
    activate Controller
    Controller->>Controller: Validasi Form Request (Tinggi, Berat, Tanggal)
    Controller->>User: find(auth_id) (Mengambil tanggal lahir untuk menghitung usia)
    activate User
    User->>DB: SELECT date_of_birth, gender FROM users WHERE id = ?
    activate DB
    DB-->>User: User Data
    deactivate DB
    User-->>Controller: User Instance
    deactivate User

    Controller->>Controller: Hitung Usia (Tanggal Perekaman - Tanggal Lahir)
    Controller->>Controller: Hitung BMI (Berat (kg) / (Tinggi (m))^2)
    Controller->>ModelFisik: create([user_id, tinggi, berat, bmi, usia, recorded_at])
    activate ModelFisik
    ModelFisik->>DB: INSERT INTO physical_metrics (user_id, height_cm, weight_kg, bmi, age, recorded_at) VALUES (...)
    activate DB
    DB-->>ModelFisik: Success
    deactivate DB
    ModelFisik-->>Controller: DataFisik Instance
    deactivate ModelFisik

    Controller-->>Browser: JSON Success Response
    deactivate Controller
    deactivate Route
    Browser->>Browser: Update grafik & tabel riwayat fisik
    Browser-->>Atlet: Tampilkan alert sukses & BMI terhitung
```

### UC-06.2 Lihat Statistik BMI Atlet
```mermaid
sequenceDiagram
    autonumber
    actor Aktor as Atlet / Pelatih / Manajemen
    participant Browser as Halaman Metrik Fisik / Detail Atlet (Vue)
    participant Route as Laravel Route
    participant Controller as KelolaDataMetrikFisikController
    participant ModelFisik as DataFisik (Model)
    participant DB as MySQL Database

    Aktor->>Browser: Membuka Halaman Metrik Fisik
    Browser->>Route: GET /atlet/fisik (atau detail atlet)
    activate Route
    Route->>Controller: tampilData()
    activate Controller
    Controller->>ModelFisik: where('user_id', atlet_id)->orderBy('recorded_at')->get()
    activate ModelFisik
    ModelFisik->>DB: SELECT height_cm, weight_kg, bmi, age, recorded_at FROM physical_metrics WHERE user_id = ? ORDER BY recorded_at
    activate DB
    DB-->>ModelFisik: Collection DataFisik
    deactivate DB
    ModelFisik-->>Controller: Collection DataFisik
    deactivate ModelFisik

    Controller-->>Browser: Render Page dengan Inertia Props (data fisik)
    deactivate Controller
    deactivate Route
    Browser->>Browser: Olah data ke grafik ApexCharts & hitung status kategori BMI
    Browser-->>Aktor: Tampilkan grafik tren Berat vs Tinggi & Kartu status BMI
```

### UC-06.3 Update Tanggal Lahir Atlet
```mermaid
sequenceDiagram
    autonumber
    actor Atlet as Atlet
    participant Browser as Pengaturan Profil (Vue)
    participant Route as Laravel Route
    participant Controller as UserProfileController
    participant User as User (Model)
    participant ModelFisik as DataFisik (Model)
    participant DB as MySQL Database

    Atlet->>Browser: Memilih tanggal lahir di datepicker & klik Simpan
    Browser->>Route: POST /profile/update
    activate Route
    Route->>Controller: update(Request)
    activate Controller
    Controller->>User: find(auth_id)
    activate User
    User->>DB: SELECT * FROM users WHERE id = ?
    activate DB
    DB-->>User: User Data
    deactivate DB
    User-->>Controller: User Instance
    deactivate User

    Controller->>User: update([date_of_birth])
    activate User
    User->>DB: UPDATE users SET date_of_birth = ? WHERE id = ?
    activate DB
    DB-->>User: Success
    deactivate DB
    
    note over User: Trigger Event: booted() -> static::saved
    activate User
    User->>ModelFisik: where('user_id', id)->get()
    activate ModelFisik
    ModelFisik->>DB: SELECT * FROM physical_metrics WHERE user_id = ?
    activate DB
    DB-->>ModelFisik: Collection DataFisik
    deactivate DB
    loop Tiap Rekaman Metrik Fisik
        User->>User: Hitung ulang usia pada tanggal perekaman
        User->>ModelFisik: update(['age' => usia_baru])
        ModelFisik->>DB: UPDATE physical_metrics SET age = ? WHERE id = ?
        activate DB
        DB-->>ModelFisik: Success
        deactivate DB
    end
    deactivate ModelFisik
    deactivate User

    User-->>Controller: Success
    deactivate User
    Controller-->>Browser: JSON Success Response
    deactivate Controller
    deactivate Route
    Browser-->>Atlet: Tampilkan alert sukses & perbarui data usia real-time
```

### UC-06.4 Lihat Tanggal Lahir Atlet
```mermaid
sequenceDiagram
    autonumber
    actor Aktor as Atlet / Pelatih
    participant Browser as Halaman Profil Fisik (Vue)
    participant Route as Laravel Route
    participant Controller as KelolaDataMetrikFisikController
    participant User as User (Model)
    participant DB as MySQL Database

    Aktor->>Browser: Membuka Halaman Profil Fisik / Detail Atlet
    Browser->>Route: GET /atlet/fisik
    activate Route
    Route->>Controller: tampilData()
    activate Controller
    Controller->>User: find(atlet_id)
    activate User
    User->>DB: SELECT name, date_of_birth FROM users WHERE id = ?
    activate DB
    DB-->>User: User Data (Tgl Lahir)
    deactivate DB
    User-->>Controller: User Instance
    deactivate User
    Controller-->>Browser: Render Page dengan Inertia Props (user dengan age / date_of_birth)
    deactivate Controller
    deactivate Route
    Browser->>Browser: Hitung usia real-time saat ini
    Browser-->>Aktor: Tampilkan Tanggal Lahir & Usia atlet saat ini
```

---

## UC-07 Kelola Jadwal Sesi Latihan

### UC-07.1 Buat Sesi Latihan
```mermaid
sequenceDiagram
    autonumber
    actor Pelatih as Pelatih
    participant Browser as Halaman Sesi Latihan (Vue)
    participant Route as Laravel Route
    participant Controller as KelolaJadwalSesiLatihanController
    participant ModelSession as SesiLatihan (Model)
    participant DB as MySQL Database

    Pelatih->>Browser: Klik "Tambah Sesi Latihan" -> Input Detail & Target -> Pilih Atlet -> Simpan
    Browser->>Route: POST /pelatih/sesi-latihan
    activate Route
    Route->>Controller: simpan(Request)
    activate Controller
    Controller->>Controller: Validasi Form Request (Judul, Tanggal, Target, dll)
    Controller->>ModelSession: create([judul, tanggal, exercise_type_id, ...])
    activate ModelSession
    ModelSession->>DB: INSERT INTO training_sessions (title, date, target_distance, ...) VALUES (...)
    activate DB
    DB-->>ModelSession: Session Created (ID Sesi)
    deactivate DB
    ModelSession-->>Controller: SesiLatihan Instance
    deactivate ModelSession

    Controller->>ModelSession: athletes()->sync(daftar_atlet_ids)
    activate ModelSession
    ModelSession->>DB: INSERT INTO training_session_user (training_session_id, user_id) VALUES (...)
    activate DB
    DB-->>ModelSession: Synchronized
    deactivate DB
    ModelSession-->>Controller: Sync Success
    deactivate ModelSession

    Controller-->>Browser: JSON Success Response
    deactivate Controller
    deactivate Route
    Browser->>Browser: Refresh kalender / daftar sesi latihan
    Browser-->>Pelatih: Tampilkan alert sukses & perbarui jadwal
```

### UC-07.2 Lihat Sesi Latihan
```mermaid
sequenceDiagram
    autonumber
    actor Aktor as Pelatih / Atlet
    participant Browser as Jadwal Sesi Latihan (Vue)
    participant Route as Laravel Route
    participant Controller as KelolaJadwalSesiLatihanController
    participant Repo as TrainingLogRepository
    participant DB as MySQL Database

    Aktor->>Browser: Klik menu "Jadwal Latihan" / "Latihan Saya"
    Browser->>Route: GET /pelatih/sesi-latihan (atau /atlet/latihan)
    activate Route
    Route->>Controller: tampilDaftar()
    activate Controller

    alt Aktor adalah Pelatih
        Controller->>Repo: getCoachedSessions(pelatih_id)
        activate Repo
        Repo->>DB: SELECT * FROM training_sessions WHERE coach_id = ?
        activate DB
        DB-->>Repo: Sesi Latihan Pelatih
        deactivate DB
        Repo-->>Controller: Collection Sesi Latihan
        deactivate Repo
    else Aktor adalah Atlet
        Controller->>Repo: getUpcomingSessions(atlet_id)
        activate Repo
        Repo->>DB: SELECT * FROM training_sessions INNER JOIN training_session_user WHERE user_id = ?
        activate DB
        DB-->>Repo: Sesi Latihan Atlet
        deactivate DB
        Repo-->>Controller: Collection Sesi Latihan
        deactivate Repo
    end

    Controller-->>Browser: Render Page dengan Inertia Props (daftar sesi)
    deactivate Controller
    deactivate Route
    Browser-->>Aktor: Tampilkan kalender atau tabel sesi latihan
```

### UC-07.3 Hapus Sesi Latihan
```mermaid
sequenceDiagram
    autonumber
    actor Pelatih as Pelatih
    participant Browser as Jadwal Sesi Latihan (Vue)
    participant Route as Laravel Route
    participant Controller as KelolaJadwalSesiLatihanController
    participant ModelSession as SesiLatihan (Model)
    participant DB as MySQL Database

    Pelatih->>Browser: Klik "Hapus" pada sesi latihan -> Klik Konfirmasi Hapus
    Browser->>Route: DELETE /pelatih/sesi-latihan/{id}
    activate Route
    Route->>Controller: hapus(id)
    activate Controller
    Controller->>ModelSession: find(id)
    activate ModelSession
    ModelSession->>DB: SELECT * FROM training_sessions WHERE id = ?
    activate DB
    DB-->>ModelSession: Session Data
    deactivate DB
    ModelSession-->>Controller: SesiLatihan Instance
    deactivate ModelSession

    Controller->>Controller: Validasi Hak Kepemilikan (session->coach_id === auth_id)
    Controller->>ModelSession: delete()
    activate ModelSession
    ModelSession->>DB: DELETE FROM training_sessions WHERE id = ? (Cascade/Delete User Pivot)
    activate DB
    DB-->>ModelSession: Success
    deactivate DB
    ModelSession-->>Controller: Success
    deactivate ModelSession

    Controller-->>Browser: JSON Success Response
    deactivate Controller
    deactivate Route
    Browser->>Browser: Hapus item dari tampilan/kalender
    Browser-->>Pelatih: Tampilkan alert sukses "Sesi latihan dihapus"
```

---

## UC-08 Analisa Grafik & Statistik Latihan

### UC-08.1 Lihat Grafik & Statistik Atlet
```mermaid
sequenceDiagram
    autonumber
    actor Aktor as Atlet / Pelatih / Manajemen
    participant Browser as Analisa Latihan (Vue)
    participant Route as Laravel Route
    participant Controller as AnalisaGrafikDanStatistikLatihanController
    participant Repo as TrainingLogRepository
    participant DB as MySQL Database

    Aktor->>Browser: Buka menu "Analisa Latihan" / "Detail Latihan"
    Browser->>Route: GET /atlet/latihan (atau detail atlet bimbingan)
    activate Route
    Route->>Controller: tampilData()
    activate Controller
    Controller->>Repo: getPerformanceTrend(atlet_id, start_date, end_date)
    activate Repo
    Repo->>DB: SELECT date, distance_km, duration_minutes, avg_speed, rpm, intensity FROM training_logs WHERE athlete_id = ?
    activate DB
    DB-->>Repo: Log Latihan
    deactivate DB
    Repo-->>Controller: Array log tren
    deactivate Repo

    Controller-->>Browser: Render Page dengan Inertia Props (log tren)
    deactivate Controller
    deactivate Route
    Browser->>Browser: Format data untuk sumbu X (Tanggal) & sumbu Y (Metrik)
    Browser->>Browser: Inisialisasi ApexCharts (Speed Line, HR Line, RPM Line)
    Browser-->>Aktor: Tampilkan grafik performa interaktif beserta ringkasan statistik
```

---

## UC-09 Bandingkan Performa & Memfilter Riwayat

### UC-09.1 Lihat Daftar Performa Atlet
```mermaid
sequenceDiagram
    autonumber
    actor Aktor as Pelatih / Manajemen
    participant Browser as Komparasi Performa (Vue)
    participant Route as Laravel Route
    participant Controller as BandingkanPerformaDanMemfilterRiwayatController
    participant User as User (Model)
    participant DB as MySQL Database

    Aktor->>Browser: Klik menu "Komparasi Performa"
    Browser->>Route: GET /komparasi-performa
    activate Route
    Route->>Controller: tampilHalaman()
    activate Controller

    alt Aktor adalah Manajemen
        Controller->>User: whereRole('Atlet')->get()
        activate User
        User->>DB: SELECT id, name, avatar FROM users WHERE role_id = (Atlet)
        activate DB
        DB-->>User: Semua Atlet
        deactivate DB
        User-->>Controller: Collection Atlet
        deactivate User
    else Aktor adalah Pelatih
        Controller->>User: whereRole('Atlet')->where('coach_id', pelatih_id)->get()
        activate User
        User->>DB: SELECT id, name, avatar FROM users WHERE role_id = (Atlet) AND coach_id = ?
        activate DB
        DB-->>User: Atlet Binaan
        deactivate DB
        User-->>Controller: Collection Atlet
        deactivate User
    end

    Controller-->>Browser: Render Page dengan Inertia Props (daftar atlet)
    deactivate Controller
    deactivate Route
    Browser-->>Aktor: Tampilkan pilihan atlet dalam bentuk chips
```

### UC-09.2 Bandingkan Performa Atlet
```mermaid
sequenceDiagram
    autonumber
    actor Aktor as Pelatih / Manajemen
    participant Browser as Komparasi Performa (Vue)
    participant Route as Laravel Route
    participant Controller as BandingkanPerformaDanMemfilterRiwayatController
    participant Repo as TrainingLogRepository
    participant DB as MySQL Database

    Aktor->>Browser: Pilih minimal 2 atlet -> Tentukan rentang tanggal -> Klik "Bandingkan"
    Browser->>Route: GET /komparasi-performa/data (with query params: athlete_ids, start_date, end_date)
    activate Route
    Route->>Controller: ambilDataKomparasi(Request)
    activate Controller
    Controller->>Controller: Validasi array athlete_ids & rentang tanggal
    
    alt Aktor adalah Pelatih
        Controller->>Controller: Pastikan semua athlete_ids adalah binaan pelatih tersebut
    end

    Controller->>Repo: getComparisonData(athlete_ids, start_date, end_date)
    activate Repo
    Repo->>DB: SELECT athlete_id, SUM(distance_km), AVG(avg_speed), AVG(rpm) FROM training_logs WHERE athlete_id IN (...) GROUP BY athlete_id
    activate DB
    DB-->>Repo: Rata-rata & Total Performa
    deactivate DB
    Repo-->>Controller: Array komparasi
    deactivate Repo

    loop Tiap Athlete ID
        Controller->>Repo: getPerformanceTrend(athlete_id, start_date, end_date)
        activate Repo
        Repo->>DB: SELECT date, avg_speed FROM training_logs WHERE athlete_id = ?
        activate DB
        DB-->>Repo: Data tren individual
        deactivate DB
        Repo-->>Controller: Array tren
        deactivate Repo
    end

    Controller-->>Browser: JSON Response (data komparasi & tren)
    deactivate Controller
    deactivate Route
    Browser->>Browser: Inisialisasi Grafik Batang (Bar Chart) & Grafik Garis Komparatif
    Browser-->>Aktor: Tampilkan kartu komparasi detail & perbandingan visual
```

---

## UC-10 Lihat Laporan Riwayat Performa

### UC-10.1 Lihat Ringkasan Performa Atlet
```mermaid
sequenceDiagram
    autonumber
    actor Aktor as Pelatih / Manajemen
    participant Browser as Laporan Performa (Vue)
    participant Route as Laravel Route
    participant Controller as LihatLaporanRiwayatPerformaController
    participant User as User (Model)
    participant DB as MySQL Database

    Aktor->>Browser: Klik menu "Laporan Performa"
    Browser->>Route: GET /laporan
    activate Route
    Route->>Controller: tampilData()
    activate Controller
    
    alt Aktor adalah Manajemen
        Controller->>User: whereRole('Atlet')->with('latestPhysicalMetric')->get()
        activate User
        User->>DB: SELECT * FROM users LEFT JOIN physical_metrics... (All Athletes)
        activate DB
        DB-->>User: Daftar Atlet & Metrik
        deactivate DB
        User-->>Controller: Collection Atlet
        deactivate User
    else Aktor adalah Pelatih
        Controller->>User: whereRole('Atlet')->where('coach_id', pelatih_id)->with('latestPhysicalMetric')->get()
        activate User
        User->>DB: SELECT * FROM users LEFT JOIN physical_metrics... WHERE coach_id = ?
        activate DB
        DB-->>User: Atlet Binaan & Metrik
        deactivate DB
        User-->>Controller: Collection Atlet
        deactivate User
    end

    Controller-->>Browser: Render Page dengan Inertia Props (data ringkasan atlet)
    deactivate Controller
    deactivate Route
    Browser-->>Aktor: Tampilkan tabel rekapitulasi performa (Jarak total, Durasi, BMI)
```

### UC-10.2 Export CSV Semua Performa Atlet
```mermaid
sequenceDiagram
    autonumber
    actor Aktor as Pelatih / Manajemen
    participant Browser as Laporan Performa (Vue)
    participant Route as Laravel Route
    participant Controller as LihatLaporanRiwayatPerformaController
    participant Log as LogLatihan (Model)
    participant DB as MySQL Database

    Aktor->>Browser: Tentukan rentang tanggal -> Klik "Export CSV Semua Atlet"
    Browser->>Route: POST /laporan/export (body: start_date, end_date)
    activate Route
    Route->>Controller: eksporData(Request)
    activate Controller
    Controller->>Controller: Validasi rentang tanggal
    
    alt Aktor adalah Manajemen
        Controller->>Log: forPeriod(start, end)->with('athlete')->get()
        activate Log
        Log->>DB: SELECT * FROM training_logs INNER JOIN users... WHERE date BETWEEN ? AND ?
        activate DB
        DB-->>Log: Data Log Semua Atlet
        deactivate DB
        Log-->>Controller: Collection Log
        deactivate Log
    else Aktor adalah Pelatih
        Controller->>Log: whereHas('athlete', coach_id)->forPeriod(start, end)->get()
        activate Log
        Log->>DB: SELECT * FROM training_logs INNER JOIN users... WHERE coach_id = ? AND date BETWEEN ? AND ?
        activate DB
        DB-->>Log: Data Log Atlet Binaan
        deactivate DB
        Log-->>Controller: Collection Log
        deactivate Log
    end

    Controller->>Controller: Format data menjadi string CSV (Tanggal, Nama, Jarak, Durasi, dll)
    Controller-->>Browser: Download CSV Stream ("Laporan_Seluruh_Atlet.csv")
    deactivate Controller
    deactivate Route
    Browser-->>Aktor: Mengunduh berkas laporan CSV ke komputer
```

### UC-10.3 Export CSV Salah Satu Performa Atlet
```mermaid
sequenceDiagram
    autonumber
    actor Aktor as Pelatih / Manajemen
    participant Browser as Laporan Performa (Vue)
    participant Route as Laravel Route
    participant Controller as LihatLaporanRiwayatPerformaController
    participant Log as LogLatihan (Model)
    participant DB as MySQL Database

    Aktor->>Browser: Pilih Atlet -> Tentukan tanggal -> Klik "Export CSV" pada baris atlet
    Browser->>Route: POST /laporan/export (body: athlete_id, start_date, end_date)
    activate Route
    Route->>Controller: eksporData(Request)
    activate Controller
    Controller->>Controller: Validasi data input (athlete_id wajib)
    
    alt Aktor adalah Pelatih
        Controller->>Controller: Pastikan athlete_id adalah binaan pelatih (Auth check)
    end

    Controller->>Log: forAthlete(athlete_id)->forPeriod(start, end)->get()
    activate Log
    Log->>DB: SELECT * FROM training_logs WHERE athlete_id = ? AND date BETWEEN ? AND ?
    activate DB
    DB-->>Log: Data Log Atlet
    deactivate DB
    Log-->>Controller: Collection Log
    deactivate Log

    Controller->>Controller: Format data log atlet menjadi CSV
    Controller-->>Browser: Download CSV Stream ("Laporan_[Nama_Atlet].csv")
    deactivate Controller
    deactivate Route
    Browser-->>Aktor: Mengunduh berkas laporan CSV atlet tersebut
```

---

## UC-11 Kelola Evaluasi & Umpan Balik Latihan

### UC-11.1 Lihat Evaluasi & Umpan Balik Latihan
```mermaid
sequenceDiagram
    autonumber
    actor Aktor as Atlet / Pelatih
    participant Browser as Detail Log Latihan (Vue)
    participant Route as Laravel Route
    participant Controller as KelolaEvaluasiDanUmpanBalikLatihanController
    participant Log as LogLatihan (Model)
    participant DB as MySQL Database

    Aktor->>Browser: Klik rincian riwayat latihan
    Browser->>Route: GET /atlet/riwayat-latihan/{log_id} (atau via detail atlet)
    activate Route
    Route->>Controller: lihatDetailLog(log_id)
    activate Controller
    Controller->>Log: find(log_id)
    activate Log
    Log->>DB: SELECT coach_rating, coach_evaluation, coach_comments FROM training_logs WHERE id = ?
    activate DB
    DB-->>Log: Data Evaluasi Pelatih
    deactivate DB
    Log-->>Controller: LogLatihan Instance
    deactivate Log
    Controller-->>Browser: Render Page dengan Inertia Props (evaluasi data)
    deactivate Controller
    deactivate Route
    Browser->>Browser: Ubah angka rating (1-10) menjadi visualisasi bintang
    Browser-->>Aktor: Tampilkan ulasan evaluasi & rating bintang dari pelatih
```

### UC-11.2 Update Evaluasi & Umpan Balik Latihan
```mermaid
sequenceDiagram
    autonumber
    actor Pelatih as Pelatih
    participant Browser as Detail Log Atlet (Vue)
    participant Route as Laravel Route
    participant Controller as KelolaEvaluasiDanUmpanBalikLatihanController
    participant Log as LogLatihan (Model)
    participant DB as MySQL Database

    Pelatih->>Browser: Klik "Evaluasi" -> Isi Ulasan, Rating (1-10), Kehadiran -> Klik Simpan Evaluasi
    Browser->>Route: PATCH /pelatih/riwayat-latihan/{log_id}/evaluation
    activate Route
    Route->>Controller: perbaruiEvaluasi(log_id, Request)
    activate Controller
    Controller->>Controller: Validasi data input (coach_rating, coach_evaluation, status_kehadiran)
    Controller->>Log: find(log_id)
    activate Log
    Log->>DB: SELECT * FROM training_logs WHERE id = ?
    activate DB
    DB-->>Log: Log Data
    deactivate DB
    Log-->>Controller: LogLatihan Instance
    deactivate Log

    Controller->>Controller: Verifikasi hak akses pelatih binaan
    Controller->>Log: update([coach_rating, coach_evaluation, coach_comments, status_kehadiran])
    activate Log
    Log->>DB: UPDATE training_logs SET coach_rating = ?, coach_evaluation = ?, status_kehadiran = ? WHERE id = ?
    activate DB
    DB-->>Log: Success
    deactivate DB
    Log-->>Controller: Success
    deactivate Log

    Controller-->>Browser: JSON Success Response
    deactivate Controller
    deactivate Route
    Browser->>Browser: Update status log di tampilan (misal: "Sudah Dievaluasi")
    Browser-->>Pelatih: Tampilkan alert sukses "Evaluasi berhasil disimpan"
```

---

## UC-12 Kelola Pesan

### UC-12.1 Kirim Pesan
```mermaid
sequenceDiagram
    autonumber
    actor Aktor as Pelatih / Manajemen
    participant Browser as Halaman Pesan (Vue)
    participant Route as Laravel Route
    participant Controller as KelolaPesanController
    participant Message as Pesan (Model)
    participant DB as MySQL Database

    Aktor->>Browser: Buka Form Catatan/Pesan -> Pilih Atlet -> Input Pesan -> Klik Kirim
    Browser->>Route: POST /pesan (prefix pelatih/manajemen)
    activate Route
    Route->>Controller: simpanPesan(Request)
    activate Controller
    Controller->>Controller: Validasi Form Request (receiver_id, message_text)
    Controller->>Message: create([sender_id => auth_id, receiver_id => atlet_id, content => message_text, is_read => false])
    activate Message
    Message->>DB: INSERT INTO messages (sender_id, receiver_id, content, is_read) VALUES (...)
    activate DB
    DB-->>Message: Message Saved
    deactivate DB
    Message-->>Controller: Message Instance
    deactivate Message

    Controller-->>Browser: JSON Success Response (Message sent)
    deactivate Controller
    deactivate Route
    Browser->>Browser: Tambahkan pesan ke log percakapan
    Browser-->>Aktor: Tampilkan status terkirim & alert sukses
```

### UC-12.2 Lihat Pesan
```mermaid
sequenceDiagram
    autonumber
    actor Atlet as Atlet
    participant Browser as Dashboard/Pesan Atlet (Vue)
    participant Route as Laravel Route
    participant Controller as KelolaPesanController
    participant Message as Pesan (Model)
    participant DB as MySQL Database

    Atlet->>Browser: Masuk Dashboard / Klik panel Pesan Pelatih
    Browser->>Route: GET /atlet/dashboard (atau load pesan)
    activate Route
    Route->>Controller: ambilPesan() (atau bagian dari dashboard load)
    activate Controller
    Controller->>Message: where('receiver_id', atlet_id)->orderBy('created_at', 'desc')->get()
    activate Message
    Message->>DB: SELECT * FROM messages WHERE receiver_id = ? ORDER BY created_at DESC
    activate DB
    DB-->>Message: Daftar Pesan
    deactivate DB
    Message-->>Controller: Collection Pesan
    deactivate Message
    Controller-->>Browser: Render Page/JSON (Pesan dengan status is_read)
    deactivate Controller
    deactivate Route

    Browser-->>Atlet: Tampilkan list pesan dengan badge "Belum Dibaca"
    
    Atlet->>Browser: Klik tombol "Tandai Dibaca" pada salah satu pesan
    Browser->>Route: PATCH /atlet/pesan/{pesan_id}/read
    activate Route
    Route->>Controller: tandaiSudahDibaca(pesan_id)
    activate Controller
    Controller->>Message: find(pesan_id)
    activate Message
    Message->>DB: SELECT * FROM messages WHERE id = ?
    activate DB
    DB-->>Message: Message Data
    deactivate DB
    Message-->>Controller: Message Instance
    deactivate Message

    Controller->>Message: update([is_read => true])
    activate Message
    Message->>DB: UPDATE messages SET is_read = true WHERE id = ?
    activate DB
    DB-->>Message: Success
    deactivate DB
    Message-->>Controller: Success
    deactivate Message

    Controller-->>Browser: JSON Success Response
    deactivate Controller
    deactivate Route
    Browser->>Browser: Ubah tampilan pesan menjadi redup / hilangkan badge unread
    Browser-->>Atlet: Status pesan diperbarui
```

### UC-12.3 Hapus Pesan
```mermaid
sequenceDiagram
    autonumber
    actor Pelatih as Pelatih
    participant Browser as Halaman Pesan (Vue)
    participant Route as Laravel Route
    participant Controller as KelolaPesanController
    participant Message as Pesan (Model)
    participant DB as MySQL Database

    Pelatih->>Browser: Klik tombol "Hapus" (X) pada pesan yang pernah dikirim
    Browser->>Route: DELETE /pesan/{pesan_id} (prefix pelatih/manajemen)
    activate Route
    Route->>Controller: hapusPesan(pesan_id)
    activate Controller
    Controller->>Message: find(pesan_id)
    activate Message
    Message->>DB: SELECT * FROM messages WHERE id = ?
    activate DB
    DB-->>Message: Message Data
    deactivate DB
    Message-->>Controller: Message Instance
    deactivate Message

    Controller->>Controller: Verifikasi hak kepemilikan pesan (sender_id === auth_id)
    Controller->>Message: delete()
    activate Message
    Message->>DB: DELETE FROM messages WHERE id = ?
    activate DB
    DB-->>Message: Success
    deactivate DB
    Message-->>Controller: Success
    deactivate Message

    Controller-->>Browser: JSON Success Response
    deactivate Controller
    deactivate Route
    Browser->>Browser: Hapus baris pesan dari riwayat
    Browser-->>Pelatih: Tampilkan alert sukses penarikan pesan
```

---

## UC-13 Kelola Event & Partisipasi

### UC-13.1 Buat Event & Menetapkan Partisipasi
```mermaid
sequenceDiagram
    autonumber
    actor Aktor as Pelatih / Manajemen
    participant Browser as Kelola Acara (Vue)
    participant Route as Laravel Route
    participant Controller as KelolaEventDanPartisipasiController
    participant ModelEvent as Event (Model)
    participant DB as MySQL Database

    Aktor->>Browser: Klik "Tambah Event Baru" -> Input Detail Perlombaan & Pilih Atlet -> Simpan
    Browser->>Route: POST /acara (prefix pelatih/manajemen)
    activate Route
    Route->>Controller: simpanData(Request)
    activate Controller
    Controller->>Controller: Validasi Form Request (Nama Event, Tanggal, Lokasi, Kategori)
    Controller->>ModelEvent: create([nama, tanggal, lokasi, jenis_event_id, ...])
    activate ModelEvent
    ModelEvent->>DB: INSERT INTO events (name, date, location, ...) VALUES (...)
    activate DB
    DB-->>ModelEvent: Event Created (ID Event)
    deactivate DB
    ModelEvent-->>Controller: Event Instance
    deactivate ModelEvent

    Controller->>ModelEvent: athletes()->sync(daftar_atlet_ids)
    activate ModelEvent
    ModelEvent->>DB: INSERT INTO event_user (event_id, user_id, status => "Berencana") VALUES (...)
    activate DB
    DB-->>ModelEvent: Synchronized
    deactivate DB
    ModelEvent-->>Controller: Sync Success
    deactivate ModelEvent

    Controller-->>Browser: JSON Success Response
    deactivate Controller
    deactivate Route
    Browser->>Browser: Tambahkan event ke kalender / daftar acara
    Browser-->>Aktor: Tampilkan alert sukses pembuatan event
```

### UC-13.2 Lihat Event & Menetapkan Partisipasi
```mermaid
sequenceDiagram
    autonumber
    actor Aktor as Pelatih / Atlet / Manajemen
    participant Browser as Halaman Event/Acara (Vue)
    participant Route as Laravel Route
    participant Controller as KelolaEventDanPartisipasiController
    participant ModelEvent as Event (Model)
    participant DB as MySQL Database

    Aktor->>Browser: Klik menu "Event/Acara"
    Browser->>Route: GET /acara (prefix sesuai role)
    activate Route
    Route->>Controller: tampilData()
    activate Controller

    alt Aktor adalah Atlet
        Controller->>ModelEvent: whereHas('athletes', auth_id)->get()
        activate ModelEvent
        ModelEvent->>DB: SELECT * FROM events INNER JOIN event_user WHERE user_id = ?
        activate DB
        DB-->>ModelEvent: Daftar Event Atlet
        deactivate DB
        ModelEvent-->>Controller: Collection Event
        deactivate ModelEvent
    else Aktor adalah Pelatih / Manajemen
        Controller->>ModelEvent: with('athletes')->get()
        activate ModelEvent
        ModelEvent->>DB: SELECT * FROM events (All / Coached events with participants)
        activate DB
        DB-->>ModelEvent: Daftar Event Global
        deactivate DB
        ModelEvent-->>Controller: Collection Event
        deactivate ModelEvent
    end

    Controller-->>Browser: Render Page dengan Inertia Props (data event)
    deactivate Controller
    deactivate Route
    Browser-->>Aktor: Tampilkan daftar event (Upcoming & Past) serta partisipasi atlet
```

### UC-13.3 Hapus Event & Menetapkan Partisipasi
```mermaid
sequenceDiagram
    autonumber
    actor Aktor as Pelatih / Manajemen
    participant Browser as Kelola Acara (Vue)
    participant Route as Laravel Route
    participant Controller as KelolaEventDanPartisipasiController
    participant ModelEvent as Event (Model)
    participant DB as MySQL Database

    Aktor->>Browser: Klik "Hapus" pada baris event -> Klik Konfirmasi Hapus
    Browser->>Route: DELETE /acara/{id} (prefix pelatih/manajemen)
    activate Route
    Route->>Controller: hapusData(id)
    activate Controller
    Controller->>ModelEvent: find(id)
    activate ModelEvent
    ModelEvent->>DB: SELECT * FROM events WHERE id = ?
    activate DB
    DB-->>ModelEvent: Event Data
    deactivate DB
    ModelEvent-->>Controller: Event Instance
    deactivate ModelEvent

    Controller->>ModelEvent: delete()
    activate ModelEvent
    ModelEvent->>DB: DELETE FROM events WHERE id = ? (Cascade/Delete Pivot event_user)
    activate DB
    DB-->>ModelEvent: Success
    deactivate DB
    ModelEvent-->>Controller: Success
    deactivate ModelEvent

    Controller-->>Browser: JSON Success Response
    deactivate Controller
    deactivate Route
    Browser->>Browser: Hapus baris event dari tabel
    Browser-->>Aktor: Tampilkan alert sukses "Event dihapus"
```

---

## UC-14 Kelola Dokumen Lisensi UCI

### UC-14.1 Lihat Dokumen Atlet dan Lihat Lisensi UCI
```mermaid
sequenceDiagram
    autonumber
    actor Aktor as Atlet / Pelatih / Manajemen
    participant Browser as Halaman Lisensi (Vue)
    participant Route as Laravel Route
    participant Controller as KelolaDokumenLisensiUciController
    participant Profile as ProfilAtlet (Model)
    participant DB as MySQL Database

    Aktor->>Browser: Klik menu "Lisensi & Dokumen"
    Browser->>Route: GET /lisensi-uci
    activate Route
    Route->>Controller: tampilData() (atau tampilHalamanLisensi)
    activate Controller
    Controller->>Profile: where('user_id', atlet_id)->first()
    activate Profile
    Profile->>DB: SELECT uci_id, license_path, id_card_path, license_valid_until FROM athlete_profiles WHERE user_id = ?
    activate DB
    DB-->>Profile: Data Profil Atlet
    deactivate DB
    Profile-->>Controller: ProfilAtlet Instance
    deactivate Profile
    Controller-->>Browser: Render Page dengan Inertia Props (data lisensi & dokumen)
    deactivate Controller
    deactivate Route
    Browser-->>Aktor: Tampilkan status kelengkapan dokumen & nomor UCI ID
```

### UC-14.2 Update Dokumen Atlet
```mermaid
sequenceDiagram
    autonumber
    actor Atlet as Atlet
    participant Browser as Halaman Lisensi (Vue)
    participant Route as Laravel Route
    participant Controller as KelolaDokumenLisensiUciController
    participant Profile as ProfilAtlet (Model)
    participant Storage as LocalStorage (Disk)
    participant DB as MySQL Database

    Atlet->>Browser: Pilih berkas KTP/KK/Akte -> Klik "Unggah Dokumen"
    Browser->>Route: POST /lisensi-uci/upload
    activate Route
    Route->>Controller: simpanDokumenPribadi(Request)
    activate Controller
    Controller->>Controller: Validasi berkas (PDF/JPG/PNG max 5MB)
    Controller->>Storage: store(file, 'private_documents/atlet_id')
    activate Storage
    Storage-->>Controller: Path berkas tersimpan (misal: private_documents/1/ktp.pdf)
    deactivate Storage

    Controller->>Profile: updateOrCreate([user_id => auth_id], [id_card_path => path])
    activate Profile
    Profile->>DB: INSERT/UPDATE athlete_profiles SET id_card_path = ? WHERE user_id = ?
    activate DB
    DB-->>Profile: Success
    deactivate DB
    Profile-->>Controller: ProfilAtlet Instance
    deactivate Profile

    Controller-->>Browser: JSON Success Response
    deactivate Controller
    deactivate Route
    Browser->>Browser: Perbarui status dokumen di UI ("Menunggu Review")
    Browser-->>Atlet: Tampilkan alert sukses unggah dokumen
```

### UC-14.3 Update Lisensi UCI
```mermaid
sequenceDiagram
    autonumber
    actor Manajemen as Manajemen
    participant Browser as Halaman Lisensi Atlet (Vue)
    participant Route as Laravel Route
    participant Controller as KelolaDokumenLisensiUciController
    participant Profile as ProfilAtlet (Model)
    participant DB as MySQL Database

    Manajemen->>Browser: Klik "Edit Lisensi UCI" -> Input UCI ID & Masa Berlaku -> Klik Simpan
    Browser->>Route: POST /lisensi-uci/update/{atlet_id}
    activate Route
    Route->>Controller: perbaruiLisensiUci(atlet_id, Request)
    activate Controller
    Controller->>Controller: Validasi input (uci_id, license_valid_until)
    Controller->>Profile: updateOrCreate([user_id => atlet_id], [uci_id, license_valid_until])
    activate Profile
    Profile->>DB: UPDATE athlete_profiles SET uci_id = ?, license_valid_until = ? WHERE user_id = ?
    activate DB
    DB-->>Profile: Success
    deactivate DB
    Profile-->>Controller: Success
    deactivate Profile

    Controller-->>Browser: JSON Success Response
    deactivate Controller
    deactivate Route
    Browser->>Browser: Perbarui tampilan lisensi atlet
    Browser-->>Manajemen: Tampilkan alert sukses "Lisensi UCI diperbarui"
```

### UC-14.4 Unduh Dokumen Atlet
```mermaid
sequenceDiagram
    autonumber
    actor Manajemen as Manajemen
    participant Browser as Halaman Lisensi Atlet (Vue)
    participant Route as Laravel Route
    participant Controller as KelolaDokumenLisensiUciController
    participant Profile as ProfilAtlet (Model)
    participant Zip as ZipArchive (Service)
    participant Storage as LocalStorage (Disk)
    participant DB as MySQL Database

    Manajemen->>Browser: Klik "Unduh Semua Dokumen" pada baris atlet
    Browser->>Route: GET /lisensi-uci/download-all/{atlet_id}
    activate Route
    Route->>Controller: unduhSemuaDokumen(atlet_id)
    activate Controller
    Controller->>Profile: where('user_id', atlet_id)->first()
    activate Profile
    Profile->>DB: SELECT profile_photo_path, birth_certificate_path, family_card_path, id_card_path, license_path FROM athlete_profiles WHERE user_id = ?
    activate DB
    DB-->>Profile: Berkas Path Data
    deactivate DB
    Profile-->>Controller: ProfilAtlet Instance
    deactivate Profile

    Controller->>Zip: open(temp_zip_file)
    activate Zip
    loop Untuk Setiap Berkas yang Ada
        Controller->>Storage: read(file_path)
        activate Storage
        Storage-->>Controller: File Binary Data
        deactivate Storage
        Controller->>Zip: addFromString(file_name, binary_data)
    end
    Zip-->>Controller: Zip File Created
    deactivate Zip

    Controller-->>Browser: Download Zip Stream ("[nama_atlet]_dokumen.zip")
    deactivate Controller
    deactivate Route
    Browser-->>Manajemen: File ZIP terunduh di sistem lokal
```

---

## UC-15 Gunakan Kalkulator Gear Sepeda

### UC-15.1 Gunakan Kalkulator Gear Sepeda
```mermaid
sequenceDiagram
    autonumber
    actor Aktor as Atlet / Pelatih / Manajemen
    participant Browser as Halaman Kalkulator Gear (Vue)
    participant Route as Laravel Route
    participant Controller as GunakanKalkulatorGearSepedaController

    Aktor->>Browser: Klik menu "Tools" > "Gear Calculator"
    Browser->>Route: GET /tools/gear-calculator
    activate Route
    Route->>Controller: tampilHalamanKalkulator()
    activate Controller
    Controller-->>Browser: Render Page (Inertia View)
    deactivate Controller
    deactivate Route
    Browser-->>Aktor: Tampilkan form Kalkulator Gear
    
    note over Aktor, Browser: Proses kalkulasi berjalan sepenuhnya di Client-Side (Vue Component)
    Aktor->>Browser: Pilih Chainring (53T), Cog (11T), Ukuran Ban (700x25c) & Kadens (90 RPM)
    activate Browser
    Browser->>Browser: Hitung Gear Ratio = 53 / 11 = 4.82
    Browser->>Browser: Hitung Rollout (Meters Development) = 4.82 * Lingkar Ban (2.105m) = 10.15m
    Browser->>Browser: Hitung Estimated Speed = 10.15m * 90 RPM * 60 menit / 1000 = 54.81 km/h
    Browser-->>Aktor: Perbarui angka hasil kalkulasi di layar secara real-time (tanpa submit/reload)
    deactivate Browser
```
