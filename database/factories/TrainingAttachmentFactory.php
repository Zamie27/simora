<?php

namespace Database\Factories;

use App\Models\TrainingAttachment;
use App\Models\TrainingLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrainingAttachment>
 */
class TrainingAttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'training_log_id' => TrainingLog::factory(),
            'file_path' => 'training-attachments/'.fake()->uuid().'.jpg',
            'file_name' => fake()->word().'.jpg',
            'file_type' => 'image/jpeg',
            'file_size' => fake()->numberBetween(100000, 5000000),
        ];
    }
}
