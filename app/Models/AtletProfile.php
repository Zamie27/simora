<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtletProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tinggi_badan',
        'berat_badan',
        'tanggal_lahir',
        'jenis_kelamin',
        'kategori',
        'bmi',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
