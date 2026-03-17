<?php

declare(strict_types=1);

namespace App\Models;

final class Word extends Model
{
    protected static $unguarded = true;

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
