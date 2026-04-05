# SIMORA - Sistem Informasi Monitoring Atlet Sepeda

SIMORA adalah sistem informasi berbasis web yang dirancang untuk memfasilitasi pemantauan dan analisis performa latihan atlet sepeda secara sistematis. Sistem ini berfungsi untuk mencatat data latihan individu, menyajikan analitik performa melalui visualisasi grafis (rata-rata kecepatan, detak jantung, cadence, dsb.), serta memungkinkan pelatih untuk memberikan evaluasi dan rekomendasi latihan yang terstruktur.

Proyek ini menggunakan arsitektur modern (Vue SPA + Laravel API) dan dijalankan sepenuhnya menggunakan kontainer Docker. Hal ini dilakukan untuk memastikan lingkungan pengembangan yang terisolasi, menjamin konsistensi di seluruh platform, dan menghindari konflik dependensi antar sistem operasi.

<div align="center">
  <img src="public/images/simora_logo.png" alt="Logo SIMORA" width="250">
</div>

---

## Teknologi yang Digunakan

### Frontend (SPA Client)
- **Vue.js 3** & **Vuetify 3** (Sneat Admin Template)
- **Pinia** (State Management)
- **Vuelidate** (Form Validation)
- **ApexCharts** (Visualisasi Data Metrik)

### Backend (REST API)
- **PHP 8.4** & **Laravel 13**
- **Laravel Sanctum** (Cookie-based Authentication)
- **Laravel Breeze**
- **Database:** MySQL 8.4

### Infrastruktur
- **Docker** & **Docker Compose**
- **Nginx** (Web Server Alpine)

---

## Prasyarat Instalasi

Sebelum memulai instalasi, pastikan sistem operasi Anda telah memiliki perangkat lunak berikut:
1. **Git**
2. **Docker** dan **Docker Compose**

### Konfigurasi Environment (`.env`)
File konfigurasi `.env` tidak disertakan dalam repositori ini demi alasan keamanan. Harap hubungi pengembang utama atau manajer proyek untuk mendapatkan salinan file `.env` yang valid. File ini diperlukan untuk konfigurasi database dan kunci aplikasi.

---

## Instruksi Instalasi (Setup Pertama Kali)

Ikuti langkah-langkah berikut secara berurutan untuk menyiapkan lingkungan pengembangan setelah melakukan kloning repositori:

### 1. Kloning Repositori
```bash
git clone https://github.com/Zamie27/simora.git
cd simora
```

### 2. Konfigurasi File Environment
Salin file `.env` yang telah diperoleh ke direktori akar (root) proyek dengan nama file `.env`.

### 3. Membangun dan Menjalankan Kontainer Docker
```bash
docker-compose up -d --build
```
Proses ini akan mengunduh image yang diperlukan dan membangun kontainer di latar belakang.

### 4. Konfigurasi Dependensi (Eksekusi di Dalam Kontainer)
Eksekusi perintah berikut untuk mengonfigurasi framework dan dependensi di dalam kontainer `simora-app`:

**a. Instalasi Dependensi Backend (Composer):**
```bash
docker exec -it simora-app composer install
```

**b. Menghasilkan Kunci Aplikasi & Symlink Storage:**
```bash
docker exec -it simora-app php artisan key:generate
docker exec -it simora-app php artisan storage:link
```

**c. Migrasi Database & Seeding Data:**
```bash
docker exec -it simora-app php artisan migrate:fresh --seed
```

**d. Instalasi Dependensi Frontend (NPM):**
```bash
docker exec -it simora-app npm install
```

### 5. Menjalankan Development Server (Vite)
```bash
docker exec -it simora-app npm run dev
```

---

## Akses Layanan Aplikasi

Setelah seluruh proses instalasi selesai, aplikasi dapat diakses melalui peramban pada alamat berikut:

| Layanan | Alamat (URL) | Port |
| :--- | :--- | :--- |
| **Aplikasi Utama** | [http://localhost:8080](http://localhost:8080) | `8080` |
| **PHPMyAdmin** | [http://localhost:8082](http://localhost:8082) | `8082` |
| **MySQL (Direct Access)** | `127.0.0.1` | `3307` |

---

## Panduan Perintah Terminal (Khusus Pengembang)

Pengeksekusian perintah framework (Artisan, Composer, NPM) harus dilakukan di dalam lingkungan Docker untuk menghindari konflik dependensi pada sistem host. Gunakan prefiks berikut untuk menjalankan perintah:

**Prefix Utama:** `docker exec -it simora-app [perintah_anda]`

### Contoh Perintah Rutin:
- **Membuat Controller:** `docker exec -it simora-app php artisan make:controller NamaController`
- **Menambah Package NPM:** `docker exec -it simora-app npm install nama-package`
- **Menghentikan Kontainer:** `docker-compose stop`
- **Menjalankan Kontainer:** `docker-compose start`

---

## Troubleshooting (Panduan Penyelesaian Kendala)

### Masalah Layanan Blank Putih (Vite Port Mismatch)
Kendala ini biasanya terjadi karena Vite menggunakan port yang tidak terdaftar pada konfigurasi Docker akibat adanya proses yang belum berhenti sempurna.

**Langkah Penyelesaian:**
1. Hapus file cache Vite: `docker exec simora-app rm -f public/hot`
2. Hentikan proses Node yang menggantung: `docker exec simora-app pkill node`
3. Jalankan kembali server Vite: `docker exec -it simora-app npm run dev`
4. Bersihkan cache peramban (Ctrl + F5).

### Kendala Git: "Rejected (non-fast-forward)"
Terjadi saat terdapat perbedaan antara riwayat commit di repositori lokal dan remote.

**Langkah Penyelesaian:**
1. Konfigurasi strategi pull: `git config pull.rebase false`
2. Lakukan sinkronisasi: `git pull origin main`
3. Push kembali perubahan Anda: `git push origin main`

---

## Peran Pengguna dalam Sistem

Sistem ini mendukung tiga kategori pengguna utama:
1. **Manajemen**: Bertanggung jawab atas pengelolaan data pengguna dan parameter sistem.
2. **Pelatih**: Melakukan analisis performa atlet dan memberikan rekomendasi latihan.
3. **Atlet**: Mencatat dan memantau hasil performa latihan secara mandiri.

Sistem ini dikembangkan secara komprehensif sebagai bagian dari proses evaluasi pada mata kuliah Pengembangan Sistem Informasi.