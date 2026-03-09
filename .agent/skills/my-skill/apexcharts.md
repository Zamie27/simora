---
trigger: always_on
---

# Skill: ApexCharts – Data Visualization for Athlete Monitoring Dashboard

## Overview

ApexCharts digunakan untuk memvisualisasikan data performa atlet sepeda dalam bentuk grafik yang mudah dipahami oleh pelatih dan atlet.

Grafik digunakan untuk menganalisis tren latihan dan perkembangan performa atlet dari waktu ke waktu.

Visualisasi harus fokus pada keterbacaan data dan kemudahan analisis.

---

# Chart Types

Dashboard harus menggunakan beberapa jenis grafik berikut.

Line Chart

Digunakan untuk menampilkan tren performa dari waktu ke waktu seperti:

kecepatan rata-rata  
detak jantung rata-rata  
cadence rata-rata

Bar Chart

Digunakan untuk membandingkan statistik latihan seperti:

total jarak per minggu  
total durasi latihan

Area Chart

Digunakan untuk melihat tren intensitas latihan.

---

# Data Sources

Data grafik berasal dari Pinia store.

chart components tidak boleh memanggil API secara langsung.

Data harus diproses terlebih dahulu oleh store atau composable.

---

# Performance Metrics Visualization

Grafik yang harus tersedia di dashboard pelatih dan atlet:

1. Grafik Kecepatan
   Menampilkan kecepatan rata-rata per sesi latihan.

2. Grafik Detak Jantung
   Menampilkan detak jantung rata-rata selama latihan.

3. Grafik Cadence
   Menampilkan cadence rata-rata setiap latihan.

4. Grafik Jarak Latihan
   Menampilkan jarak tempuh tiap latihan.

5. Grafik Kalori Terbakar
   Menampilkan jumlah kalori yang terbakar per latihan.

---

# Time Series Data

Semua grafik performa harus berbasis waktu.

Sumbu X:

tanggal latihan

Sumbu Y:

metrik performa

---

# Chart Components

Buat komponen grafik reusable.

components/charts/

SpeedChart.vue  
HeartRateChart.vue  
CadenceChart.vue  
DistanceChart.vue  
CaloriesChart.vue

Setiap chart harus menerima data melalui props.

---

# Chart Data Processing

Data mentah dari backend perlu diproses menjadi format ApexCharts.

Contoh:

series  
categories

Series berisi nilai performa.

Categories berisi tanggal latihan.

---

# Athlete Performance Analysis

Grafik harus membantu pelatih memahami performa atlet seperti:

apakah kecepatan meningkat  
apakah detak jantung terlalu tinggi  
apakah cadence stabil

Grafik harus membantu mendeteksi tren performa.

---

# Chart Responsiveness

Grafik harus responsif dan tetap terbaca di berbagai ukuran layar.

Gunakan fitur responsive ApexCharts untuk mengatur ukuran chart.

---

# Visual Clarity

Gunakan warna berbeda untuk setiap metrik performa.

Contoh:

biru → kecepatan  
merah → detak jantung  
hijau → cadence  
kuning → kalori

Hal ini membantu pelatih membaca grafik dengan cepat.

---

# Chart Performance

Jangan merender grafik dengan data yang terlalu besar.

Batasi data misalnya:

30 hari terakhir  
12 minggu terakhir

Jika data sangat banyak gunakan filter tanggal.

---

# Chart Interaction

Grafik harus memiliki fitur interaktif:

tooltip  
zoom  
hover detail

Hal ini membantu pelatih melihat detail performa pada tanggal tertentu.

---

# Dashboard Integration

Grafik harus ditempatkan dalam layout dashboard dengan struktur berikut:

Statistik ringkas di bagian atas

Grafik performa di bagian tengah

Riwayat latihan di bagian bawah

Urutan ini memudahkan pengguna memahami data secara bertahap.
