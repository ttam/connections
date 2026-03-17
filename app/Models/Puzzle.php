<?php

declare(strict_types=1);

namespace App\Models;

final class Puzzle extends Model
{
    protected static $unguarded = true;

    public function categories()
    {
        return $this->hasMany(Category::class);
    }
}
