---
trigger: always_on
---

# Skill: Vuelidate – Form Validation for Athlete Training System

## Overview

Vuelidate digunakan untuk melakukan validasi form pada frontend Vue.js dalam Sistem Informasi Monitoring Atlet Sepeda. Validasi diperlukan untuk memastikan bahwa data latihan yang dimasukkan atlet memiliki format dan nilai yang benar sebelum dikirim ke backend Laravel API.

Validasi harus dilakukan di frontend untuk meningkatkan pengalaman pengguna, namun backend Laravel tetap harus melakukan validasi ulang untuk keamanan.

---

# Validation Principles

Semua form input yang berhubungan dengan data latihan harus memiliki validasi.

Tujuan validasi:

- memastikan data tidak kosong
- memastikan tipe data benar
- memastikan nilai berada dalam batas yang wajar
- mencegah kesalahan input dari pengguna

---

# Forms That Require Validation

Form yang wajib menggunakan validasi:

Input Data Latihan  
Edit Data Latihan  
Registrasi User  
Login User  
Input Rekomendasi Latihan oleh Pelatih

---

# Training Data Validation Rules

Field data latihan harus memiliki aturan berikut.

tanggal_latihan

- required
- valid date

durasi_latihan

- required
- numeric
- minimum value: 1 menit

jarak_latihan

- required
- numeric
- minimum value: 0.1 km

kecepatan_rata_rata

- required
- numeric

kecepatan_maksimum

- required
- numeric

detak_jantung_rata_rata

- required
- numeric

detak_jantung_maksimum

- required
- numeric

cadence_rata_rata

- required
- numeric

cadence_maksimum

- required
- numeric

kalori_terbakar

- required
- numeric

jenis_latihan

- required
- string

---

# Validation Structure

Validasi harus ditempatkan dalam komponen Vue menggunakan Vuelidate Composition API.

Validasi harus dideklarasikan terpisah dari logika tampilan agar kode tetap bersih dan mudah dipelihara.

---

# Error Messages

Setiap field harus memiliki pesan error yang jelas.

Contoh:

Durasi latihan wajib diisi  
Jarak latihan harus berupa angka  
Tanggal latihan tidak valid

Pesan error harus mudah dipahami oleh atlet.

---

# UI Error Display

Error harus ditampilkan secara langsung pada field input menggunakan komponen Vuetify.

Gunakan indikator visual seperti:

- error message di bawah field
- warna merah pada field input
- icon peringatan

Hal ini membantu pengguna segera mengetahui kesalahan input.

---

# Submit Validation

Form tidak boleh dikirim ke backend jika terdapat error validasi.

Saat user menekan tombol submit:

1. jalankan validasi
2. jika valid → kirim request API
3. jika tidak valid → tampilkan error

---

# Real-time Validation

Validasi dapat dijalankan secara real-time saat user mengisi form untuk memberikan feedback lebih cepat.

Namun validasi berat sebaiknya hanya dijalankan saat submit.

---

# Validation Reusability

Jika terdapat pola validasi yang sering digunakan, buat helper validation agar dapat digunakan kembali.

Contoh:

numericValidation  
dateValidation

Hal ini menjaga konsistensi validasi di seluruh aplikasi.

---

# Backend Validation

Meskipun frontend memiliki validasi, backend Laravel tetap harus memvalidasi semua input menggunakan Form Request.

Hal ini penting untuk keamanan sistem.
