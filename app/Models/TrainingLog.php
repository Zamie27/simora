<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingLog extends Model
{
    protected $fillable = [
        'training_session_id',
        'athlete_id',
        'date',
        'distance_km',
        'duration_minutes',
        'avg_speed',
        'rpm',
        'intensity',
        'type',
        'athlete_notes',
        'attendance_status',
        'completion_status',
        'coach_rating',
        'coach_evaluation',
        'coach_comments',
    ];

    protected $casts = [
        'date' => 'date',
        'distance_km' => 'decimal:2',
        'avg_speed' => 'decimal:2',
        'rpm' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(TrainingSession::class, 'training_session_id');
    }

    public function athlete(): BelongsTo
    {
        return $this->belongsTo(User::class, 'athlete_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TrainingAttachment::class);
    }

    /**
     * Accessors
     */
    public function getCalculatedAvgSpeedAttribute(): float
    {
        if ($this->distance_km && $this->duration_minutes && $this->duration_minutes > 0) {
            return round(($this->distance_km / $this->duration_minutes) * 60, 2);
        }

        return $this->avg_speed ?? 0;
    }

    /**
     * Local Scopes
     */
    public function scopeForAthlete(Builder $query, int $athleteId): void
    {
        $query->where('athlete_id', $athleteId);
    }

    public function scopeForPeriod(Builder $query, string $startDate, string $endDate): void
    {
        $query->whereBetween('date', [$startDate, $endDate]);
    }
}
