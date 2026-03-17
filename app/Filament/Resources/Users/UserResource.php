<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\RelationManagers\PuzzleProgressesRelationManager;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $recordTitleAttribute = 'name';

    public static function canAccess(): bool
    {
        return \auth()->user()->email === \config('admin.email');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('password')
                            ->password()
                            // Hash the password before saving to the database
                            ->dehydrateStateUsing(static fn (string $state): string => Hash::make($state))
                            // Only save the password field if it is NOT empty
                            ->dehydrated(static fn (?string $state): bool => \filled($state))
                            // Require the password ONLY on the 'create' page, not the 'edit' page
                            ->required(static fn (string $operation): bool => $operation === 'create')
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),

                Action::make('sendPasswordReset')
                    ->label('Reset Password')
                    ->icon('heroicon-o-envelope')
                    ->color('gray')
                    ->requiresConfirmation() // Adds a nice "Are you sure?" modal
                    ->modalHeading('Send Password Reset Link')
                    ->modalDescription('This will email the user a secure link to choose a new password.')
                    ->action(static function (User $record): void {
                        // Trigger Laravel's built-in password reset email
                        $status = Password::broker()->sendResetLink(
                            ['email' => $record->email]
                        );

                        // Show a toast notification based on the result
                        if ($status === Password::RESET_LINK_SENT) {
                            Notification::make()
                                ->title('Reset email sent!')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Failed to send email.')
                                ->body(\__($status)) // Outputs the Laravel error message
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PuzzleProgressesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
