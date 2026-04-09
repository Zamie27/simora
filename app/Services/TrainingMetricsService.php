<?php

namespace App\Services;

use App\Models\PhysicalMetric;

class TrainingMetricsService
{
    /**
     * Calculate all estimated training metrics based on inputs and physical profile.
     */
    public function calculateMetrics(array $inputs, ?PhysicalMetric $physicalMetric): array
    {
        $durationMinutes = (float) ($inputs['duration_minutes'] ?? 0);
        $distanceKm = (float) ($inputs['distance_km'] ?? 0);
        $avgHr = (int) ($inputs['avg_heart_rate'] ?? 0);
        $elevation = (int) ($inputs['elevation_m'] ?? 0);

        $durationHours = $durationMinutes / 60;

        // Basic fallback values for calculations if PhysicalMetric is absent
        // We use typical values for an adult male cyclist
        $weightKg = $physicalMetric?->weight ?? 70;
        $age = $physicalMetric?->age ?? $physicalMetric?->user?->age ?? 30; // Uses DB stored age or DOB accessor
        $maxHr = 220 - $age;
        $restingHr = 60; // Assumed resting HR

        // 1. Calculate Avg Speed (km/h)
        $avgSpeed = null;
        if ($durationHours > 0) {
            $avgSpeed = round($distanceKm / $durationHours, 2);
        }

        // 2. Calculate Pace (min/km)
        $pacePerKm = null;
        if ($distanceKm > 0) {
            $paceMinutes = $durationMinutes / $distanceKm;
            $minutes = floor($paceMinutes);
            $seconds = round(($paceMinutes - $minutes) * 60);
            $pacePerKm = sprintf('%d:%02d', $minutes, $seconds);
        }

        // 3. Calculate Calories
        $calories = null;
        if ($avgHr > 0 && $durationMinutes > 0) {
            // Formula for men: Calories = [ (0.2017 x Age) - (0.09036 x Weight) + (0.6309 x HR) - 55.0969 ] x Time / 4.184
            // Simplified general formula if gender is unknown or combined:
            $cal = ((0.2017 * $age) - (0.09036 * $weightKg) + (0.6309 * $avgHr) - 55.0969) * $durationMinutes / 4.184;
            $calories = $cal > 0 ? (int) round($cal) : (int) round($distanceKm * $weightKg * 0.5); // Fallback to basic if formula yields negative
        } elseif ($distanceKm > 0) {
            // General cycling calories formula without HR (approx 30 kcal per mile per 100 lbs or just METs-based)
            // A common cycling rule of thumb is ~30-40 kcal per km depending on speed, but Distance * Weight multiplier is simpler.
            // Let's use distance (km) * roughly 0.5 to 0.7 * weight (kg)
            $speedFactor = $avgSpeed ? ($avgSpeed / 20) : 1;
            $calories = (int) round($distanceKm * $weightKg * 0.4 * $speedFactor);
        }

        // 4. Calculate HR Zone
        $hrZone = null;
        if ($avgHr > 0) {
            $percentMax = $avgHr / $maxHr;
            if ($percentMax < 0.6) {
                $hrZone = 'Zone 1 (Recovery)';
            } elseif ($percentMax < 0.7) {
                $hrZone = 'Zone 2 (Endurance)';
            } elseif ($percentMax < 0.8) {
                $hrZone = 'Zone 3 (Tempo)';
            } elseif ($percentMax < 0.9) {
                $hrZone = 'Zone 4 (Threshold)';
            } else {
                $hrZone = 'Zone 5 (Maxmimal)';
            }
        }

        // 5. Calculate TRIMP (Banister's TRIMP)
        $trimp = null;
        if ($avgHr > 0 && $durationMinutes > 0) {
            // HRR = (HR - HRrest) / (HRmax - HRrest)
            $hrr = ($avgHr - $restingHr) / max(1, ($maxHr - $restingHr));
            if ($hrr > 0) {
                // e^(1.92 * hrr) for men, 1.67 for women. We use 1.92 as general.
                $trimpVal = $durationMinutes * $hrr * exp(1.92 * $hrr);
                $trimp = (int) round($trimpVal);
            }
        }

        // 6. Calculate VO2 Max (Estimated from HR and Speed)
        $vo2Max = null;
        if ($avgHr > 0 && $avgSpeed > 0 && $avgSpeed > 15) {
            // Very rough formula: Ratio of Max HR to Resting HR * 15 (Uth-Sorensen-Overgaard-Pedersen equation)
            // Or based on steady state HR at a given speed. We will just use the HRmax/HRrest formula as an indicator.
            $vo2Max = round(15.3 * ($maxHr / max(1, $restingHr)), 1);
        }

        // 7. Calculate AVG Watt Power (Estimated)
        $avgWattPower = null;
        if ($distanceKm > 0 && $durationMinutes > 0) {
            $speedMs = ($distanceKm * 1000) / ($durationMinutes * 60);

            // Simplified physical model:
            // Power = (Cr * m * g * v) + (0.5 * rho * CdA * v^3) + (m * g * grade * v)
            $cr = 0.005; // Rolling resistance coefficient
            $g = 9.81; // Gravity
            $rho = 1.225; // Air density
            $cda = 0.32; // Drag area (typical road bike drops)
            $grade = ($distanceKm > 0) ? ($elevation / ($distanceKm * 1000)) : 0;

            // Added 10kg for the bike
            $totalMass = $weightKg + 10;

            $powerRolling = $cr * $totalMass * $g * $speedMs;
            $powerAero = 0.5 * $rho * $cda * pow($speedMs, 3);
            $powerGravity = $totalMass * $g * $grade * $speedMs;

            $totalPowerWatts = $powerRolling + $powerAero + $powerGravity;

            // Account for drivetrain efficiency (~95-97%)
            $totalPowerWatts = $totalPowerWatts / 0.96;

            if ($totalPowerWatts > 0) {
                $avgWattPower = (int) round($totalPowerWatts);
            }
        }

        return [
            'avg_speed' => $avgSpeed,
            'pace_per_km' => $pacePerKm,
            'calories' => $calories,
            'hr_zone' => $hrZone,
            'trimp' => $trimp,
            'vo2_max' => $vo2Max,
            'avg_watt_power' => $avgWattPower,
        ];
    }
}
