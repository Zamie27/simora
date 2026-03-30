<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhysicalMetric extends Model
{
    use HasFactory;

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

    /**
     * Get the BMI value.
     */
    public function getBmiAttribute(): ?float
    {
        if ($this->height <= 0) {
            return null;
        }

        $heightInMeters = $this->height / 100;

        return round($this->weight / ($heightInMeters * $heightInMeters), 1);
    }

    /**
     * Get the BMI status based on gender and common Indonesian (Kemenkes) standards.
     */
    public function getBmiStatusAttribute(): string
    {
        $bmi = $this->bmi;

        if ($bmi === null) {
            return 'N/A';
        }

        $gender = $this->user->gender;

        // Using Kemenkes RI Standard for Adults
        // Note: For athletes/children it might vary, but this is the general baseline requested.
        if ($bmi < 18.5) {
            return 'Kurus (Underweight)';
        }

        // Slight gender-aware logic if needed, but standardizing on Kemenkes for now
        // Male Normal: 18.5 - 25.0, Female Normal: 18.5 - 25.0
        if ($bmi >= 18.5 && $bmi <= 25.0) {
            return 'Normal';
        }

        if ($bmi > 25.0 && $bmi <= 27.0) {
            return 'Gemuk (Overweight)';
        }

        return 'Obesitas (Obese)';
    }

    protected $appends = ['bmi', 'bmi_status'];
}
