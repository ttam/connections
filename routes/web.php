<?php

declare(strict_types=1);

use App\Http\Controllers;
use App\Livewire;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(static function () {
    Route::get('/', Livewire\PuzzleArchive::class)->name('archive');
    Route::get('/{id}', Livewire\Game::class)->name('game.play')->where(['id' => '[-a-f0-9]+']);
    Route::get('/{id}.png', Controllers\OGImageController::class)->name('game.image')->where(['id' => '[-a-f0-9]+']);
});
