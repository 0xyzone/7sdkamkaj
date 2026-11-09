<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\LabelTemplate;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class LabelTemplatePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LabelTemplate') || $authUser->hasRole('super_admin');
    }

    public function view(AuthUser $authUser, LabelTemplate $labelTemplate): bool
    {
        return $authUser->can('View:LabelTemplate') || $authUser->hasRole('super_admin');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LabelTemplate') || $authUser->hasRole('super_admin');
    }

    public function update(AuthUser $authUser, LabelTemplate $labelTemplate): bool
    {
        return $authUser->can('Update:LabelTemplate') || $authUser->hasRole('super_admin');
    }

    public function delete(AuthUser $authUser, LabelTemplate $labelTemplate): bool
    {
        return $authUser->can('Delete:LabelTemplate') || $authUser->hasRole('super_admin');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LabelTemplate') || $authUser->hasRole('super_admin');
    }
}

