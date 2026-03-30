<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('training_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_session_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('athlete_id')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->decimal('avg_speed', 5, 2)->nullable();
            $table->enum('intensity', ['low', 'medium', 'high', 'very_high'])->nullable();
            $table->enum('type', ['endurance', 'interval', 'recovery', 'time_trial'])->nullable();
            $table->text('athlete_notes')->nullable();
            $table->enum('attendance_status', ['present', 'absent', 'late', 'excused'])->default('present');
            $table->enum('completion_status', ['not_started', 'in_progress', 'completed', 'incomplete'])->default('not_started');
            $table->unsignedTinyInteger('coach_rating')->nullable();
            $table->text('coach_evaluation')->nullable();
            $table->text('coach_comments')->nullable();
            $table->timestamps();

            $table->index(['athlete_id', 'date']);
            $table->index('date');
            $table->index(['training_session_id', 'athlete_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_logs');
    }
};
