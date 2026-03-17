<?php

declare(strict_types=1);

namespace App\Models;

final class Puzzle extends Model
{
    protected $casts = [
        'play_date' => 'datetime',
    ];

    protected static $unguarded = true;

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
