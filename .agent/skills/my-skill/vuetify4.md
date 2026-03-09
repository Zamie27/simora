---
trigger: always_on
---

# Skill: Vuetify 4 – UI Framework for Athlete Monitoring Dashboard

## Overview

Vuetify 4 digunakan sebagai UI framework utama untuk membangun dashboard sistem monitoring atlet sepeda. Framework ini digunakan untuk membuat tampilan modern, konsisten, dan responsif.

Vuetify harus digunakan secara modular agar komponen UI dapat digunakan kembali.

---

# Design Principles

Dashboard harus memiliki desain:

- clean
- data-driven
- responsive
- mudah dibaca oleh pelatih

Prioritas tampilan:

1. Statistik performa
2. Grafik latihan
3. Riwayat latihan
4. Notifikasi pelatih

---

# Layout System

Gunakan layout berbasis Vuetify.

layouts/

MainLayout.vue

Struktur dasar:

AppBar
Navigation Drawer
Main Content
Footer

Navigation Drawer berisi menu:

Dashboard  
Data Latihan  
Riwayat Latihan  
Analisis Performa  
Rekomendasi Pelatih  
Manajemen User

---

# Dashboard UI Components

Gunakan komponen berikut untuk dashboard.

Statistik:

v-card
v-row
v-col

Data tabel:

v-data-table

Form input latihan:

v-form
v-text-field
v-select
v-date-picker

Dialog:

v-dialog

---

# Dashboard Statistic Cards

Gunakan kartu statistik untuk menampilkan data penting seperti:

- total latihan minggu ini
- total jarak latihan
- rata-rata kecepatan
- rata-rata detak jantung

Gunakan:

v-card
v-icon
v-progress-linear

---

# Responsive Design

Gunakan grid system Vuetify.

v-container  
v-row  
v-col

Pastikan dashboard tetap nyaman digunakan di:

desktop
tablet
mobile

---

# Data Tables

Gunakan v-data-table untuk:

- riwayat latihan
- daftar atlet
- daftar rekomendasi latihan

Fitur yang wajib ada:

search
pagination
sorting

---

# Form Input Latihan Atlet

Form input latihan harus memuat field berikut:

tanggal latihan  
durasi latihan  
jarak latihan  
kecepatan rata-rata  
kecepatan maksimum  
detak jantung rata-rata  
detak jantung maksimum  
cadence rata-rata  
cadence maksimum  
kalori terbakar  
jenis latihan

Gunakan validasi melalui Vuelidate.

---

# Notification UI

Gunakan komponen berikut untuk notifikasi:

v-snackbar
v-alert
v-badge

Contoh penggunaan:

notifikasi rekomendasi latihan dari pelatih ke atlet.

---

# Theme System

Gunakan Vuetify theme system.

Tema harus mendukung:

light mode
dark mode

Warna utama dashboard:

primary → navigasi  
secondary → statistik  
success → performa baik  
warning → performa menurun

---

# UI Reusability

Semua komponen UI harus reusable.

components/ui/

StatCard.vue
AppTable.vue
FormInput.vue
ConfirmDialog.vue

Tujuannya agar tampilan konsisten di seluruh aplikasi.

---

# UX Principles

Dashboard harus memudahkan pelatih membaca performa atlet.

Gunakan:

chart visual
warna indikator performa
ringkasan statistik

Pelatih harus bisa langsung memahami:

apakah performa atlet meningkat atau menurun.

---

# Accessibility

Pastikan UI memiliki:

- label yang jelas
- kontras warna yang baik
- ukuran teks yang mudah dibaca

Dashboard digunakan untuk analisis data sehingga keterbacaan sangat penting.
