---
description:
---

# Antigravity AI Development Workflow

AI harus mengikuti workflow ini saat membantu pengembangan proyek.

---

# Step 1: Understand Feature

Sebelum menghasilkan kode, AI harus memahami:

fitur apa yang akan dibuat  
data apa yang dibutuhkan  
bagaimana fitur berinteraksi dengan sistem lain

AI harus memastikan fitur sesuai dengan arsitektur sistem.

---

# Step 2: Design First

Sebelum menulis kode, AI harus menjelaskan:

struktur folder  
komponen yang dibutuhkan  
API endpoint yang diperlukan  
store yang digunakan

AI harus melakukan perencanaan singkat sebelum coding.

---

# Step 3: Backend First

Untuk fitur yang membutuhkan data baru:

1. buat database structure
2. buat model
3. buat repository
4. buat service
5. buat controller
6. buat API endpoint

Backend harus selesai sebelum frontend dibuat.

---

# Step 4: API Integration

Setelah backend siap:

frontend harus membuat service untuk memanggil API.

Jangan memanggil API langsung dari komponen.

Gunakan service layer.

---

# Step 5: State Management

Data dari API harus disimpan dalam Pinia store.

Store bertanggung jawab untuk:

fetch data  
update state  
expose getter

Komponen hanya mengambil data dari store.

---

# Step 6: UI Implementation

Setelah state tersedia:

buat komponen UI menggunakan Vuetify.

Komponen harus kecil dan reusable.

---

# Step 7: Data Visualization

Jika fitur membutuhkan analitik:

gunakan ApexCharts.

Grafik harus menampilkan tren data dari waktu ke waktu.

---

# Step 8: Validation

Semua form harus menggunakan Vuelidate.

Validasi harus dilakukan sebelum request API dikirim.

---

# Step 9: Testing

AI harus memastikan:

API bekerja dengan benar  
form validasi berjalan  
UI menampilkan data dengan benar

---

# Step 10: Code Refinement

Sebelum selesai, AI harus:

memastikan kode modular  
menghapus kode duplikat  
memastikan struktur project rapi
