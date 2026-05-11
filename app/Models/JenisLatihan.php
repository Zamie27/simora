<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisLatihan extends Model
{
    protected $table = 'exercise_types';

    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function sessions(): HasMany
    {
        return $this->hasMany(SesiLatihan::class, 'exercise_type_id');
    }
}
