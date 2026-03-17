<?php

declare(strict_types=1);

namespace App\Filament\Resources\Puzzles\Pages;

use App\Filament\Resources\Puzzles\PuzzleResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePuzzle extends CreateRecord
{
    protected static string $resource = PuzzleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = \auth()->id();

        return $data;
    }
}
