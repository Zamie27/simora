# 🚴‍♂️ SIMORA - Sistem Informasi Monitoring Atlet Sepeda

SIMORA adalah sistem informasi berbasis web yang dirancang untuk membantu pemantauan dan analisis performa latihan atlet sepeda. Sistem ini digunakan untuk mencatat data latihan individu, menampilkan analitik grafis performa (kecepatan rata-rata, detak jantung, cadence, dll), serta memungkinkan pelatih untuk memberikan evaluasi performa dan rekomendasi latihan secara terstruktur.

Proyek ini dibangun menggunakan arsitektur modern (*Vue SPA + Laravel API*) dan berjalan secara penuh di dalam kontainer **Docker**. Hal ini membuat *environment web* benar-benar terisolasi dan menjamin semua anggota tim maupun evaluator/dosen memiliki standar platform yang identik (tanpa konflik dependensi antar _OS_ atau versi PHP/Node di laptop lokal).

<div align="center">
  <img src="public/images/simora_logo.png" alt="Logo SIMORA" width="250">
</div>

---

## 🛠 Teknologi yang Digunakan

**Frontend (SPA Client)**
- **Vue.js 3** & **Vuetify 4** (Sneat Admin Template)
- **Pinia** (State Management) & **Vuelidate** (Form Validation)
- **ApexCharts** (Visualisasi Data Metrik Sepeda)

**Backend (REST API)**
- **PHP 8.4** & **Laravel 13**
- **Laravel Sanctum** (Cookie-based Authentication) & Laravel Breeze
- **Database:** MySQL 8.4

**Infrastruktur & Lingkungan Eksekusi**
- **Docker** & **Docker Compose**
- **Nginx** (Web Server Alpine)

---

## ⚙️ Prasyarat Instalasi

Sebelum memulai integrasi, pastikan sistem operasi/laptop Anda sudah terinstal perangkat lunak berikut:
1. **Git**
2. **Docker** dan **Docker Compose** 
   > _Pastikan service atau daemon Docker sudah berstatus "Running" di komputer Anda terlebih dahulu._

> 💡 **PENTING: Memulai Dengan File `.env` (Environment Variables)**
> Demi keamanan kredensial, file konfigurasi utama `.env` **sengaja tidak diikutsertakan/di-_ignore_ di dalam repository Git ini**. 
> 
> **Silakan hubungi pengembang sistem (Ketua Tim/Dev Manager) untuk meminta salinan file `.env`**. File ini berisi konfigurasi akses database dan pengaturan kunci framework yang sesuai. Tanpanya, aplikasi pasti akan *error*.

---

## 🚀 Cara Menjalankan Proyek (Setup Pertama Kali Pull)

Mohon untuk mengikuti langkah-langkah di bawah ini secara **berurutan** saat Anda pertama kali mengunduh (pull/clone) dari *repository* Github ini:

### 1. Clone Repositori
Buka terminal/CMD dan eksekusi perintah di bawah:
```bash
git clone https://github.com/USERNAME/simora.git
cd simora
```

### 2. Konfigurasi Environment (`.env`)
1. Dapatkan file `.env` dari salah satu anggota tim pengembang Anda.
2. _Copy/paste_ file tersebut ke dalam folder *root* proyek (alias direktori `simora/`).
*(Ingat, jangan sampai nama file terubah. Harus persis `.env`).*

### 3. Build & Jalankan Container Docker
Proyek ini mengandalkan _Docker Compose_ untuk membentuk ekosistem *web server*, database *MySQL*, dan OS aplikasi Laravel (*PHP-FPM + Node.js*).
```bash
docker-compose up -d --build
```
> *Tunggu hingga proses download image `MySQL`, `Nginx`, `PHP-FPM`, serta instalasi `Node.js 24 LTS` di dalam baris kode container selesai. Perintah ini akan menyalakan ekosistem server di latar belakang/background (`-d`).*

### 4. Setup Dependensi Utama (⚠️ Lakukan Di Dalam Container)
Karena aplikasi utuh berjalan dan hidup di dalam rumah Docker bernama `simora-app`, **segala proses instalasi framework dependency HARUS dieksekusi dan diketik di dalam kontainer sistem tersebut.** 

Ketik baris perintah (_command_) berikut satu per satu:

**a. Install Keseluruhan Package Laravel Backend (Composer):**
```bash
docker exec -it simora-app composer install
```

**b. Buat Kunci Aplikasi & Link Direktori Penyimpanan:**
```bash
docker exec -it simora-app php artisan key:generate
docker exec -it simora-app php artisan storage:link
```

**c. Pembuatan Tabel & Ekspor Data Dummy (Migrate & Seed):**
```bash
docker exec -it simora-app php artisan migrate:fresh --seed
```
> _Perintah ini membentuk pondasi tabel database dan mengisi otomatis beberapa data atlet, jadwal latihan, serta user default untuk bisa sekadar dicoba masuk (login test)._

**d. Install Package Pendukung Frontend (NPM):**
```bash
docker exec -it simora-app npm install
```

### 5. Aktivasi & Compile Resource UI (Vite Server)
Agar aset gaya (_CSS/Vue file/Vuetify component_) berhasil disajikan secara *Real-Time*, jalankan Node server dengan pengeksekusian perintah berikut:
```bash
docker exec -it simora-app npm run dev
```
> _Terminal Anda mungkin akan sedikit "tertahan" karena Vite berjalan di *foreground*, biarkan saja dan **buka tab terminal baru** bila Anda perlu mengetikkan perintah baru lainnya._

---

## 🌐 Akses Tautan Aplikasi & Basis Data

Jika Anda sudah berhasil melewati instruksi bernomor 1-5 di atas, silakan akses *link* dari _browser_ Anda (seperti _Chrome_ atau _Edge_):

| Layanan Aplikasi | Akses Tautan (URL) | Port |
| :--- | :--- | :--- |
| **Aplikasi Website SIMORA** | [http://localhost:8080](http://localhost:8080) | `8080` |
| **Akses Database Management (PHPMyAdmin)** | [http://localhost:8082](http://localhost:8082) | `8082` |
| **Akses Langsung Jaringan MySQL** | `127.0.0.1` | `3307` |

> *Untuk memasuki ruang kontrol **PHPMyAdmin**, lakukan login menggunakan kredensial (username & password) database yang termaktub tegas di dalam file konfigurasi `.env` Anda tadi.*

---

## 💻 Panduan Aturan Terminal / Command (Khusus Tim)

> **⚠️ Peringatan Terpenting untuk Tim Pengembang Mendasar:** 
> Arsitektur kode server kita diwadahkan dalam environment bernama Docker! **TOLONG JANGAN mengeksekusi perintah manual `php artisan`, `composer`, atau `npm` di command prompt laptop lokal / host Anda!** 
> *Karena kalian akan mendapatkan kegagalan versi yang tak cocok atau folder node/vendor yang hancur alias 'conflict'.*
> 
> Selalu hantui awalan perintah *System Commands* dengan penambahan *prefix* berikut:  
> 👉  **`docker exec -it simora-app [perintah_anda]`**

**Dokumentasi Contoh Perintah Rutin Harian Paling Berguna:**
- **Membuat Controller Baru:**
  `docker exec -it simora-app php artisan make:controller Api/NotificationController`
- **Menambah NPM Package:**
  `docker exec -it simora-app npm install js-cookie`
- **Mengecek Status Hidup/Mati Semua Docker:**
  `docker-compose ps`
- **Mematikan Wadah Kontainer Sementara Tanpa Menghancurkan Tabel:**
  `docker-compose stop`
- **Menghidupkan Lagi Pada Hari Besoknya:**
  `docker-compose start`
- **Akan Matikan Semua dan Lenyapkan Data Volume (Bersih Suci Kembali ke Titik Awal)*:**
  `docker-compose down -v`

---

## Troubleshooting (Solusi Kendala Umum)

### Tampilan Website Blank Putih (Vite Port Mismatch)
Jika Anda membuka `localhost:8080` namun hanya melihat layar putih kosong, ini biasanya karena Vite di dalam Docker berjalan di port yang salah atau aksesnya terhalang.

**Penyebab:** Ada proses `npm run dev` lama yang masih menggantung atau port `5174` sibuk, sehingga Vite pindah ke port lain (misal `5176`) yang tidak terdaftar di Docker.

**Langkah Solusi:**
1. **Bersihkan Cache Koneksi:**
   ```bash
   docker exec simora-app rm -f public/hot
   ```
2. **Matikan Proses Node Tersembunyi:**
   ```bash
   docker exec simora-app pkill node
   ```
3. **Jalankan Ulang Vite:**
   ```bash
   docker exec -it simora-app npm run dev
   ```
4. **Refresh Browser:** Tekan `Ctrl + F5` untuk membersihkan cache browser Anda.

---

## 👥 Profil Evaluator & Peran Anggota Tim

Modul aplikasi dibentuk berdasarkan tugas manajemen sistem informasi terpisah dengan 3 cakupan _Role/Aktor_ penting:
1. **Pihak Manajemen Klub** - Dapat mengubah parameter struktur user manajemen dan klub pelatih.  
2. **Pelatih** - Menganalisa statistik grafik metrik siswa dan berwawan rekomendasi.  
3. **Atlet Pesepeda** - Mengkoleksi serta mempublikasikan performa kalori, kecepatan, dll  setelah pasca latihan.  

*Dibuat & didedikasi komprehensif bagi proses penilaian Dosen Pembimbing untuk mata kuliah Pengembangan Sistem Informasi.*