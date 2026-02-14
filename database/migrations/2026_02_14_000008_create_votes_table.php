<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('value')->default(1);   // 1 = いいね
            $table->timestamps();

            $table->unique(['user_id', 'quote_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
