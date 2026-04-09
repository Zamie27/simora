<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'training_session_id',
        'athlete_id',
        'title',
        'date',
        'distance_km',
        'duration_minutes',
        'avg_speed',
        'rpm',
        'avg_heart_rate',
        'calories',
        'elevation_m',
        'temperature_c',
        'pace_per_km',
        'hr_zone',
        'trimp',
        'vo2_max',
        'avg_watt_power',
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
        'avg_heart_rate' => 'integer',
        'rpm' => 'decimal:2',
        'calories' => 'integer',
        'elevation_m' => 'integer',
        'temperature_c' => 'decimal:1',
        'trimp' => 'integer',
        'vo2_max' => 'decimal:1',
        'avg_watt_power' => 'integer',
    ];

    protected $appends = [
        'is_editable',
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

    public function exerciseType(): BelongsTo
    {
        return $this->belongsTo(ExerciseType::class, 'exercise_type_id');
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

    public function getIsEditableAttribute(): bool
    {
        $date = $this->date;
        if (! $date) {
            return false;
        }

        if (is_string($date)) {
            try {
                $date = Carbon::parse($date);
            } catch (\Exception $e) {
                return false;
            }
        }

        if ($date instanceof Carbon || $date instanceof \DateTimeInterface) {
            return Carbon::instance($date)->isToday();
        }

        return false;
    }

    /**
     * Local Scopes
     */
    public function scopeForAthlete(Builder $query, int $athleteId): void
    {
        $query->where(fn ($q) => $q->where('athlete_id', $athleteId));
    }

    public function scopeForPeriod(Builder $query, string $startDate, string $endDate): void
    {
        $query->whereBetween('date', [$startDate, $endDate]);
    }
}
