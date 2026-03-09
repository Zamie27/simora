---
trigger: always_on
---

# Skill: Laravel 12 – Backend Architecture for Athlete Monitoring System

## Overview

Laravel 12 digunakan untuk membangun REST API backend yang melayani aplikasi frontend Vue.js SPA.

Backend bertanggung jawab untuk:

- autentikasi user
- penyimpanan data latihan atlet
- analisis performa
- pengiriman rekomendasi latihan dari pelatih
- manajemen user

Autentikasi menggunakan Laravel Sanctum.

---

# Backend Architecture

Gunakan arsitektur modular.

Struktur utama backend:

app/

Controllers/
Services/
Repositories/
Models/

Tujuannya agar kode tetap terstruktur dan scalable.

---

# Folder Structure

Contoh struktur backend:

app/

Http/Controllers/
AuthController.php
TrainingController.php
DashboardController.php
NotificationController.php

Services/
TrainingService.php
DashboardService.php
NotificationService.php

Repositories/
TrainingRepository.php
UserRepository.php

Models/
User.php
Training.php
Notification.php

---

# API Design

Backend harus menggunakan REST API.

Endpoint utama:

POST /login  
POST /logout

GET /user

GET /trainings  
POST /trainings  
PUT /trainings/{id}  
DELETE /trainings/{id}

GET /dashboard/stats

GET /notifications  
POST /recommendations

---

# Database Models

Model utama sistem:

User  
Training  
Notification  
Recommendation

Relasi utama:

User memiliki banyak Training

Pelatih dapat membuat Recommendation untuk atlet

User memiliki banyak Notification

---

# Training Model Fields

Training harus memiliki field berikut:

user_id
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

# Role Based Access

Gunakan role untuk mengatur akses:

Manajemen  
Pelatih  
Atlet

Contoh aturan:

Atlet

- hanya bisa melihat dan menambah data latihan sendiri

Pelatih

- bisa melihat data latihan atlet
- memberi rekomendasi latihan

Manajemen

- mengelola user sistem

---

# Service Layer

Semua logika bisnis harus berada di service layer.

Contoh:

TrainingService

fungsi:

createTraining  
updateTraining  
deleteTraining  
getAthleteTrainings

DashboardService

fungsi:

calculateWeeklyStats  
calculateAverageSpeed  
calculateHeartRateTrend

---

# Repository Layer

Repository bertanggung jawab untuk akses database.

Tujuan:

memisahkan query dari logika bisnis.

Contoh:

TrainingRepository

fungsi:

getTrainingsByUser  
getWeeklyTrainings  
getMonthlyTrainings

---

# API Response Format

Semua endpoint harus mengembalikan response JSON dengan format yang konsisten.

Contoh success response:

status
message
data

Contoh error response:

status
message
errors

---

# Validation

Gunakan Laravel Form Request untuk validasi.

Contoh:

StoreTrainingRequest
UpdateTrainingRequest

Validasi harus mencakup:

required
numeric
date
min/max value

---

# Dashboard Analytics

Backend harus mampu menghitung statistik seperti:

total latihan per minggu
total jarak tempuh
rata-rata kecepatan
rata-rata detak jantung
rata-rata cadence

Data ini akan dikirim ke frontend untuk ditampilkan dalam grafik.

---

# Notification System

Pelatih dapat mengirim rekomendasi latihan kepada atlet.

Rekomendasi akan disimpan sebagai:

Recommendation

Sistem akan membuat Notification untuk atlet.

Atlet akan melihat notifikasi pada dashboard.

---

# Pagination

Endpoint histori latihan harus menggunakan pagination.

Contoh:

GET /trainings?page=1

Hal ini penting untuk performa aplikasi.

---

# Security

Gunakan Laravel Sanctum untuk autentikasi API.

Endpoint yang memerlukan login harus menggunakan middleware:

auth:sanctum

---

# Clean Controller Principle

Controller harus tetap kecil.

Controller hanya bertugas:

menerima request
memanggil service
mengembalikan response

Semua logika kompleks harus berada di service layer.
