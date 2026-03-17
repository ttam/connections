<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }

    public function up(): void
    {
        Schema::create('categories', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('puzzle_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->integer('difficulty_level');
            $table->string('color_hex')->nullable();
            $table->timestamps();
        });
    }
};
