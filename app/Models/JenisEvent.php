<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisEvent extends Model
{
    protected $table = 'event_types';

    protected $fillable = [
        'coach_id',
        'name',
        'description',
    ];

    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'event_type_id');
    }
}
