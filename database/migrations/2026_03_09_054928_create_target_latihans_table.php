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
        Schema::create('target_latihans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('atlet_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('pelatih_id')->constrained('users')->onDelete('cascade');
            $table->enum('jangka_waktu', ['pendek', 'panjang']);
            $table->enum('tipe_target', ['jarak', 'durasi', 'kecepatan', 'event']);
            $table->decimal('target_value', 8, 2)->comment('Satuan tergantung tipe_target');
            $table->text('deskripsi_target')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->enum('status', ['berjalan', 'tercapai', 'gagal'])->default('berjalan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('target_latihans');
    }
};
