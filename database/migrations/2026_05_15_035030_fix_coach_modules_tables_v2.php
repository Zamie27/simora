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
        // Fix training_sessions
        Schema::table('training_sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('training_sessions', 'location')) {
                $table->string('location')->nullable()->after('scheduled_time');
            }
            $table->string('type')->nullable()->change();
        });

        // Fix event_types
        Schema::table('event_types', function (Blueprint $table) {
            $table->foreignId('coach_id')->nullable()->change();
            if (! Schema::hasColumn('event_types', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
        });

        // Fix event_points
        Schema::table('event_points', function (Blueprint $table) {
            $table->foreignId('coach_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op for now to avoid rollback issues with foreign keys
    }
};
