<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bug_reports', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('title');
            $blueprint->text('description');
            $blueprint->string('image_path')->nullable();
            $blueprint->string('reporter_name');
            $blueprint->string('reporter_contact');
            $blueprint->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $blueprint->string('status')->default('pending');
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bug_reports');
    }
};
