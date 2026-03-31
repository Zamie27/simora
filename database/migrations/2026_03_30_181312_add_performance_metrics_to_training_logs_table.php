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
        Schema::table('training_logs', function (Blueprint $table) {
            $table->unsignedInteger('avg_heart_rate')->nullable()->after('avg_speed');
            $table->unsignedInteger('calories')->nullable()->after('rpm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_logs', function (Blueprint $table) {
            $table->dropColumn(['avg_heart_rate', 'calories']);
        });
    }
};
