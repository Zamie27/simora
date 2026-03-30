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
        Schema::create('training_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coach_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('athlete_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['short_term', 'long_term']);
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('target_distance_km', 8, 2)->nullable();
            $table->unsignedInteger('target_duration_minutes')->nullable();
            $table->decimal('target_avg_speed', 5, 2)->nullable();
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->timestamps();

            $table->index(['coach_id', 'status']);
            $table->index(['athlete_id', 'status']);
            $table->index('start_date');
            $table->index('end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_plans');
    }
};
