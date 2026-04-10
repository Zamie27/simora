<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property Carbon $scheduled_date
 */
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
     * Local Scopes
     */
    public function scopeUpcoming(Builder $query): void
    {
        $query->where(function ($q) {
            $q->where(function ($sub) {
                $sub->where('repeat_weekly', false)
                    ->whereDate('scheduled_date', '>=', now()->toDateString());
            })->orWhere('repeat_weekly', true);
        })->orderBy('scheduled_date', 'asc');
    }

    public function scopePast(Builder $query): void
    {
        $query->where('repeat_weekly', false)
            ->whereDate('scheduled_date', '<', now()->toDateString())
            ->orderBy('scheduled_date', 'desc');
    }

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
     * Helpers
     */
    public function getActiveInstanceDate(): CarbonInterface
    {
        if (! $this->repeat_weekly) {
            return $this->scheduled_date->startOfDay();
        }

        $now = now()->startOfDay();
        $targetDay = $this->scheduled_date->dayOfWeek;
        $currentDay = $now->dayOfWeek;

        if ($currentDay === $targetDay) {
            return $now;
        }

        // Carbon's next() or previous() can be used here.
        // If today is Tuesday (2) and target is Monday (1).
        // next(1) will give next week's Monday.

        return $now->copy()->next($targetDay);
    }
}
