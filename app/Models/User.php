<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'date_of_birth', 'gender', 'password', 'role_id', 'is_verified', 'coach_id', 'category_id'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

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
            'is_verified' => 'boolean',
            'date_of_birth' => 'date',
        ];
    }

    /**
     * Get the user's age.
     */
    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth?->age;
    }

    /**
     * Get the role associated with the user.
     *
     * @return BelongsTo<Role, $this>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the coach associated with the athlete.
     */
    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    /**
     * Get the athletes coached by this user.
     */
    public function athletes(): HasMany
    {
        return $this->hasMany(User::class, 'coach_id');
    }

    public function physicalMetrics(): HasMany
    {
        return $this->hasMany(PhysicalMetric::class, 'user_id');
    }

    public function latestPhysicalMetric(): HasOne
    {
        return $this->hasOne(PhysicalMetric::class, 'user_id')->latestOfMany('recorded_at');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the training sessions assigned to the coach.
     */
    public function coachedSessions(): HasMany
    {
        return $this->hasMany(TrainingSession::class, 'coach_id');
    }

    /**
     * Get the training sessions assigned to the athlete.
     */
    public function athleteSessions(): BelongsToMany
    {
        return $this->belongsToMany(TrainingSession::class, 'training_session_user');
    }

    /**
     * Get training logs for this user as an athlete.
     */
    public function trainingLogs(): HasMany
    {
        return $this->hasMany(TrainingLog::class, 'athlete_id');
    }

    public function scopeWhereRole($query, string $role)
    {
        return $query->whereHas('role', function ($q) use ($role) {
            $q->where('name', $role);
        });
    }

    /**
     * Get the events created by this coach.
     */
    public function coachedEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'coach_id');
    }

    /**
     * Get the events assigned to this athlete.
     */
    public function athleteEvents(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_user')
            ->using(EventUser::class)
            ->withPivot(['status', 'result', 'notes', 'event_point_id'])
            ->withTimestamps();
    }

    /**
     * Get the event participations for this user.
     */
    public function eventParticipations(): HasMany
    {
        return $this->hasMany(EventUser::class, 'user_id');
    }
}
