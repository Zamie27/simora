---
trigger: always_on
---

# Skill: Laravel Excel – Export Training Data

## Overview

Maatwebsite Laravel Excel digunakan untuk mengekspor data latihan atlet ke dalam format spreadsheet seperti Excel.

Fitur ini berguna untuk:

- laporan performa atlet
- analisis latihan oleh pelatih
- dokumentasi latihan

Export harus mendukung format Excel (.xlsx).

---

# Export Use Cases

Sistem harus mampu mengekspor:

- riwayat latihan atlet
- statistik latihan
- laporan latihan bulanan
- laporan performa atlet

Pelatih dan manajemen dapat mengunduh laporan ini untuk analisis lebih lanjut.

---

# Export Architecture

Export harus menggunakan class khusus.

Contoh struktur:

app/Exports/

TrainingExport.php  
AthletePerformanceExport.php

Setiap export class bertanggung jawab untuk membangun dataset Excel.

---

# Training Export Fields

Export data latihan harus mencakup field berikut:

Tanggal Latihan  
Durasi Latihan  
Jarak Latihan  
Kecepatan Rata-rata  
Kecepatan Maksimum  
Detak Jantung Rata-rata  
Detak Jantung Maksimum  
Cadence Rata-rata  
Cadence Maksimum  
Kalori Terbakar  
Jenis Latihan

---

# Export Controller

Endpoint API untuk export harus disediakan.

Contoh:

GET /export/trainings/{athlete_id}

Endpoint ini menghasilkan file Excel yang dapat diunduh.

---

# Filtering Export

Export harus mendukung filter agar data yang diunduh lebih relevan.

Contoh filter:

tanggal mulai  
tanggal akhir  
jenis latihan

Hal ini memungkinkan pelatih mengekspor laporan spesifik.

---

# Performance Considerations

Jika dataset sangat besar gunakan chunking.

Hal ini mencegah penggunaan memori yang berlebihan saat menghasilkan file Excel.

---

# Column Formatting

Kolom Excel harus memiliki format yang jelas dan mudah dibaca.

Contoh header:

Tanggal  
Durasi (menit)  
Jarak (km)  
Kecepatan Rata-rata (km/h)  
Detak Jantung Rata-rata (bpm)

---

# File Naming

Nama file export harus informatif.

Contoh:

training-report-athlete-2026.xlsx

Atau

training-report-march-2026.xlsx

---

# Access Control

Tidak semua user boleh melakukan export.

Aturan akses:

Pelatih → dapat export data atlet  
Manajemen → dapat export semua data  
Atlet → hanya dapat export data latihannya sendiri

---

# API Integration

Frontend Vue akan memanggil endpoint export dan menerima file download.

Request harus dikirim dengan autentikasi Sanctum.

File Excel akan langsung diunduh oleh browser.
