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
        Schema::create('training_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_plan_id')->constrained()->cascadeOnDelete();
            $table->date('scheduled_date');
            $table->time('scheduled_time')->nullable();
            $table->enum('type', ['endurance', 'interval', 'recovery', 'time_trial']);
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('target_distance_km', 8, 2)->nullable();
            $table->unsignedInteger('target_duration_minutes')->nullable();
            $table->decimal('target_avg_speed', 5, 2)->nullable();
            $table->timestamps();

            $table->index('scheduled_date');
            $table->index(['training_plan_id', 'scheduled_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_sessions');
    }
};
