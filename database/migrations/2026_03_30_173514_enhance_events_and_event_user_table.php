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
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('event_type_id')->nullable()->after('coach_id')->constrained('event_types')->onDelete('set null');
        });

        Schema::table('event_user', function (Blueprint $table) {
            $table->foreignId('event_point_id')->nullable()->after('user_id')->constrained('event_points')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
