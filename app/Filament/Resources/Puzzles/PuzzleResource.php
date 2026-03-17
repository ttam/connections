<?php

declare(strict_types=1);

namespace App\Filament\Resources\Puzzles;

use App\Filament\Resources\Puzzles\Pages\CreatePuzzle;
use App\Filament\Resources\Puzzles\Pages\EditPuzzle;
use App\Filament\Resources\Puzzles\Pages\ListPuzzles;
use App\Filament\Resources\Puzzles\Pages\ViewPuzzle;
use App\Filament\Resources\Puzzles\Schemas\PuzzleInfolist;
use App\Models\Puzzle;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class PuzzleResource extends Resource
{
    protected static ?string $model = Puzzle::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Puzzle Details')
                    ->schema([
                        DatePicker::make('play_date')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Toggle::make('is_published')
                            ->default(false)
                            ->helperText('Publishing will make this puzzle live on its designated date.'),
                    ])->columns(2),

                Repeater::make('categories')
                    ->relationship()
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->placeholder('e.g., FRESHWATER FISH')
                            ->columnSpan(2),

                        Select::make('difficulty_level')
                            ->options([
                                1 => 'Yellow (Straightforward)',
                                2 => 'Green (Medium)',
                                3 => 'Blue (Hard)',
                                4 => 'Purple (Tricky)',
                            ])
                            ->required()
                            ->columnSpan(2),

                        Repeater::make('words')
                            ->relationship()
                            ->schema([
                                TextInput::make('text')
                                    ->required()
                                    ->hiddenLabel()
                                    ->placeholder('Enter word...'),
                            ])
                            ->minItems(4)
                            ->maxItems(4)
                            ->columns(2)
                            ->columnSpanFull()
                    ])
                    ->minItems(4)
                    ->maxItems(4)
                    ->collapsible()
                    ->itemLabel(static fn (array $state): ?string => $state['title'] ?? null)
                    ->columnSpanFull()
                    ->columns(4)
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PuzzleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('play_date')
                    ->date()
                    ->sortable(),
                ToggleColumn::make('is_published'),
                TextColumn::make('categories_count')
                    ->counts('categories')
                    ->label('Categories'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPuzzles::route('/'),
            'create' => CreatePuzzle::route('/create'),
            'view' => ViewPuzzle::route('/{record}'),
            'edit' => EditPuzzle::route('/{record}/edit'),
        ];
    }
}
