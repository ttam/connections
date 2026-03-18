<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Puzzle;
use App\Models\PuzzleProgress;
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

    public function mount(?string $id = null): void
    {
        $query = Puzzle::with(['categories.words', 'user'])
            ->where('is_published', true)
            ->whereDate('play_date', '<=', \now());

        $this->puzzle = $id === null
            ? $query->orderBy('play_date', 'desc')->firstOrFail()
            : $query->findOrFail($id);

        $this->mistakesRemaining = $this->puzzle->max_mistakes;

        $loaded = $this->loadProgress();

        if ($loaded === false) {
            $this->initializeBoard();
        }
    }

    public function initializeBoard(): void
    {
        // Flatten all 16 words from the puzzle and shuffle them
        $this->boardWords = $this->puzzle->categories
            ->flatMap->words
            ->shuffle();

        $this->saveProgress();
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

        $this->saveProgress();
    }

    public function deselectAll(): void
    {
        $this->selectedWordIds = [];
    }

    public function getShareText()
    {
        // 1. Start with the title and puzzle identifier (you could use the date here instead)
        $text = "Clonections\n";
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

        $this->saveProgress();
    }

    public function render()
    {
        return \view('livewire.game');
    }

    private function loadProgress(): bool
    {
        $state = null;

        // 1. Fetch the state from the DB (if logged in) or the Session (if guest)
        if (\auth()->check()) {
            $progress = PuzzleProgress::where('user_id', \auth()->id())
                ->where('puzzle_id', $this->puzzle->id)
                ->first();

            if ($progress) {
                $state = $progress->toArray();
            }
        } else {
            $state = \session()->get('puzzle_progress_' . $this->puzzle->id);
        }

        if (!$state) {
            return false; // No progress found
        }

        // 2. Hydrate the Livewire properties
        $this->guesses = $state['guesses'] ?? [];
        $this->solvedCategoryIds = $state['solved_category_ids'] ?? [];
        $this->mistakesRemaining = $state['mistakes_remaining'];
        $this->gameStatus = $state['game_status'] ?? 'playing';

        $allWords = $this->puzzle->categories->flatMap->words;

        // 3. Reconstruct the board words in the exact order they were saved
        if (!empty($state['current_word_order'])) {
            $this->boardWords = \collect($state['current_word_order'])
                ->map(static fn ($wordId) => $allWords->firstWhere('id', $wordId))
                ->filter()
                ->values();
        } else {
            $this->boardWords = $allWords->shuffle();
        }

        return true;
    }

    private function saveProgress(): void
    {
        // 1. Package the current state
        $state = [
            'guesses' => $this->guesses,
            'solved_category_ids' => $this->solvedCategoryIds,
            'current_word_order' => $this->boardWords->pluck('id')->toArray(),
            'mistakes_remaining' => $this->mistakesRemaining,
            'game_status' => $this->gameStatus,
        ];

        // 2. Save to the DB (if logged in) or the Session (if guest)
        if (\auth()->check()) {
            PuzzleProgress::updateOrCreate(
                [
                    'user_id' => \auth()->id(),
                    'puzzle_id' => $this->puzzle->id,
                ],
                $state
            );
        } else {
            \session()->put('puzzle_progress_' . $this->puzzle->id, $state);
        }
    }

    private function rebuildBoard(Collection $unsolvedWords): void
    {
        // Keep solved words at the top, grouped by category
        $solvedWords = $this->boardWords->filter(fn ($w) => \in_array($w->category_id, $this->solvedCategoryIds))
            ->sortBy('category_id');

        $this->boardWords = $solvedWords->concat($unsolvedWords);
    }
}
