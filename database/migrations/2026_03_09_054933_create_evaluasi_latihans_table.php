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
        Schema::create('evaluasi_latihans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('latihan_id')->constrained('data_latihans')->onDelete('cascade');
            $table->foreignId('pelatih_id')->constrained('users')->onDelete('cascade');
            $table->text('catatan_evaluasi');
            $table->integer('rating_latihan')->nullable()->comment('1-5');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluasi_latihans');
    }
};
