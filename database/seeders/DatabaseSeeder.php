<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Buat Akun Manajer (Admin)
        User::create([
            'name' => 'Manajer SIMORA',
            'email' => 'manajer@simora.test',
            'password' => bcrypt('password'),
            'role' => 'manajer',
            'is_active' => true,
        ]);

        // Buat Akun Pelatih
        $pelatih1 = User::create([
            'name' => 'Pelatih Budi',
            'email' => 'pelatih1@simora.test',
            'password' => bcrypt('password'),
            'role' => 'pelatih',
            'is_active' => true,
        ]);

        $pelatih2 = User::create([
            'name' => 'Pelatih Anton',
            'email' => 'pelatih2@simora.test',
            'password' => bcrypt('password'),
            'role' => 'pelatih',
            'is_active' => true,
        ]);

        // Buat Akun Atlet 1
        $atlet1 = User::create([
            'name' => 'Atlet Dimas',
            'email' => 'atlet1@simora.test',
            'password' => bcrypt('password'),
            'role' => 'atlet',
            'is_active' => true,
        ]);

        // Buat Akun Atlet 2
        $atlet2 = User::create([
            'name' => 'Atlet Reza',
            'email' => 'atlet2@simora.test',
            'password' => bcrypt('password'),
            'role' => 'atlet',
            'is_active' => true,
        ]);

        // Relasikan Pelatih & Atlet (Pivot table)
        $pelatih1->atlet()->attach($atlet1->id);
        $pelatih1->atlet()->attach($atlet2->id);

        // Buat Profil Atlet
        \App\Models\AtletProfile::create([
            'user_id' => $atlet1->id,
            'tinggi_badan' => 170,
            'berat_badan' => 65.5,
            'tanggal_lahir' => '2000-05-15',
            'jenis_kelamin' => 'L',
            'kategori' => 'Men Elite',
            'bmi' => 65.5 / ((170/100) * (170/100)),
        ]);

        \App\Models\AtletProfile::create([
            'user_id' => $atlet2->id,
            'tinggi_badan' => 175,
            'berat_badan' => 70.2,
            'tanggal_lahir' => '1998-10-20',
            'jenis_kelamin' => 'L',
            'kategori' => 'Men U23',
            'bmi' => 70.2 / ((175/100) * (175/100)),
        ]);

        // Opsional: Bikin Data Dummy Latihan untuk Atlet 1
        $jadwal1 = \App\Models\JadwalLatihan::create([
            'atlet_id' => $atlet1->id,
            'pelatih_id' => $pelatih1->id,
            'tanggal_jadwal' => now()->subDays(2),
            'jenis_latihan' => 'endurance',
            'deskripsi' => 'Latihan ketahanan 100km',
            'status_kehadiran' => 'hadir',
        ]);

        \App\Models\DataLatihan::create([
            'atlet_id' => $atlet1->id,
            'jadwal_id' => $jadwal1->id,
            'tanggal_latihan' => now()->subDays(2),
            'jenis_latihan' => 'endurance',
            'jarak_tempuh' => 102.5, // km
            'durasi_menit' => 185, // 3 jam 5 menit
            'kecepatan_rata_rata' => 33.2,
            'kecepatan_max' => 52.4,
            'elevasi_gain' => 450,
            'hr_avg' => 145,
            'hr_max' => 178,
            'gear_ratio_depan' => 52,
            'gear_ratio_belakang' => 15,
            'cadence_avg' => 88,
            'power_avg' => 180,
            'kalori' => 2400,
            'intensitas_latihan' => 'sedang',
            'catatan_atlet' => 'Cuaca sedikit berangin, tapi cukup konstan.',
            'status_penyelesaian' => 'selesai',
        ]);
    }
}
