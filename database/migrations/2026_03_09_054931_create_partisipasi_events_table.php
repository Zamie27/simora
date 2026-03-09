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
        Schema::create('partisipasi_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('atlet_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->string('nomor_dada')->nullable();
            $table->time('hasil_waktu')->nullable();
            $table->integer('hasil_peringkat')->nullable();
            $table->text('catatan_hasil')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partisipasi_events');
    }
};
