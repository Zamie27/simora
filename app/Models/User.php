<?php

namespace App\Models;

use App\Notifications\CustomResetPassword;
use App\Notifications\CustomVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $date_of_birth
 * @property string|null $gender
 * @property int|null $role_id
 * @property bool $is_verified
 * @property int|null $coach_id
 * @property int|null $category_id
 * @property string|null $avatar
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Role|null $role
 * @property-read User|null $coach
 * @property-read Collection|TrainingLog[] $trainingLogs
 */
#[Fillable(['name', 'email', 'date_of_birth', 'gender', 'password', 'role_id', 'is_verified', 'coach_id', 'category_id', 'avatar'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
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
     * Get the user's avatar URL.
     */
    public function getAvatarAttribute(?string $value): ?string
    {
        if ($value) {
            return $value;
        }

        // Fallback for athletes who have a profile photo path but no avatar URL set
        if ($this->relationLoaded('athleteProfile') && $this->athleteProfile?->profile_photo_path) {
            return "/documents/{$this->id}/profile_photo";
        }

        return null;
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

    public function athleteProfile(): HasOne
    {
        return $this->hasOne(AthleteProfile::class, 'user_id');
    }

    /**
     * Check if the athlete has a valid license.
     */
    public function hasValidLicense(): bool
    {
        return $this->athleteProfile &&
               $this->athleteProfile->license_path &&
               $this->athleteProfile->uci_id &&
               ($this->athleteProfile->license_valid_until === null || $this->athleteProfile->license_valid_until >= now());
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

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new CustomResetPassword($token));
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        // We override this to DO NOTHING by default during registration/store.
        // User must click "Resend" manually from the verification page.
    }

    /**
     * Send the email verification notification manually.
     */
    public function sendManualEmailVerificationNotification(): void
    {
        $this->notify(new CustomVerifyEmail);
    }

    /**
     * Update the user's profile photo and keep it in sync with athlete documents if applicable.
     */
    public function updateProfilePhoto($file): void
    {
        $oldAvatar = $this->avatar;
        $isAthlete = $this->role?->name === 'Atlet';

        if ($isAthlete) {
            $profile = $this->athleteProfile ?? $this->athleteProfile()->create();

            // Delete old secure file
            if ($profile->profile_photo_path) {
                Storage::disk('local')->delete($profile->profile_photo_path);
            }

            // Store new secure file
            $path = $file->store('private_documents/'.$this->id, 'local');
            $profile->update(['profile_photo_path' => $path]);

            // Set avatar column with cache busting timestamp
            $this->avatar = "/documents/{$this->id}/profile_photo?v=".time();

            // Clean up old public avatar if it existed
            if ($oldAvatar && str_contains($oldAvatar, '/storage/avatars')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $oldAvatar));
            }
        } else {
            // Standard avatar handling for other roles
            if ($oldAvatar) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $oldAvatar));
            }

            $path = $file->store('avatars', 'public');
            $this->avatar = '/storage/'.$path;
        }

        $this->save();
    }
}
