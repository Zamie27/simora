# Standar Penulisan Skenario Use Case SIMORA

Dokumen ini berisi panduan dan standar penulisan skenario use case untuk proyek SIMORA, berdasarkan format yang telah ditentukan dalam dokumen SRS dan referensi visual.

## Struktur Dasar Skenario

Setiap skenario use case harus memiliki komponen berikut untuk menjaga kejelasan alur:

1. **ID Use Case**: Kode unik use case (Contoh: UC-11.1).
2. **Aktor**: Nama aktor yang terlibat (Manajemen, Pelatih, Atlet). (dapat di isi lebih dari 1 aktor menyesuaikan dengan kebutuhan use case)
3. **Kondisi Awal**: Keadaan sistem atau prasyarat sebelum use case dimulai.
4. **Hasil yang Diharapkan**: Kondisi sistem setelah use case selesai dengan sukses.
5. **Tabel Skenario**:
    - **Aksi Aktor**: Langkah manual yang dilakukan oleh pengguna di antarmuka.
    - **Reaksi Sistem**: Respon otomatis, pengolahan data, atau perubahan tampilan oleh sistem.
6. **Alur Alternatif** (Opsional): Deskripsi langkah jika terjadi penyimpangan dari alur utama (misal: pembatalan atau error).

## Template Penulisan (.md)

Gunakan format tabel berikut dalam setiap file skenario:

```markdown
### [ID Use Case] - [Nama Use Case]

| Elemen                    | Deskripsi                               |
| :------------------------ | :-------------------------------------- |
| **Aktor**                 | [Nama Aktor]                            |
| **Kondisi Awal**          | [Kondisi Awal / Lokasi Aktor di Sistem] |
| **Hasil yang Diharapkan** | [Kondisi Akhir yang Diinginkan]         |

**Skenario:**

| No  | Aksi Aktor                                      | Reaksi Sistem                                   |
| :-- | :---------------------------------------------- | :---------------------------------------------- |
| 1   | Klik menu [Nama Menu]                           |                                                 |
| 2   |                                                 | Menampilkan [Halaman/Daftar Data]               |
| 3   | [Langkah Berikutnya, misal: Klik Tombol Tambah] |                                                 |
| 4   |                                                 | [Respon Sistem, misal: Menampilkan Form]        |
| 5   | Memasukkan data [Nama Field]                    |                                                 |
| 6   | Klik Tombol [Simpan/Tambah/Kirim]               |                                                 |
| 7   |                                                 | [Proses Backend, misal: Menyimpan ke Basisdata] |

**Alur Alternatif:**

- **Langkah No [X]**: Jika [Kondisi], maka [Tindakan Sistem]
```

## Ketentuan Penamaan & Istilah

1. **Aksi Aktor (Kata Kerja Aktif)**:
    - **Klik**: Untuk menekan tombol atau menu.
    - **Memilih**: Untuk memilih item dari daftar atau dropdown.
    - **Memasukkan**: Untuk mengisi data ke dalam form.
    - **Mengunjungi**: Untuk membuka URL atau halaman pertama kali.

2. **Reaksi Sistem (Kata Kerja Deskriptif)**:
    - **Menampilkan**: Untuk perubahan UI/Halaman.
    - **Menyimpan**: Untuk operasi _Insert_ ke database.
    - **Menghapus**: Untuk operasi _Delete_ dari database.
    - **Memperbarui**: Untuk operasi _Update_ data.
    - **Mengarahkan**: Untuk redirect halaman.

3. **Penomoran Berurutan**: Nomor langkah harus berlanjut secara bergantian antara kolom Aksi Aktor dan Reaksi Sistem untuk menunjukkan urutan waktu yang jelas.

## Contoh Implementasi (Berdasarkan SIMORA)

### UC-11.2 - Lihat Detail Atlet

| Elemen                    | Deskripsi                                                  |
| :------------------------ | :--------------------------------------------------------- |
| **Aktor**                 | Manajemen                                                  |
| **Kondisi Awal**          | Aktor berada pada halaman backend sistem                   |
| **Hasil yang Diharapkan** | Aktor dapat melihat detail informasi atlet secara mendalam |

**Skenario:**

| No  | Aksi Aktor                       | Reaksi Sistem                                                                   |
| :-- | :------------------------------- | :------------------------------------------------------------------------------ |
| 1   | Klik menu Ringkasan Daftar Atlet |                                                                                 |
| 2   |                                  | Menampilkan Daftar Data Atlet                                                   |
| 3   | Memilih salah satu Data Atlet    |                                                                                 |
| 4   | Klik Tombol Detail               |                                                                                 |
| 5   |                                  | Menampilkan detail informasi atlet (Biodata, Metrik Fisik, dan Grafik Performa) |

---

> [!NOTE]
> Pastikan setiap aksi penghapusan data selalu menyertakan langkah **Konfirmasi** (Reaksi Sistem menampilkan pesan konfirmasi) sebelum data benar-benar dihapus dari basisdata.
