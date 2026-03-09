---
trigger: always_on
---

# Skill: Laravel Breeze – Authentication for Vue SPA using Sanctum

## Overview

Laravel Breeze digunakan sebagai sistem autentikasi untuk Sistem Informasi Monitoring Atlet Sepeda. Sistem menggunakan mode API dengan Laravel Sanctum untuk mengamankan komunikasi antara backend Laravel dan frontend Vue.js SPA.

Frontend akan mengelola sesi login menggunakan cookie-based authentication yang disediakan oleh Sanctum.

---

# Authentication Architecture

Sistem menggunakan arsitektur berikut:

Vue.js SPA (Frontend)
↓
Laravel API
↓
Laravel Sanctum Authentication

Sanctum menggunakan cookie-based authentication sehingga lebih aman dibandingkan menyimpan token manual di localStorage.

---

# Authentication Features

Sistem autentikasi harus mendukung fitur berikut:

- registrasi user
- login
- logout
- mendapatkan data user yang sedang login
- proteksi endpoint API

---

# User Roles

Sistem memiliki tiga role utama:

Manajemen  
Pelatih  
Atlet

Role harus disimpan pada tabel user.

Role digunakan untuk mengontrol akses fitur aplikasi.

---

# Authentication Flow

Alur login:

1. User memasukkan email dan password di frontend.
2. Frontend mengirim request login ke API Laravel.
3. Laravel memverifikasi kredensial user.
4. Sanctum membuat session authentication.
5. Vue frontend dapat mengakses endpoint yang dilindungi.

---

# API Endpoints

Endpoint autentikasi utama:

POST /login  
POST /logout  
GET /user

Endpoint ini digunakan oleh frontend Vue untuk mengelola sesi pengguna.

---

# Protecting Routes

Endpoint yang memerlukan autentikasi harus menggunakan middleware:

auth:sanctum

Contoh endpoint yang dilindungi:

GET /trainings  
POST /trainings  
GET /dashboard/stats

---

# Auth Store Integration

Frontend menggunakan Pinia untuk menyimpan state user.

Auth store akan memanggil endpoint berikut:

login()  
logout()  
fetchUser()

Data user disimpan pada state:

user  
role  
isAuthenticated

---

# Role Based Access Control

Backend harus memverifikasi role user sebelum mengakses fitur tertentu.

Contoh aturan:

Atlet

- hanya dapat mengakses data latihan miliknya sendiri

Pelatih

- dapat melihat data latihan atlet
- dapat memberikan rekomendasi latihan

Manajemen

- dapat mengelola semua user

---

# Security Practices

Gunakan hashing password menggunakan bcrypt.

Gunakan rate limiting pada endpoint login untuk mencegah brute force.

Pastikan endpoint login dan logout menggunakan HTTPS.

---

# Session Management

Logout harus menghapus session authentication.

Setelah logout user tidak dapat mengakses endpoint yang dilindungi.

Frontend harus menghapus state user dari Pinia store.
