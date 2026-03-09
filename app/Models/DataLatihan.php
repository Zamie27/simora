<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataLatihan extends Model
{
    use HasFactory;

    protected $table = 'data_latihans';

    protected $fillable = [
        'atlet_id',
        'jadwal_id',
        'tanggal_latihan',
        'jenis_latihan',
        'jarak_tempuh',
        'durasi_menit',
        'kecepatan_rata_rata',
        'kecepatan_max',
        'elevasi_gain',
        'hr_avg',
        'hr_max',
        'gear_ratio_depan',
        'gear_ratio_belakang',
        'cadence_avg',
        'power_avg',
        'kalori',
        'intensitas_latihan',
        'catatan_atlet',
        'bukti_latihan',
        'status_penyelesaian',
    ];

    protected $casts = [
        'tanggal_latihan' => 'datetime',
    ];

    public function atlet()
    {
        return $this->belongsTo(User::class, 'atlet_id');
    }

    public function jadwalLatihan()
    {
        return $this->belongsTo(JadwalLatihan::class, 'jadwal_id');
    }

    public function evaluasiLatihan()
    {
        return $this->hasOne(EvaluasiLatihan::class, 'latihan_id');
    }
}
