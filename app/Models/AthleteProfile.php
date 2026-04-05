<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AthleteProfile extends Model
{
    protected $fillable = [
        'user_id',
        'profile_photo_path',
        'birth_certificate_path',
        'family_card_path',
        'id_card_path',
        'license_path',
        'uci_id',
        'license_valid_until',
    ];

    protected $casts = [
        'license_valid_until' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
