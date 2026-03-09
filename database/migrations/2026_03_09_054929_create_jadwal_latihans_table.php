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
        Schema::create('jadwal_latihans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('atlet_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('pelatih_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('tanggal_jadwal');
            $table->enum('jenis_latihan', ['endurance', 'interval', 'recovery', 'time_trial']);
            $table->text('deskripsi')->nullable();
            $table->enum('status_kehadiran', ['belum_mulai', 'hadir', 'absen'])->default('belum_mulai');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_latihans');
    }
};
