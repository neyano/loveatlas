<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->date('visited_at');
            $table->text('note')->nullable();
            $table->string('photo_path')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();  // 1-5
            $table->timestamps();

            $table->unique(['user_id', 'location_id', 'visited_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
