---
trigger: always_on
---

# Skill: Sneat Vuetify – Admin Dashboard Template

## Overview

Sneat Vuetify Admin Template digunakan sebagai dasar tampilan dashboard dalam Sistem Informasi Monitoring Atlet Sepeda. Template ini memberikan struktur UI yang modern, responsif, dan konsisten untuk aplikasi berbasis dashboard.

Sneat harus digunakan sebagai fondasi layout dan komponen UI, namun tetap mengikuti arsitektur Vue yang modular.

---

# Dashboard Layout Structure

Aplikasi harus menggunakan layout utama berbasis dashboard.

Struktur layout utama:

Sidebar Navigation  
Top Navigation Bar  
Main Content Area  
Footer

Layout ini harus didefinisikan dalam folder:

layouts/MainLayout.vue

Semua halaman dashboard menggunakan layout ini.

---

# Sidebar Navigation

Sidebar berisi menu navigasi utama aplikasi.

Menu harus disesuaikan dengan role user.

Menu untuk Atlet:

Dashboard  
Input Latihan  
Riwayat Latihan  
Grafik Performa  
Notifikasi

Menu untuk Pelatih:

Dashboard  
Daftar Atlet  
Analisis Performa  
Rekomendasi Latihan

Menu untuk Manajemen:

Dashboard  
Manajemen User  
Monitoring Atlet

---

# Dashboard Page Structure

Setiap halaman dashboard harus mengikuti struktur berikut.

Header halaman  
Statistik ringkas  
Grafik performa  
Data tabel

Urutan ini memudahkan pengguna memahami data secara bertahap.

---

# UI Component Organization

Semua komponen UI harus ditempatkan pada folder berikut.

components/ui/

StatCard.vue  
AppTable.vue  
PageHeader.vue  
ConfirmDialog.vue

Komponen ini harus reusable di seluruh halaman aplikasi.

---

# Chart Placement

Grafik performa atlet harus ditempatkan pada bagian tengah dashboard menggunakan layout grid Vuetify.

Contoh grafik:

Grafik kecepatan  
Grafik detak jantung  
Grafik cadence  
Grafik jarak latihan

Grafik harus menggunakan ApexCharts.

---

# Data Tables

Riwayat latihan dan daftar atlet harus ditampilkan menggunakan tabel.

Fitur tabel harus mencakup:

pencarian  
pagination  
sorting

Hal ini penting agar data mudah diakses ketika jumlah data besar.

---

# Notification UI

Sistem harus menampilkan notifikasi pada dashboard.

Contoh notifikasi:

rekomendasi latihan dari pelatih  
peringatan performa menurun  
pemberitahuan latihan baru

Gunakan komponen badge atau snackbar dari Vuetify.

---

# UI Consistency

Gunakan warna dan tipografi yang konsisten di seluruh aplikasi.

Warna utama:

primary → navigasi  
secondary → statistik  
success → performa baik  
warning → performa menurun

Konsistensi visual penting untuk meningkatkan pengalaman pengguna.

---

# Responsive Design

Dashboard harus tetap nyaman digunakan pada berbagai ukuran layar.

Gunakan grid system Vuetify agar layout dapat menyesuaikan perangkat:

desktop  
tablet  
mobile

---

# Page Modularity

Setiap halaman harus dipisahkan dalam folder pages.

Contoh:

pages/Dashboard.vue  
pages/TrainingInput.vue  
pages/TrainingHistory.vue  
pages/AthleteAnalysis.vue

Hal ini memudahkan pengembangan dan pemeliharaan sistem di masa depan.

---

# UI Performance

Hindari komponen yang terlalu kompleks dalam satu halaman.

Pisahkan fitur besar menjadi beberapa komponen kecil agar rendering lebih efisien.

Hal ini penting terutama pada halaman dashboard dengan banyak grafik dan tabel.
