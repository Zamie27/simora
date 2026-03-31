<?php

namespace Database\Factories;

use App\Models\TrainingPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrainingPlan>
 */
class TrainingPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-1 month', '+1 month');
        $endDate = fake()->dateTimeBetween($startDate, '+3 months');

        return [
            'coach_id' => User::factory(),
            'athlete_id' => User::factory(),
            'title' => fake()->randomElement([
                'Program Endurance Bulanan',
                'Latihan Interval Mingguan',
                'Recovery Week',
                'Program Time Trial',
                'Persiapan Kompetisi',
                'Base Training Phase',
            ]),
            'description' => fake()->paragraph(),
            'type' => fake()->randomElement(['short_term', 'long_term']),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'target_distance_km' => fake()->randomFloat(2, 50, 500),
            'target_duration_minutes' => fake()->numberBetween(60, 600),
            'target_avg_speed' => fake()->randomFloat(2, 20, 40),
            'status' => 'active',
        ];
    }

    /**
     * Short-term plan state.
     */
    public function shortTerm(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'short_term',
        ]);
    }

    /**
     * Long-term plan state.
     */
    public function longTerm(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'long_term',
        ]);
    }

    /**
     * Completed plan state.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }
}
