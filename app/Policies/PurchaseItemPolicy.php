<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PurchaseItem;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PurchaseItemPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PurchaseItem');
    }

    public function view(AuthUser $authUser, PurchaseItem $purchaseItem): bool
    {
        return $authUser->can('View:PurchaseItem');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PurchaseItem');
    }

    public function update(AuthUser $authUser, PurchaseItem $purchaseItem): bool
    {
        return $authUser->can('Update:PurchaseItem');
    }

    public function delete(AuthUser $authUser, PurchaseItem $purchaseItem): bool
    {
        return $authUser->can('Delete:PurchaseItem');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PurchaseItem');
    }

    public function restore(AuthUser $authUser, PurchaseItem $purchaseItem): bool
    {
        return $authUser->can('Restore:PurchaseItem');
    }

    public function forceDelete(AuthUser $authUser, PurchaseItem $purchaseItem): bool
    {
        return $authUser->can('ForceDelete:PurchaseItem');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PurchaseItem');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PurchaseItem');
    }

    public function replicate(AuthUser $authUser, PurchaseItem $purchaseItem): bool
    {
        return $authUser->can('Replicate:PurchaseItem');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PurchaseItem');
    }
}
