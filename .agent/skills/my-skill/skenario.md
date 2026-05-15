# Standar Penulisan Skenario Use Case SIMORA

Dokumen ini berisi panduan dan standar penulisan skenario use case untuk proyek SIMORA, berdasarkan format terbaru yang mencakup penggunaan Use Case Global dan Turunan.

## Struktur Dokumentasi

Setiap dokumen skenario harus mengikuti urutan berikut:

1. **Judul Global**: Nama Use Case Global (Contoh: **8. Analisa Grafik & Statistik Latihan**).
2. **Deskripsi Global**: Penjelasan singkat fungsi use case global tersebut.
3. **Tabel Daftar Use Case**: Ringkasan seluruh use case turunan.
4. **Detail Skenario**: Tabel rinci untuk setiap use case turunan.

## Ketentuan Verifikasi Sistem

Sebelum membuat skenario, Anda WAJIB melakukan verifikasi terhadap sistem nyata untuk memastikan akurasi data:

1. **Verifikasi UI (View/Frontend)**: Cek file Vue atau Blade terkait untuk memastikan nama menu, label tombol, dan alur interaksi sesuai dengan yang tampil di layar.
2. **Verifikasi Logic (Controller/Backend)**: Cek Controller dan Service terkait untuk memahami alur proses data, validasi, dan respon sistem yang sebenarnya.
3. **Verifikasi Route**: Pastikan endpoint dan akses role sesuai dengan definisi di file routing.
4. **Sinkronisasi Referensi Dokumen**: Anda (AI Agent) **WAJIB** membaca file `srs.txt`, `uc.txt`, dan `uc-turunan.txt` yang bera da di direktori ini sebelum membuat skenario. Pastikan nomor, nama, dan detail skenario merujuk pada referensi tersebut.

> [!CAUTION]
> **INSTRUKSI KHUSUS AI AGENT (HARAP DIPATUHI):**
>
> 1. Jangan membuat skenario secara asal. Selalu gunakan `grep_search`/`view_file` ke frontend/backend dan sinkronkan dengan `srs.txt`, `uc.txt`, & `uc-turunan.txt`.
> 2. **JANGAN PERNAH** merubah, mengacak-acak, atau membuat file skenario.md baru ketika diminta "buatkan skenario dari use case X". File ini bersifat **LOCKED**.
> 3. Hasil _generate_ skenario **HANYA BOLEH** Anda tampilkan di dalam jendela **CHAT** ini langsung, tanpa memanipulasi file dokumentasinya.

> [!IMPORTANT]
> Jangan membuat skenario berdasarkan asumsi. Gunakan tool `grep_search` atau `view_file` untuk melihat kode sumber aslinya sebelum menuliskan langkah-langkah skenario.

---

## 1. Format Tabel Daftar Use Case

Gunakan tabel ini untuk merangkum semua use case turunan di bawah satu use case global.

| No. Use Case | Nama Use Case  | Deskripsi                          |
| :----------- | :------------- | :--------------------------------- |
| UC-XX.1      | [Nama Turunan] | [Deskripsi singkat fungsi turunan] |
| UC-XX.2      | [Nama Turunan] | [Deskripsi singkat fungsi turunan] |

---

## 2. Format Tabel Skenario (Turunan)

Gunakan format berikut untuk setiap detail skenario turunan:

| [ID Use Case, misal: UC-08.1] |                                 |
| :---------------------------- | :------------------------------ |
| **Aktor**                     | [Nama Aktor]                    |
| **Kondisi Awal**              | [Lokasi/Status Aktor di Sistem] |
| **Hasil yang Diharapkan**     | [Target Akhir]                  |
| **Skenario**                  |                                 |
| **Aksi Aktor**                | **Reaksi Sistem**               |
| 1. [Langkah Aktor]            |                                 |
|                               | 2. [Respon Sistem]              |
| 3. [Langkah Berikutnya]       |                                 |

---

## Contoh Implementasi

### 8. Analisa Grafik & Statistik Latihan

Use Case Analisa Grafik & Statistik Latihan berfungsi untuk menganalisa grafik dan statistik.

**Daftar Use Case:**

| No. Use Case | Nama Use Case                  | Deskripsi                                                                                  |
| :----------- | :----------------------------- | :----------------------------------------------------------------------------------------- |
| UC-08.1      | Lihat Grafik & Statistik Atlet | Berfungsi untuk menampilkan grafik performa (kecepatan, HR, dll) berdasarkan data latihan. |

**Skenario:**

| UC-08.1                   |                                                   |
| :------------------------ | :------------------------------------------------ |
| **Aktor**                 | Atlet, Pelatih, Manajemen                         |
| **Kondisi Awal**          | Aktor berada pada halaman dashboard sistem        |
| **Hasil yang Diharapkan** | Aktor dapat melihat grafik performa secara visual |
| **Skenario**              |                                                   |
| **Aksi Aktor**            | **Reaksi Sistem**                                 |
| 1. Klik menu Dashboard    |                                                   |
|                           | 2. Menampilkan widget statistik dan area grafik   |
| 3. Memilih filter waktu   |                                                   |
|                           | 4. Memproses dan menampilkan grafik ApexCharts    |

---

> [!IMPORTANT]
>
> - Gunakan penomoran berurut antara Aksi Aktor dan Reaksi Sistem.
> - Pastikan deskripsi singkat dan padat.
> - Jika hanya ada Use Case Global (tanpa .nomor), jangan buatkan skenario rincinya, cukup catat namanya saja.
