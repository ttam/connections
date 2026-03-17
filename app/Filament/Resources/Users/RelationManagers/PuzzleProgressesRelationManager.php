<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PuzzleProgressesRelationManager extends RelationManager
{
    protected static string $relationship = 'puzzleProgresses';

    protected static ?string $relatedResource = UserResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('puzzle.title')
                    ->label('Puzzle Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('game_status')
                    ->label('Status')
                    ->badge()
                    ->color(static fn (string $state): string => match ($state) {
                        'playing' => 'warning',
                        'won' => 'success',
                        'lost' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('mistakes_remaining')
                    ->label('Mistakes Left')
                    ->numeric(),

                TextColumn::make('updated_at')
                    ->label('Last Played')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Removed CreateAction
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
