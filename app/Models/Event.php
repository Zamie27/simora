<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_event',
        'tanggal_mulai',
        'tanggal_selesai',
        'kategori_event',
        'lokasi',
        'deskripsi',
    ];

    public function partisipasiEvents()
    {
        return $this->hasMany(PartisipasiEvent::class, 'event_id');
    }
}
