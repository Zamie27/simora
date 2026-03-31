<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $fillable = [
        'coach_id',
        'event_type_id',
        'title',
        'description',
        'location',
        'event_date',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
        ];
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(EventType::class, 'event_type_id');
    }

    /**
     * Get the coach who created the event.
     */
    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    /**
     * Get the athletes participating in the event.
     */
    public function athletes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_user')
            ->using(EventUser::class)
            ->withPivot(['status', 'result', 'notes', 'event_point_id'])
            ->withTimestamps();
    }

    /**
     * Get the participations in the event.
     */
    public function participants(): HasMany
    {
        return $this->hasMany(EventUser::class, 'event_id');
    }
}
