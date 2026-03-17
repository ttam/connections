<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Puzzle;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

final class PuzzleSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['title' => 'Example Puzzle #2', 'play_date' => Carbon::yesterday(), 'max_mistakes' => null],
            ['title' => 'Example Puzzle #1', 'play_date' => Carbon::today()],
        ];

        foreach ($defaults as $default) {
            $puzzle = Puzzle::create([
                'is_published' => true,
                'show_live_results' => false,
                'max_mistakes' => 4,
                ...$default,
            ]);

            $categories = [
                ['title' => 'Yellow', 'difficulty_level' => 1],
                ['title' => 'Green', 'difficulty_level' => 2],
                ['title' => 'Blue', 'difficulty_level' => 3],
                ['title' => 'Purple', 'difficulty_level' => 4],
            ];

            // 3. Loop through and create the related records
            foreach ($categories as $category) {
                $category = $puzzle->categories()->create($category);

                for ($i = 1; $i <= 4; $i++) {
                    $category->words()->create([
                        'text' => \sprintf(
                            '%s%d',
                            \substr($category['title'], 0, 1),
                            $i,
                        ),
                    ]);
                }
            }
        }
    }
}
