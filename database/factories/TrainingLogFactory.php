<?php

namespace Database\Factories;

use App\Models\TrainingLog;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrainingLog>
 */
class TrainingLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $distanceKm = fake()->randomFloat(2, 5, 120);
        $durationMinutes = fake()->numberBetween(15, 300);
        $avgSpeed = $durationMinutes > 0 ? round($distanceKm / ($durationMinutes / 60), 2) : null;

        return [
            'training_session_id' => TrainingSession::factory(),
            'athlete_id' => User::factory(),
            'date' => fake()->dateTimeBetween('-1 month', 'now'),
            'distance_km' => $distanceKm,
            'duration_minutes' => $durationMinutes,
            'avg_speed' => $avgSpeed,
            'intensity' => fake()->randomElement(['low', 'medium', 'high', 'very_high']),
            'type' => fake()->randomElement(['endurance', 'interval', 'recovery', 'time_trial']),
            'athlete_notes' => fake()->optional(0.7)->sentence(),
            'attendance_status' => 'present',
            'completion_status' => fake()->randomElement(['completed', 'in_progress', 'incomplete']),
            'coach_rating' => null,
            'coach_evaluation' => null,
            'coach_comments' => null,
        ];
    }

    /**
     * Log with complete coach evaluation.
     */
    public function evaluated(): static
    {
        return $this->state(fn (array $attributes) => [
            'coach_rating' => fake()->numberBetween(1, 10),
            'coach_evaluation' => fake()->paragraph(),
            'coach_comments' => fake()->sentence(),
            'completion_status' => 'completed',
        ]);
    }

    /**
     * Standalone log (not tied to a session).
     */
    public function standalone(): static
    {
        return $this->state(fn (array $attributes) => [
            'training_session_id' => null,
        ]);
    }
}
