<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalLatihan extends Model
{
    use HasFactory;

    protected $fillable = [
        'atlet_id',
        'pelatih_id',
        'tanggal_jadwal',
        'jenis_latihan',
        'deskripsi',
        'status_kehadiran',
    ];

    protected $casts = [
        'tanggal_jadwal' => 'datetime',
    ];

    public function atlet()
    {
        return $this->belongsTo(User::class, 'atlet_id');
    }

    public function pelatih()
    {
        return $this->belongsTo(User::class, 'pelatih_id');
    }

    public function dataLatihan()
    {
        return $this->hasOne(DataLatihan::class, 'jadwal_id');
    }
}
