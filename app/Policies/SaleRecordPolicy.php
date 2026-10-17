<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SaleRecord;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class SaleRecordPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SaleRecord');
    }

    public function view(AuthUser $authUser, SaleRecord $saleRecord): bool
    {
        return $authUser->can('View:SaleRecord');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SaleRecord');
    }

    public function update(AuthUser $authUser, SaleRecord $saleRecord): bool
    {
        return $authUser->can('Update:SaleRecord');
    }

    public function delete(AuthUser $authUser, SaleRecord $saleRecord): bool
    {
        return $authUser->can('Delete:SaleRecord');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SaleRecord');
    }

    public function restore(AuthUser $authUser, SaleRecord $saleRecord): bool
    {
        return $authUser->can('Restore:SaleRecord');
    }

    public function forceDelete(AuthUser $authUser, SaleRecord $saleRecord): bool
    {
        return $authUser->can('ForceDelete:SaleRecord');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SaleRecord');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SaleRecord');
    }

    public function replicate(AuthUser $authUser, SaleRecord $saleRecord): bool
    {
        return $authUser->can('Replicate:SaleRecord');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SaleRecord');
    }
}
