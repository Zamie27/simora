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
        Schema::create('athlete_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('profile_photo_path')->nullable();
            $table->string('birth_certificate_path')->nullable();
            $table->string('family_card_path')->nullable();
            $table->string('id_card_path')->nullable();
            $table->string('license_path')->nullable();
            $table->string('uci_id')->nullable()->unique();
            $table->date('license_valid_until')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('athlete_profiles');
    }
};
