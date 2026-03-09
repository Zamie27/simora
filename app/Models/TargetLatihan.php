<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TargetLatihan extends Model
{
    use HasFactory;

    protected $fillable = [
        'atlet_id',
        'pelatih_id',
        'jangka_waktu',
        'tipe_target',
        'target_value',
        'deskripsi_target',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
    ];

    public function atlet()
    {
        return $this->belongsTo(User::class, 'atlet_id');
    }

    public function pelatih()
    {
        return $this->belongsTo(User::class, 'pelatih_id');
    }
}
