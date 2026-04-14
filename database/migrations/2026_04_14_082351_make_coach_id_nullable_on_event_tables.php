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
        Schema::table('event_types', function (Blueprint $table) {
            $table->foreignId('coach_id')->nullable()->change();
        });

        Schema::table('event_points', function (Blueprint $table) {
            $table->foreignId('coach_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_types', function (Blueprint $table) {
            $table->foreignId('coach_id')->nullable(false)->change();
        });

        Schema::table('event_points', function (Blueprint $table) {
            $table->foreignId('coach_id')->nullable(false)->change();
        });
    }
};
