---
trigger: always_on
---

# Skill: Vue.js 3 – Frontend Architecture for Athlete Monitoring System

## Overview

Vue.js 3 digunakan sebagai frontend SPA untuk Sistem Informasi Monitoring Atlet Sepeda. Frontend berkomunikasi dengan backend Laravel 13 melalui REST API yang diamankan menggunakan Laravel Sanctum.

Sistem memiliki tiga role utama:

- Manajemen
- Pelatih
- Atlet

Frontend harus mampu menampilkan dashboard analitik, visualisasi performa atlet, input data latihan, serta notifikasi rekomendasi latihan dari pelatih.

---

# Project Architecture

Gunakan struktur folder berikut agar maintainable dan scalable.

src/

pages/ → halaman utama aplikasi  
components/ → reusable UI component  
layouts/ → layout utama aplikasi  
stores/ → state management Pinia  
services/ → komunikasi API ke Laravel  
composables/ → reusable logic Vue Composition API

Contoh struktur:

src/
pages/
Dashboard.vue
AthleteDashboard.vue
TrainingInput.vue
TrainingHistory.vue

components/
charts/
TrainingChart.vue
HeartRateChart.vue

ui/
StatCard.vue
DataTable.vue

layouts/
MainLayout.vue
AuthLayout.vue

stores/
authStore.js
trainingStore.js
notificationStore.js

services/
api.js
trainingService.js
authService.js

composables/
useTrainingStats.js
usePagination.js

---

# Coding Style

Gunakan Composition API.

Contoh struktur component:

<script setup>

import { ref, onMounted } from 'vue'
import trainingService from '@/services/trainingService'

const trainings = ref([])

onMounted(async () => {
  trainings.value = await trainingService.getTrainings()
})

</script>

---

# State Management

Gunakan Pinia untuk state global seperti:

- user authentication
- data latihan atlet
- notifikasi dari pelatih
- statistik dashboard

Store harus dipisah berdasarkan domain.

Contoh:

stores/
authStore.js
trainingStore.js
notificationStore.js

---

# API Communication

Semua komunikasi API harus melalui folder:

services/

Contoh:

trainingService.js

Semua endpoint Laravel harus diakses melalui service agar:

- mudah di maintain
- mudah diganti endpoint
- menghindari duplikasi code

---

# Role Based Interface

Vue harus mampu membedakan tampilan berdasarkan role user.

Manajemen

- manajemen user
- monitoring keseluruhan

Pelatih

- melihat dashboard atlet
- analisis performa
- memberi rekomendasi latihan

Atlet

- input data latihan
- melihat performa pribadi
- menerima rekomendasi latihan

---

# Data Training Model

Data latihan atlet memiliki field berikut:

- tanggal_latihan
- durasi_latihan
- jarak_latihan
- kecepatan_rata_rata
- kecepatan_maksimum
- detak_jantung_rata_rata
- detak_jantung_maksimum
- cadence_rata_rata
- cadence_maksimum
- kalori_terbakar
- jenis_latihan

Semua data harus diproses agar bisa digunakan untuk:

- statistik mingguan
- statistik bulanan
- grafik performa

---

# Performance Best Practices

Gunakan lazy loading untuk halaman besar.

Contoh:

const Dashboard = () => import('@/pages/Dashboard.vue')

Gunakan computed untuk data turunan.

Gunakan debounce untuk filter atau search.

---

# Dashboard Requirements

Dashboard harus menampilkan:

- total latihan minggu ini
- total jarak tempuh
- grafik performa kecepatan
- grafik detak jantung
- grafik cadence
- histori latihan

Semua grafik akan menggunakan ApexCharts.

---

# Component Principles

Setiap component harus:

- reusable
- memiliki satu tanggung jawab
- tidak terlalu besar

Contoh:

StatCard.vue
TrainingChart.vue
HeartRateChart.vue
CadenceChart.vue

---

# Error Handling

Setiap request API harus memiliki:

- loading state
- error handling
- fallback UI

Contoh:

loading indicator
error alert
empty data state

---

# Security

Token authentication disimpan menggunakan cookie melalui Laravel Sanctum.

Vue hanya menyimpan state user pada Pinia store.

Jangan menyimpan token secara manual di localStorage jika menggunakan Sanctum.
