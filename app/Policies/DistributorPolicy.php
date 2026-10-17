<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Distributor;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class DistributorPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Distributor');
    }

    public function view(AuthUser $authUser, Distributor $distributor): bool
    {
        return $authUser->can('View:Distributor');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Distributor');
    }

    public function update(AuthUser $authUser, Distributor $distributor): bool
    {
        return $authUser->can('Update:Distributor');
    }

    public function delete(AuthUser $authUser, Distributor $distributor): bool
    {
        return $authUser->can('Delete:Distributor');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Distributor');
    }

    public function restore(AuthUser $authUser, Distributor $distributor): bool
    {
        return $authUser->can('Restore:Distributor');
    }

    public function forceDelete(AuthUser $authUser, Distributor $distributor): bool
    {
        return $authUser->can('ForceDelete:Distributor');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Distributor');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Distributor');
    }

    public function replicate(AuthUser $authUser, Distributor $distributor): bool
    {
        return $authUser->can('Replicate:Distributor');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Distributor');
    }
}
