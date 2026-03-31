# Project Context

## Project Name

Athlete Monitoring System – Roadbike Training Dashboard

## Project Overview

Athlete Monitoring System adalah sistem informasi berbasis web yang digunakan untuk memonitor dan menganalisis performa latihan atlet sepeda roadbike.

Sistem ini membantu atlet, pelatih, dan manajemen tim dalam melihat perkembangan performa latihan secara terstruktur melalui dashboard analitik, grafik performa, dan laporan latihan.

Aplikasi berbentuk **Single Page Application (SPA)** yang menggunakan backend REST API.

---

# Main Goals

Tujuan utama sistem ini adalah:

* memonitor performa latihan atlet
* membantu pelatih menganalisis data latihan
* menyediakan dashboard visual untuk perkembangan atlet
* menyimpan histori latihan secara terstruktur
* memberikan rekomendasi latihan dari pelatih

Sistem ini berfungsi sebagai **alat analisis performa atlet berbasis data**.

---

# Target Users

Sistem memiliki tiga jenis pengguna utama.

### Atlet

Atlet menggunakan sistem untuk:

* memasukkan data latihan
* melihat histori latihan
* melihat grafik performa pribadi
* menerima rekomendasi latihan dari pelatih

### Pelatih

Pelatih menggunakan sistem untuk:

* melihat data latihan atlet
* menganalisis performa atlet
* membandingkan performa antar atlet
* memberikan rekomendasi latihan

### Manajemen

Manajemen menggunakan sistem untuk:

* memonitor seluruh atlet
* melihat statistik performa tim
* mengelola user sistem

---

# Core Features

Fitur utama sistem meliputi:

### Training Data Management

Atlet dapat memasukkan data latihan seperti:

* tanggal latihan
* durasi latihan
* jarak tempuh
* kecepatan rata-rata
* kecepatan maksimum
* detak jantung rata-rata
* detak jantung maksimum
* cadence rata-rata
* cadence maksimum
* kalori terbakar
* jenis latihan

### Performance Dashboard

Dashboard menampilkan statistik performa seperti:

* total latihan mingguan
* total jarak latihan
* rata-rata kecepatan
* rata-rata detak jantung
* rata-rata cadence

### Performance Charts

Grafik digunakan untuk memvisualisasikan tren performa atlet:

* grafik kecepatan
* grafik detak jantung
* grafik cadence
* grafik jarak latihan
* grafik kalori terbakar

### Training History

Sistem menyimpan seluruh histori latihan atlet yang dapat difilter berdasarkan:

* tanggal
* jenis latihan
* periode waktu

### Coach Recommendation

Pelatih dapat memberikan rekomendasi latihan kepada atlet berdasarkan performa latihan.

Rekomendasi akan muncul sebagai notifikasi pada dashboard atlet.

### Data Export

Data latihan dapat diekspor dalam format Excel untuk kebutuhan analisis lebih lanjut.

---

# Technology Stack

Frontend:

* Vue.js 3
* Vuetify
* Pinia
* ApexCharts
* Vuelidate

Backend:

* Laravel 12
* PHP 8.4
* Laravel Sanctum
* Laravel Excel

Database:

* MySQL / MariaDB

---

# Application Architecture

Aplikasi menggunakan arsitektur berikut:

Frontend (Vue SPA)
↓
Service Layer (API communication)
↓
Laravel REST API
↓
Database

Frontend berkomunikasi dengan backend menggunakan REST API yang diamankan oleh Laravel Sanctum.

---

# Frontend Architecture

Frontend dibangun menggunakan Vue 3 dengan struktur modular.

Struktur utama frontend:

src/

pages/ → halaman aplikasi
components/ → reusable components
layouts/ → layout utama
stores/ → state management Pinia
services/ → komunikasi API
composables/ → reusable logic

State global dikelola menggunakan Pinia.

Visualisasi data menggunakan ApexCharts.

UI dibangun menggunakan Vuetify dan Sneat Dashboard Template.

---

# Backend Architecture

Backend menggunakan Laravel 12 dengan arsitektur modular.

Struktur utama backend:

Controllers
Services
Repositories
Models

Controller hanya menangani request dan response.

Logika bisnis ditempatkan pada service layer.

Akses database dilakukan melalui repository layer.

---

# Security

Sistem menggunakan Laravel Sanctum untuk autentikasi.

Metode autentikasi:

cookie-based authentication.

Endpoint API yang memerlukan login menggunakan middleware:

auth:sanctum

Role user digunakan untuk mengontrol akses fitur aplikasi.

---

# Data Model

Model utama sistem:

User
Training
Recommendation
Notification

Relasi utama:

User memiliki banyak Training.

Pelatih dapat membuat Recommendation untuk atlet.

User dapat menerima Notification.

---

# Performance Considerations

Sistem harus mempertimbangkan performa karena data latihan dapat bertambah besar seiring waktu.

Beberapa strategi yang digunakan:

* pagination untuk histori latihan
* filtering berdasarkan tanggal
* pembatasan dataset pada grafik
* query database yang efisien

---

# Design Principles

Sistem harus memiliki karakteristik berikut:

* data-driven dashboard
* visualisasi performa yang jelas
* UI yang responsif
* kode yang modular dan maintainable

Dashboard harus memudahkan pelatih memahami perkembangan performa atlet dengan cepat melalui grafik dan statistik.
