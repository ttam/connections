<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Puzzle;
use Livewire\Component;
use Livewire\WithPagination;

final class PuzzleArchive extends Component
{
    use WithPagination;

    public function render()
    {
        $puzzles = Puzzle::where('is_published', true)
            ->whereDate('play_date', '<=', \now())
            ->orderBy('play_date', 'desc')
            ->paginate(20);

        return \view('livewire.archive', [
            'puzzles' => $puzzles,
        ]);
    }
}
