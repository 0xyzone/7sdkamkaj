<?php

namespace App\Filament\Widgets;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class UserAvatarWidget extends Widget implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected string $view = 'filament.widgets.user-avatar-widget';

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 1,
    ];

    protected static ?int $sort = -1;

    public function updateAvatarAction(): Action
    {
        return Action::make('updateAvatar')
            ->label('Update Avatar')
            ->icon(Heroicon::OutlinedCamera)
            ->color('primary')
            ->size('sm')
            ->modalHeading('Update Profile Avatar')
            ->modalDescription('Upload a clean photo or square picture for your account avatar.')
            ->form([
                FileUpload::make('avatar_url')
                    ->label('Upload New Avatar')
                    ->image()
                    ->avatar()
                    ->disk('public')
                    ->directory('avatars')
                    ->openable()
                    ->downloadable()
                    ->previewable()
                    ->maxSize(2048)
                    ->required(),
            ])
            ->action(function (array $data): void {
                $user = Auth::user();
                if (! $user) {
                    return;
                }

                $user->avatar_url = $data['avatar_url'];
                $user->save();

                Notification::make()
                    ->title('Avatar updated successfully')
                    ->success()
                    ->send();
            });
    }

    public function removeAvatarAction(): Action
    {
        return Action::make('removeAvatar')
            ->label('Remove')
            ->icon(Heroicon::OutlinedTrash)
            ->color('danger')
            ->size('sm')
            ->requiresConfirmation()
            ->modalHeading('Remove Avatar')
            ->modalDescription('Are you sure you want to remove your profile photo and reset to default initials?')
            ->visible(fn (): bool => filled(Auth::user()?->avatar_url))
            ->action(function (): void {
                $user = Auth::user();
                if (! $user) {
                    return;
                }

                $user->avatar_url = null;
                $user->save();

                Notification::make()
                    ->title('Avatar removed')
                    ->info()
                    ->send();
            });
    }
}
