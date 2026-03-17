<?php

use App\Livewire;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(static function () {
    Route::get('/', Livewire\PuzzleArchive::class)->name('archive');
    Route::get('/{id}', Livewire\Game::class)->name('game.play');
});
