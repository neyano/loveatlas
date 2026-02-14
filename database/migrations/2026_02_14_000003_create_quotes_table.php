<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->text('quote_text');
            $table->string('character_name', 200)->nullable();
            $table->text('scene_description')->nullable();
            $table->string('episode_info', 100)->nullable();
            $table->string('language', 10)->default('ja');
            $table->string('photo_path')->nullable();
            $table->string('status', 20)->default('pending');   // pending, approved, rejected
            $table->unsignedInteger('likes_count')->default(0);
            $table->timestamps();

            $table->index('status');
            $table->index('likes_count');
            $table->fullText(['quote_text', 'character_name', 'scene_description']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
