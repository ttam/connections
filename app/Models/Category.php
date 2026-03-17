<?php

declare(strict_types=1);

namespace App\Models;

final class Category extends Model
{
    protected static $unguarded = true;

    public function words()
    {
        return $this->hasMany(Word::class);
    }
}
