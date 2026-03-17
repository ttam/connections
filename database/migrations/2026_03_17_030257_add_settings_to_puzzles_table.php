<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function down(): void
    {
        Schema::table('puzzles', static function (Blueprint $table): void {
            $table->dropColumn(['show_live_results', 'max_mistakes']);
        });
    }

    public function up(): void
    {
        Schema::table('puzzles', static function (Blueprint $table): void {
            $table->boolean('show_live_results')->default(false)->after('is_published');
            $table->integer('max_mistakes')->nullable()->default(4)->after('show_live_results');
        });
    }
};
