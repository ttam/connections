<?php

declare(strict_types=1);

namespace App\Models;

final class PuzzleProgress extends Model
{
    protected $guarded = [];

    protected $casts = [
        'guesses' => 'array',
        'solved_category_ids' => 'array',
        'current_word_order' => 'array',
    ];
}
