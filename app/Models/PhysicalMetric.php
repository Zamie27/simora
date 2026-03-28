<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhysicalMetric extends Model
{
    protected $fillable = [
        'user_id',
        'height',
        'weight',
        'age',
        'category',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'height' => 'decimal:2',
        'weight' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
