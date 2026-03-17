<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function down(): void
    {
        Schema::dropIfExists('puzzle_progress');
    }

    public function up(): void
    {
        Schema::create('puzzle_progress', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('puzzle_id')->constrained()->cascadeOnDelete();

            $table->json('guesses')->nullable();
            $table->json('solved_category_ids')->nullable();
            $table->json('current_word_order')->nullable();
            $table->integer('mistakes_remaining')->nullable();
            $table->string('game_status')->default('playing');

            $table->timestamps();

            $table->unique(['user_id', 'puzzle_id']);
        });
    }
};
