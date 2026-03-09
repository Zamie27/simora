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
        Schema::create('data_latihans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('atlet_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('jadwal_id')->nullable()->constrained('jadwal_latihans')->onDelete('set null');
            $table->dateTime('tanggal_latihan');
            $table->enum('jenis_latihan', ['endurance', 'interval', 'recovery', 'time_trial']);
            $table->decimal('jarak_tempuh', 8, 2)->comment('km');
            $table->integer('durasi_menit');
            $table->decimal('kecepatan_rata_rata', 5, 2)->comment('km/h');
            $table->decimal('kecepatan_max', 5, 2)->nullable()->comment('km/h');
            $table->integer('elevasi_gain')->nullable()->comment('meter');
            $table->integer('hr_avg')->nullable()->comment('bpm');
            $table->integer('hr_max')->nullable()->comment('bpm');
            $table->integer('gear_ratio_depan')->nullable()->comment('chainring');
            $table->integer('gear_ratio_belakang')->nullable()->comment('cassette');
            $table->integer('cadence_avg')->nullable()->comment('rpm');
            $table->integer('power_avg')->nullable()->comment('watt');
            $table->integer('kalori')->nullable()->comment('kcal');
            $table->enum('intensitas_latihan', ['ringan', 'sedang', 'berat', 'sangat_berat']);
            $table->text('catatan_atlet')->nullable();
            $table->string('bukti_latihan')->nullable();
            $table->enum('status_penyelesaian', ['selesai', 'tidak_selesai'])->default('selesai');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_latihans');
    }
};
