# SIMORA - Sistem Informasi Monitoring Atlet Sepeda

## Ringkasan Dokumen PDF

Dokumen: **"Pengembangan Sistem Monitoring Latihan Atlet Sepeda Berbasis Web pada Kohar Cycling Club"**
Halaman: 84 halaman

---

## 1. Latar Belakang

Kohar Cycling Club saat ini masih menggunakan pencatatan manual (Excel) untuk data latihan atlet. Sistem SIMORA dikembangkan untuk menggantikan proses manual tersebut dengan sistem web-based yang dapat:
- Mencatat data latihan secara digital
- Menganalisis performa atlet secara visual
- Memberikan rekomendasi latihan oleh pelatih
- Menampilkan dashboard analitik performa

---

## 2. User Roles & Hak Akses

### Atlet
- Registrasi & kelola profil fisik (Tinggi, Berat, Usia → BMI otomatis)
- Input data latihan (Jarak, Durasi, Kecepatan, Detak Jantung, Cadence, Kalori)
- Lihat riwayat latihan & grafik performa
- Terima feedback/rekomendasi dari pelatih

### Pelatih
- Monitoring semua atlet di bawah supervisi
- Kelola jadwal latihan & presensi kehadiran atlet
- Analisis grafik performa atlet
- Berikan rekomendasi & feedback latihan

### Manajemen
- Kelola akun user & role
- Lihat dashboard monitoring tingkat tinggi
- Export laporan performa ke Excel/PDF

---

## 3. Kebutuhan Fungsional

| No | Fitur | Deskripsi |
|----|-------|-----------|
| 1 | Autentikasi | Login & registrasi untuk 3 role berbeda |
| 2 | Manajemen Latihan | Input & simpan metrik: tanggal, kecepatan, detak jantung, cadence, jarak, kalori |
| 3 | Monitoring Fisik | Kalkulasi BMI otomatis, tracking perubahan fisik |
| 4 | Analitik Visual | Grafik performa (ApexCharts) berbasis time-series |
| 5 | Jadwal & Presensi | Pelatih set jadwal latihan, tracking kehadiran atlet |
| 6 | Reporting | Export data latihan via Laravel Excel |
| 7 | Upload Bukti | Atlet upload bukti latihan (gambar/dokumen) |
| 8 | Rekomendasi | Pelatih berikan rekomendasi & feedback ke atlet |

---

## 4. Data Models / Entitas

### User
- `name`, `email`, `password`, `role_id`

### Athlete Profile
- `user_id`, `height`, `weight`, `category`, `bmi` (auto-calculated)

### Training Record (Data Latihan)
- `athlete_id`, `date`, `distance`, `duration`, `speed`, `heart_rate`, `cadence`, `calories`
- `training_type`, `route`, `image_proof` (bukti latihan)

### Schedule (Jadwal Latihan)
- `coach_id`, `date`, `start_time`, `end_time`, `description`

### Presence (Presensi)
- `schedule_id`, `athlete_id`, `attendance_status`

### Feedback / Recommendation
- `coach_id`, `athlete_id`, `content`, `date`

---

## 5. Metrik Performa untuk Grafik

Grafik yang harus ditampilkan (semua time-series berdasarkan tanggal):
1. **Kecepatan Rata-rata** (Average Speed - km/h)
2. **Detak Jantung Rata-rata** (Average Heart Rate - bpm)
3. **Cadence** (Pedaling speed - RPM)
4. **Jarak Latihan** (Distance - km)
5. **Kalori Terbakar** (Calories Burned - kcal)

---

## 6. Technology Stack

### Backend
- PHP 8.4
- Laravel 13
- Laravel Sanctum (API Security / cookie-based auth)
- Laravel Breeze (Auth scaffolding) → actually using Fortify
- Laravel Excel (Reporting export)

### Frontend
- Vue.js 3 (SPA)
- Vuetify 4 (UI Component Library)
- Pinia (State Management)
- ApexCharts (Data Visualization)
- Vuelidate (Form Validation)
- Sneat Admin Template (Dashboard UI)

### Database
- MySQL 8.4 (via Docker)

### Infrastructure
- Docker (PHP-FPM + Nginx + MySQL + PHPMyAdmin)

### Arsitektur
- Progressive Web App (PWA) untuk desktop & mobile

---

## 7. Arsitektur Backend (Layered)

```
Controller → Service → Repository → Model
```

- **Controller**: Terima request, return response saja
- **Service**: Semua logika bisnis
- **Repository**: Query database
- **Model**: Eloquent ORM

---

## 8. Frontend Architecture

```
pages/        → Halaman Vue (Inertia pages)
components/   → Komponen reusable
layouts/      → Layout templates
stores/       → Pinia stores
services/     → API service layer
composables/  → Vue composables
```

### Pinia Stores:
- `authStore` → autentikasi
- `trainingStore` → data latihan
- `dashboardStore` → dashboard data
- `notificationStore` → notifikasi

---

## 9. Dashboard UI Layout

```
┌──────────────────────────────────┐
│           Topbar                 │
├──────────┬───────────────────────┤
│ Sidebar  │                       │
│ Nav      │   Content Area        │
│          │                       │
│          │   - Statistik Ringkas  │
│          │   - Grafik Performa   │
│          │   - Riwayat Latihan   │
│          │                       │
└──────────┴───────────────────────┘
```

---

## 10. Status Project Saat Ini

Project masih dalam tahap awal (fresh install):
- ✅ Docker setup (app, nginx, mysql, phpmyadmin)
- ✅ Laravel 13 + Fortify + Inertia v2 terinstall
- ✅ Default User model (tanpa role)
- ✅ Default migrations (users, cache, jobs, two_factor)
- ✅ Basic routes (Welcome, Dashboard, Settings)
- ✅ Vue components dari starter kit (auth pages, settings)
- ✅ Laravel Boost + Wayfinder configured
- ❌ Belum ada model domain (Training, AthleteProfile, Schedule, dll)
- ❌ Belum ada migration untuk tabel domain
- ❌ Belum ada role system (Atlet, Pelatih, Manajemen)
- ❌ Belum ada Vuetify / Sneat template (masih default starter kit)
- ❌ Belum ada Pinia stores
- ❌ Belum ada ApexCharts
- ❌ Belum ada Vuelidate
- ❌ Belum ada service/repository layer di backend
- ❌ Belum ada fitur domain (latihan, jadwal, presensi, dsb)

---

## 11. Docker Environment

| Container | Image | Port | Keterangan |
|-----------|-------|------|------------|
| simora-app | php:8.4-fpm (custom) | - | PHP-FPM backend |
| simora-nginx | nginx:alpine | 8080:80 | Web server |
| simora-db | mysql:8.4 | 3307:3306 | Database MySQL |
| simora-pma | phpmyadmin:latest | 8082:80 | PHPMyAdmin |

**Semua CLI command harus via:** `docker exec -it simora-app <command>`

---

## 12. Yang Perlu Dibangun (Priority Order)

1. **Setup Frontend Stack**: Install Vuetify 4, Pinia, ApexCharts, Vuelidate, integrasi Sneat template
2. **Role System**: Migration + model untuk roles, tambah role_id ke users
3. **Models & Migrations**: AthleteProfile, Training, Schedule, Presence, Feedback
4. **Backend Architecture**: Service layer, Repository layer
5. **API Endpoints**: REST API untuk semua domain
6. **Auth & Middleware**: Role-based middleware
7. **Frontend Pages**: Dashboard, Training CRUD, Schedule, Analytics
8. **Data Visualization**: ApexCharts integration
9. **Reporting**: Laravel Excel export
10. **Upload Bukti**: File upload untuk bukti latihan
