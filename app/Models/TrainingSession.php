<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'coach_id',
        'exercise_type_id',
        'title',
        'description',
        'scheduled_date',
        'scheduled_time',
        'repeat_weekly',
        'target_distance_km',
        'target_duration_minutes',
        'target_avg_speed',
        'target_rpm',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'repeat_weekly' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    public function exerciseType(): BelongsTo
    {
        return $this->belongsTo(ExerciseType::class);
    }

    public function athletes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'training_session_user');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(TrainingLog::class);
    }

    /**
     * Local Scopes
     */
    public function scopeUpcoming(Builder $query): void
    {
        $query->where(fn ($q) => $q->whereDate('scheduled_date', '>=', now()->toDateString()))
            ->orderBy('scheduled_date', 'asc');
    }

    public function scopePast(Builder $query): void
    {
        $query->where(fn ($q) => $q->whereDate('scheduled_date', '<', now()->toDateString()))
            ->orderBy('scheduled_date', 'desc');
    }
}
