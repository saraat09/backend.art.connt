<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')
                  ->constrained('reviews')
                  ->cascadeOnDelete();
            $table->foreignId('artisan_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();

            $table->unique('review_id'); // 1 réponse par avis max
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_replies');
    }
};
