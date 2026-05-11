<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoinEvent extends Model
{
    protected $table = 'event_points';

    protected $fillable = [
        'coach_id',
        'name',
    ];

    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_id');
    }
}
