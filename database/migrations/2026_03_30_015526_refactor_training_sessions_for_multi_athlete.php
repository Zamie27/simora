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
        Schema::table('training_sessions', function (Blueprint $table) {
            // Remove old relation first
            if (Schema::hasColumn('training_sessions', 'training_plan_id')) {
                $table->dropForeign(['training_plan_id']);
                $table->dropIndex(['training_plan_id', 'scheduled_date']);
                $table->dropColumn('training_plan_id');
            }

            // Add new columns
            $table->foreignId('coach_id')->after('id')->constrained('users')->onDelete('cascade');
            $table->foreignId('exercise_type_id')->after('coach_id')->constrained('exercise_types')->onDelete('cascade');
            $table->boolean('repeat_weekly')->default(false)->after('scheduled_time');
            $table->decimal('target_rpm', 8, 2)->nullable()->after('target_avg_speed');
        });

        // Now safe to drop training_plans
        Schema::dropIfExists('training_plans');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_sessions', function (Blueprint $table) {
            $table->dropForeign(['coach_id']);
            $table->dropForeign(['exercise_type_id']);
            $table->dropColumn(['coach_id', 'exercise_type_id', 'repeat_weekly', 'target_rpm']);
        });
    }
};
