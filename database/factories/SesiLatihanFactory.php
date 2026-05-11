<?php

namespace Database\Factories;

use App\Models\JenisLatihan;
use App\Models\SesiLatihan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SesiLatihan>
 */
class SesiLatihanFactory extends Factory
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
            'exercise_type_id' => JenisLatihan::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'scheduled_date' => fake()->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
            'scheduled_time' => fake()->time('H:i'),
            'repeat_weekly' => fake()->boolean(20),
            'target_distance_km' => fake()->randomFloat(2, 5, 120),
            'target_duration_minutes' => fake()->numberBetween(30, 240),
            'target_avg_speed' => fake()->randomFloat(2, 15, 45),
            'target_rpm' => fake()->numberBetween(70, 110),
        ];
    }
}
