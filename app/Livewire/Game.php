<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Puzzle;
use Illuminate\Support\Collection;
use Livewire\Component;

final class Game extends Component
{
    public Puzzle $puzzle;
    public Collection $boardWords;
    public array $selectedWordIds = [];
    public array $solvedCategoryIds = [];
    public ?int $mistakesRemaining = 4;
    public string $gameStatus = 'playing'; // 'playing', 'won', or 'lost'

    public array $guesses = [];

    public function mount(): void
    {
        // Fetch today's published puzzle
        $this->puzzle = Puzzle::with('categories.words')
            ->where('is_published', true)
            ->whereDate('play_date', '<=', \now())
            ->orderBy('play_date', 'desc')
            ->firstOrFail();

        $this->mistakesRemaining = $this->puzzle->max_mistakes;
        $this->initializeBoard();
    }

    public function initializeBoard(): void
    {
        // Flatten all 16 words from the puzzle and shuffle them
        $this->boardWords = $this->puzzle->categories
            ->flatMap->words
            ->shuffle();
    }

    public function toggleSelection($wordId): void
    {
        if ($this->gameStatus !== 'playing') {
            return;
        }

        if (\in_array($wordId, $this->selectedWordIds)) {
            // Deselect if already selected
            $this->selectedWordIds = \array_diff($this->selectedWordIds, [$wordId]);
        } elseif (\count($this->selectedWordIds) < 4) {
            // Select if we have room
            $this->selectedWordIds[] = $wordId;
        }
    }

    public function shuffle(): void
    {
        if ($this->gameStatus !== 'playing') {
            return;
        }

        // Shuffle only the unsolved words
        $unsolvedWords = $this->boardWords->reject(function ($word) {
            return \in_array($word->category_id, $this->solvedCategoryIds);
        })->shuffle();

        $this->rebuildBoard($unsolvedWords);
    }

    public function deselectAll(): void
    {
        $this->selectedWordIds = [];
    }

    public function getShareText()
    {
        // 1. Start with the title and puzzle identifier (you could use the date here instead)
        $text = "Connections\n";
        $text .= "Puzzle #" . $this->puzzle->id . "\n";

        // 2. Loop through the guesses array and map the numbers to colored emojis
        foreach ($this->guesses as $guessRow) {
            foreach ($guessRow as $difficultyLevel) {
                $text .= match ((int) $difficultyLevel) {
                    1 => '🟨',
                    2 => '🟩',
                    3 => '🟦',
                    4 => '🟪',
                    default => '⬜', // Fallback
                };
            }
            $text .= "\n"; // New line at the end of each row
        }

        return $text;
    }

    public function submit(): void
    {
        if (\count($this->selectedWordIds) !== 4 || $this->gameStatus !== 'playing') {
            return;
        }

        $selectedWords = $this->boardWords->whereIn('id', $this->selectedWordIds);

        $guessColors = $selectedWords->map->category->pluck('difficulty_level')->sort()->values()->toArray();
        $this->guesses[] = $guessColors;

        $categoryCounts = $selectedWords->groupBy('category_id')->map->count();

        if ($categoryCounts->count() === 1) {
            // Success! All 4 words belong to the same category.
            $categoryId = $categoryCounts->keys()->first();
            $this->solvedCategoryIds[] = $categoryId;
            $this->selectedWordIds = [];

            // Extract the remaining unsolved words so we can pin the solved ones to the top
            $unsolvedWords = $this->boardWords->reject(fn ($w) => \in_array($w->category_id, $this->solvedCategoryIds));
            $this->rebuildBoard($unsolvedWords);

            // Check for win condition
            if (\count($this->solvedCategoryIds) === 4) {
                $this->gameStatus = 'won';
            }
        } else {
            // Incorrect guess
            if ($categoryCounts->max() === 3) {
                // 3 words match, 1 is wrong
                $this->dispatch('toast', message: 'One away!');
            } else {
                $this->dispatch('toast', message: 'Incorrect guess.');
            }

            // Trigger Alpine.js shake animation
            $this->dispatch('shake-tiles');

            // 2. Handle Mistakes (Only decrement if not unlimited)
            if ($this->mistakesRemaining !== null) {
                $this->mistakesRemaining--;

                if ($this->mistakesRemaining <= 0) {
                    $this->gameStatus = 'lost';
                    $this->solvedCategoryIds = $this->puzzle->categories->pluck('id')->toArray();
                    $this->rebuildBoard(\collect());
                    $this->selectedWordIds = [];
                }
            }
        }
    }

    public function render()
    {
        return \view('livewire.game');
    }

    private function rebuildBoard(Collection $unsolvedWords): void
    {
        // Keep solved words at the top, grouped by category
        $solvedWords = $this->boardWords->filter(fn ($w) => \in_array($w->category_id, $this->solvedCategoryIds))
            ->sortBy('category_id');

        $this->boardWords = $solvedWords->concat($unsolvedWords);
    }
}
