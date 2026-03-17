<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function down(): void
    {
        Schema::table('puzzles', static function (Blueprint $table): void {
            $table->dropColumn('title');
        });
    }

    public function up(): void
    {
        Schema::table('puzzles', static function (Blueprint $table): void {
            $table->string('title')->after('id');
        });
    }
};
