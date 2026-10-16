<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ExpenseRecord;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ExpenseRecordPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ExpenseRecord');
    }

    public function view(AuthUser $authUser, ExpenseRecord $expenseRecord): bool
    {
        return $authUser->can('View:ExpenseRecord');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ExpenseRecord');
    }

    public function update(AuthUser $authUser, ExpenseRecord $expenseRecord): bool
    {
        return $authUser->can('Update:ExpenseRecord');
    }

    public function delete(AuthUser $authUser, ExpenseRecord $expenseRecord): bool
    {
        return $authUser->can('Delete:ExpenseRecord');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ExpenseRecord');
    }

    public function restore(AuthUser $authUser, ExpenseRecord $expenseRecord): bool
    {
        return $authUser->can('Restore:ExpenseRecord');
    }

    public function forceDelete(AuthUser $authUser, ExpenseRecord $expenseRecord): bool
    {
        return $authUser->can('ForceDelete:ExpenseRecord');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ExpenseRecord');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ExpenseRecord');
    }

    public function replicate(AuthUser $authUser, ExpenseRecord $expenseRecord): bool
    {
        return $authUser->can('Replicate:ExpenseRecord');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ExpenseRecord');
    }
}
