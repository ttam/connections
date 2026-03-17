<?php

use App\Livewire;
use Illuminate\Support\Facades\Route;

//Route::get('/', Livewire\Game::class)->name('home');
//
Route::get('/', Livewire\PuzzleArchive::class)->name('archive');
Route::get('/{id}', Livewire\Game::class)->name('game.play');
