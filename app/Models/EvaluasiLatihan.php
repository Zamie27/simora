<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluasiLatihan extends Model
{
    use HasFactory;

    protected $table = 'evaluasi_latihans';

    protected $fillable = [
        'latihan_id',
        'pelatih_id',
        'catatan_evaluasi',
        'rating_latihan',
    ];

    public function dataLatihan()
    {
        return $this->belongsTo(DataLatihan::class, 'latihan_id');
    }

    public function pelatih()
    {
        return $this->belongsTo(User::class, 'pelatih_id');
    }
}
