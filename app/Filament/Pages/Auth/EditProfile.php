<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;

class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('avatar_url')
                    ->label('Profile Avatar')
                    ->avatar()
                    ->image()
                    ->disk('public')
                    ->directory('avatars')
                    ->openable()
                    ->downloadable()
                    ->previewable()
                    ->maxSize(2048)
                    ->columnSpanFull(),
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }

    protected function afterSave(): void
    {
        $user = $this->getUser();
        $avatarUrl = $user->getFilamentAvatarUrl();

        $this->dispatch('avatar-updated', url: $avatarUrl);
    }
}
