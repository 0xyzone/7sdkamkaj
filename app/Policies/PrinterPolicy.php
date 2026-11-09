<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Printer;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PrinterPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Printer') || $authUser->hasRole('super_admin');
    }

    public function view(AuthUser $authUser, Printer $printer): bool
    {
        return $authUser->can('View:Printer') || $authUser->hasRole('super_admin');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Printer') || $authUser->hasRole('super_admin');
    }

    public function update(AuthUser $authUser, Printer $printer): bool
    {
        return $authUser->can('Update:Printer') || $authUser->hasRole('super_admin');
    }

    public function delete(AuthUser $authUser, Printer $printer): bool
    {
        return $authUser->can('Delete:Printer') || $authUser->hasRole('super_admin');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Printer') || $authUser->hasRole('super_admin');
    }
}

