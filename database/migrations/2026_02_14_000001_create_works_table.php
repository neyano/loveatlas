<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('works', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('title_original')->nullable();
            $table->string('type', 20);                // movie, anime, drama, novel, game, other
            $table->smallInteger('year')->unsigned()->nullable();
            $table->string('country', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('poster_path')->nullable();
            $table->string('external_url', 500)->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_approved')->default(false);
            $table->timestamps();

            $table->index('type');
            $table->index('is_approved');
            $table->fullText(['title', 'title_original']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('works');
    }
};
