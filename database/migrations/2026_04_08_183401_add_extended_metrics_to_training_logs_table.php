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
            $table->unsignedInteger('elevation_m')->nullable()->after('distance_km');
            $table->decimal('temperature_c', 4, 1)->nullable()->after('elevation_m');
            $table->string('pace_per_km', 10)->nullable()->after('avg_speed');
            $table->string('hr_zone', 20)->nullable()->after('avg_heart_rate');
            $table->unsignedInteger('trimp')->nullable()->after('hr_zone');
            $table->decimal('vo2_max', 4, 1)->nullable()->after('trimp');
            $table->unsignedInteger('avg_watt_power')->nullable()->after('vo2_max');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_logs', function (Blueprint $table) {
            $table->dropColumn([
                'elevation_m',
                'temperature_c',
                'pace_per_km',
                'hr_zone',
                'trimp',
                'vo2_max',
                'avg_watt_power',
            ]);
        });
    }
};
