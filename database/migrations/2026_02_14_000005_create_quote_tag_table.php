<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_tag', function (Blueprint $table) {
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();

            $table->primary(['quote_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_tag');
    }
};
