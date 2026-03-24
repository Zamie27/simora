---
trigger: always_on
---

# Skill: PHP 8.4 – Backend Coding Standard for Athlete Monitoring System

## Overview

PHP 8.4 digunakan sebagai bahasa utama backend untuk Sistem Informasi Monitoring Atlet Sepeda yang dibangun menggunakan Laravel 13.

Kode harus mengikuti prinsip:

- clean code
- modular
- readable
- maintainable

PHP digunakan untuk membangun REST API yang akan dikonsumsi oleh frontend Vue.js.

---

# Coding Standards

Gunakan standar PSR:

PSR-12 coding style  
PSR-4 autoloading

Nama class harus menggunakan PascalCase.

Contoh:

TrainingController
AthleteService
NotificationService

Nama variabel menggunakan camelCase.

Contoh:

trainingData
averageSpeed
heartRateMax

---

# Strong Typing

Gunakan type declaration sebanyak mungkin.

Contoh:

function calculateAverageSpeed(array $trainings): float
{
}

Return type harus selalu didefinisikan jika memungkinkan.

---

# Data Structures

Gunakan array associative untuk struktur data yang dikirim ke frontend.

Contoh data latihan:

tanggal_latihan
durasi_latihan
jarak_latihan
kecepatan_rata_rata
kecepatan_maksimum
detak_jantung_rata_rata
detak_jantung_maksimum
cadence_rata_rata
cadence_maksimum
kalori_terbakar
jenis_latihan

---

# Error Handling

Gunakan exception handling.

Contoh:

try
catch

Jangan mengembalikan error secara manual tanpa struktur.

Gunakan response JSON yang konsisten.

---

# Response Structure

Semua API harus menggunakan format response yang konsisten.

success response:

status
message
data

error response:

status
message
errors

---

# Performance Practices

Hindari query berulang.

Gunakan eager loading untuk relasi database.

Gunakan pagination untuk data besar seperti histori latihan.

---

# Security

Gunakan validasi input sebelum memproses data.

Sanitasi input dari request.

Gunakan Laravel Sanctum untuk autentikasi API.

---

# Business Logic Separation

Jangan menaruh logika bisnis besar di controller.

Logika harus ditempatkan di:

service layer

Controller hanya bertugas:

- menerima request
- memanggil service
- mengembalikan response
