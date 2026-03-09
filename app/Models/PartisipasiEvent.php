<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartisipasiEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'atlet_id',
        'event_id',
        'nomor_dada',
        'hasil_waktu',
        'hasil_peringkat',
        'catatan_hasil',
    ];

    public function atlet()
    {
        return $this->belongsTo(User::class, 'atlet_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}
