---
trigger: always_on
---

# Skill: Pinia – State Management for Athlete Monitoring System

## Overview

Pinia digunakan sebagai state management utama pada frontend Vue.js 3 untuk mengelola data global aplikasi dalam Sistem Informasi Monitoring Atlet Sepeda.

Pinia bertanggung jawab untuk mengelola:

- autentikasi user
- data latihan atlet
- statistik performa
- notifikasi rekomendasi dari pelatih

Pinia harus digunakan dengan pendekatan modular berdasarkan domain data agar mudah dikelola dan scalable.

---

# Store Architecture

Struktur store harus dipisah berdasarkan domain.

stores/

authStore.js  
trainingStore.js  
athleteStore.js  
notificationStore.js  
dashboardStore.js

Tujuan pemisahan ini agar state tidak menjadi terlalu kompleks dan tetap mudah di-maintain.

---

# Auth Store

authStore bertanggung jawab untuk:

- menyimpan data user yang sedang login
- menyimpan role user
- mengatur status autentikasi

Data yang disimpan:

user  
role  
isAuthenticated

Auth store digunakan oleh seluruh aplikasi untuk menentukan akses halaman berdasarkan role.

---

# Training Store

trainingStore bertanggung jawab untuk menyimpan dan memproses data latihan atlet.

State utama:

trainings  
loading  
error

Data latihan atlet memiliki struktur berikut:

tanggal_latihan  
durasi_latihan  
jarak_latihan  
kecepatan_rata_rata  
kecepatan_maksimum  
detak_jantung_rata_rata  
detak_jantung_maksimum  
cadence_rata_rata  
cadence_maksimum  
cadence_tertinggi  
kalori_terbakar  
jenis_latihan

Store ini juga harus mampu melakukan:

- fetch data latihan dari API
- menambahkan data latihan baru
- memperbarui data latihan
- menghapus data latihan

---

# Dashboard Store

dashboardStore bertanggung jawab untuk menghitung statistik performa atlet dari data latihan.

Contoh statistik:

- total latihan minggu ini
- total jarak latihan
- rata-rata kecepatan
- rata-rata detak jantung
- rata-rata cadence

Data statistik ini digunakan oleh komponen dashboard.

---

# Computed Statistics

Pinia harus menggunakan getter untuk menghitung statistik dari data latihan.

Contoh statistik yang harus tersedia:

weeklyTrainingCount  
weeklyDistance  
averageSpeed  
averageHeartRate  
averageCadence

Getter ini akan digunakan oleh chart dan statistik dashboard.

---

# Notification Store

notificationStore menyimpan notifikasi sistem seperti:

- rekomendasi latihan dari pelatih
- pemberitahuan program latihan baru
- peringatan performa menurun

State:

notifications  
unreadCount

---

# API Integration

Semua store harus mengambil data melalui service layer.

Jangan memanggil API langsung dari komponen.

Gunakan:

services/trainingService.js  
services/authService.js

Pinia hanya bertugas mengelola state dan memanggil service.

---

# Data Flow

Alur data aplikasi harus mengikuti pola berikut:

API Laravel  
↓  
Service Layer  
↓  
Pinia Store  
↓  
Vue Components

Pola ini menjaga kode tetap bersih dan terstruktur.

---

# Loading State

Setiap store harus memiliki state loading untuk menangani proses request API.

Contoh:

loading = true saat fetch data  
loading = false setelah data diterima

Hal ini penting untuk menampilkan indikator loading di UI.

---

# Error Handling

Store harus menyimpan error jika request API gagal.

State error dapat digunakan untuk menampilkan alert di UI.

---

# Best Practices

Gunakan satu store untuk satu domain data.

Jangan menyimpan terlalu banyak data yang tidak diperlukan.

Gunakan getter untuk data turunan daripada menyimpan data yang sama berkali-kali.

Pisahkan logika bisnis ke dalam store atau composables.
