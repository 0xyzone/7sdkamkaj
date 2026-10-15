<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Information')
                    ->description('Personal details, credentials, and role assignments.')
                    ->icon(Heroicon::OutlinedUser)
                    ->schema([
                        FileUpload::make('avatar_url')
                            ->label('Profile Avatar')
                            ->image()
                            ->avatar()
                            ->disk('public')
                            ->directory('avatars')
                            ->openable()
                            ->downloadable()
                            ->previewable()
                            ->maxSize(2048)
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Full Name')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('email')
                                    ->label('Email Address')
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),

                                TextInput::make('password')
                                    ->label('Password')
                                    ->password()
                                    ->revealable()
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->dehydrated(fn (?string $state): bool => filled($state))
                                    ->maxLength(255)
                                    ->helperText('Leave blank when editing to keep existing password.'),

                                Select::make('roles')
                                    ->label('Assigned Roles')
                                    ->relationship('roles', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable(),
                            ]),
                    ]),

                Section::make('Status & Verification')
                    ->description('Manage user active state and email verification.')
                    ->icon(Heroicon::OutlinedShieldCheck)
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Account Active')
                                    ->helperText('Inactive users cannot log into the panel.')
                                    ->default(true)
                                    ->inline(false)
                                    ->onColor('success')
                                    ->offColor('danger'),

                                DateTimePicker::make('email_verified_at')
                                    ->label('Email Verified At')
                                    ->nullable(),
                            ]),
                    ]),
            ]);
    }
}
