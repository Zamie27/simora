<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function atletProfile()
    {
        return $this->hasOne(AtletProfile::class, 'user_id');
    }

    public function pelatih()
    {
        return $this->belongsToMany(User::class, 'pelatih_atlet', 'atlet_id', 'pelatih_id')
            ->withPivot('status')->withTimestamps();
    }

    public function atlet()
    {
        return $this->belongsToMany(User::class, 'pelatih_atlet', 'pelatih_id', 'atlet_id')
            ->withPivot('status')->withTimestamps();
    }

    public function targetLatihans()
    {
        return $this->hasMany(TargetLatihan::class, 'atlet_id');
    }

    public function jadwalLatihans()
    {
        return $this->hasMany(JadwalLatihan::class, 'atlet_id');
    }

    public function dataLatihans()
    {
        return $this->hasMany(DataLatihan::class, 'atlet_id');
    }

    public function partisipasiEvents()
    {
        return $this->hasMany(PartisipasiEvent::class, 'atlet_id');
    }
}
