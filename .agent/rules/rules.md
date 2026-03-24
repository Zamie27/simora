---
trigger: always_on
---

# Antigravity AI Global Rules

Project: Sistem Informasi Monitoring Atlet Sepeda

## Project Overview

Proyek ini adalah Sistem Informasi Monitoring Atlet Sepeda berbasis web yang digunakan untuk:

- mencatat data latihan atlet
- menganalisis performa atlet
- memberikan rekomendasi latihan oleh pelatih
- menampilkan dashboard analitik performa atlet

Arsitektur sistem:

Frontend:
Vue.js 3
Vuetify 4
Pinia
ApexCharts
Vuelidate
Sneat Admin Template

Backend:
PHP 8.4
Laravel 13
Laravel Sanctum
Laravel Breeze
Laravel Excel

Frontend menggunakan Vue SPA yang berkomunikasi dengan Laravel API.

---

# Core Development Principles

Semua kode yang dihasilkan harus mengikuti prinsip berikut:

Clean Code  
Modular Architecture  
Separation of Concerns  
Maintainability  
Scalability

Jangan membuat kode monolitik atau sulit dirawat.

---

# Architecture Rules

Frontend dan backend harus dipisahkan.

Frontend:
Vue SPA

Backend:
Laravel REST API

Semua komunikasi menggunakan JSON API.

---

# Backend Rules

Backend Laravel harus mengikuti arsitektur berikut:

Controllers  
Services  
Repositories  
Models

Controller hanya menerima request dan mengembalikan response.

Semua logika bisnis harus berada pada service layer.

Query database harus berada pada repository layer.

---

# API Design Rules

Semua API harus mengikuti standar REST.

Contoh endpoint:

GET /trainings  
POST /trainings  
PUT /trainings/{id}  
DELETE /trainings/{id}

Response API harus berbentuk JSON.

Format response:

success response

status  
message  
data

error response

status  
message  
errors

---

# Authentication Rules

Sistem autentikasi menggunakan Laravel Sanctum.

Frontend Vue menggunakan cookie-based authentication.

Semua endpoint penting harus dilindungi dengan middleware:

auth:sanctum

---

# Role Based Access

Sistem memiliki tiga role:

Manajemen  
Pelatih  
Atlet

Atlet:

- menginput data latihan
- melihat grafik performa

Pelatih:

- melihat data latihan atlet
- menganalisis performa atlet
- memberikan rekomendasi latihan

Manajemen:

- mengelola user

---

# Frontend Architecture

Frontend harus mengikuti struktur berikut:

pages/
components/
layouts/
stores/
services/
composables/

Jangan menaruh logika bisnis langsung di komponen UI.

---

# State Management

State global harus menggunakan Pinia.

Store dipisah berdasarkan domain:

authStore  
trainingStore  
dashboardStore  
notificationStore

---

# Data Visualization

Grafik performa atlet harus menggunakan ApexCharts.

Grafik utama:

kecepatan rata-rata  
detak jantung rata-rata  
cadence  
jarak latihan  
kalori terbakar

Semua grafik harus berbasis waktu (tanggal latihan).

---

# Form Validation

Semua form harus menggunakan Vuelidate.

Form yang wajib divalidasi:

input latihan  
edit latihan  
login  
registrasi  
rekomendasi pelatih

---

# Dashboard UI Rules

Gunakan komponen Vuetify dari Sneat template.

Layout dashboard:

Sidebar navigation  
Topbar  
Content area

Dashboard harus menampilkan:

statistik ringkas  
grafik performa  
riwayat latihan

---

# Code Quality Rules

Semua kode harus:

mudah dibaca  
memiliki struktur jelas  
menggunakan nama variabel yang deskriptif

Hindari:

kode duplikat  
fungsi terlalu panjang  
komponen terlalu besar

---

# Security Rules

Semua input harus divalidasi.

Backend harus memvalidasi ulang semua request dari frontend.

Gunakan:

Form Request Validation di Laravel

---

# Performance Rules

Gunakan pagination untuk data besar seperti riwayat latihan.

Gunakan eager loading untuk relasi database.

Hindari query berulang.

---

# Development Goal

Tujuan utama sistem ini adalah menghasilkan dashboard monitoring atlet yang dapat:

menampilkan performa latihan secara visual  
membantu pelatih menganalisis performa atlet  
membantu atlet mengevaluasi latihan mereka

---

# Docker Execution Rules

Karena proyek ini berjalan di dalam environment Docker, semua eksekusi command line (CLI) yang berkaitan dengan backend atau node HARUS dijalankan di dalam container `simora-app` (sesuai konfigurasi `docker-compose.yml`).

Gunakan prefix `docker exec -it simora-app` untuk mengeksekusi command:

- **Artisan**: `docker exec -it simora-app php artisan <perintah>`
- **Composer**: `docker exec -it simora-app composer <perintah>`
- **NPM**: `docker exec -it simora-app npm <perintah>`

Contoh:

- `docker exec -it simora-app php artisan migrate`
- `docker exec -it simora-app php artisan make:controller`
- `docker exec -it simora-app composer install`
- `docker exec -it simora-app npm install`
- `docker exec -it simora-app npm run dev`

Jangan menjalankan perintah `php`, `artisan`, `composer`, atau `npm` secara langsung di sistem host.
