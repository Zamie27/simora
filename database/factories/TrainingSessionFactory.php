<?php

namespace Database\Factories;

use App\Models\TrainingSession;
use App\Models\User;
use App\Models\ExerciseType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrainingSession>
 */
class TrainingSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'coach_id' => User::factory(),
            'exercise_type_id' => ExerciseType::factory(),
            'scheduled_date' => fake()->dateTimeBetween('-1 week', '+2 weeks'),
            'scheduled_time' => fake()->time('H:i'),
            'type' => fake()->randomElement(['endurance', 'interval', 'recovery', 'time_trial']),
            'title' => fake()->randomElement([
                'Ride Pagi - Endurance',
                'Interval Sprint',
                'Recovery Ride',
                'Time Trial Practice',
                'Long Distance Ride',
                'Hill Climbing Session',
                'Tempo Ride',
                'Cadence Drill',
            ]),
            'description' => fake()->sentence(),
            'target_distance_km' => fake()->randomFloat(2, 10, 100),
            'target_duration_minutes' => fake()->numberBetween(30, 180),
            'target_avg_speed' => fake()->randomFloat(2, 22, 38),
        ];
    }
}
