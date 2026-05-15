# Commit Guidelines for AI Agents

Gunakan panduan ini setiap kali selesai melakukan perubahan kode atau saat pengguna meminta ringkasan pekerjaan.

## 1. Verifikasi Perubahan
- Selalu jalankan `git status` untuk memastikan file mana saja yang telah dimodifikasi.
- Pastikan tidak ada file sampah atau file sementara yang ikut terdeteksi.

## 2. Struktur Ringkasan (Bahasa Indonesia)
Berikan ringkasan dalam format poin-poin yang jelas, dikategorikan berdasarkan domain:
- **Frontend**: Apa yang berubah di Vue/CSS/TS.
- **Backend**: Apa yang berubah di Controller/Service/Model/Migration.
- **Lain-lain**: Konfigurasi, README, dll.

## 3. Format Pesan Commit (Conventional Commits)
Berikan usulan pesan commit dalam format **Conventional Commits** yang ringkas dan deskriptif.

### Jenis Commit yang Sering Digunakan:
- `feat:` Untuk fitur baru.
- `fix:` Untuk perbaikan bug atau error (linting, typo, logic).
- `refactor:` Untuk perubahan kode yang tidak mengubah fungsi (merapikan struktur, ganti nama variabel).
- `docs:` Untuk perubahan dokumentasi.
- `style:` Untuk perubahan format/linting tanpa mengubah logika.

### Contoh Format:
`refactor: rename participant relationship to athletes and update type imports`

## 4. Alur Interaksi
1. AI melakukan perubahan.
2. AI menjalankan `git status`.
3. AI memberikan ringkasan perbaikan dalam Bahasa Indonesia.
4. AI memberikan 1-3 opsi pesan commit (Bahasa Inggris/Indonesia) agar pengguna tinggal memilih atau menyalinnya.
