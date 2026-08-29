<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Ddo;
use Illuminate\Auth\Access\HandlesAuthorization;

class DdoPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Ddo');
    }

    public function view(AuthUser $authUser, Ddo $ddo): bool
    {
        return $authUser->can('View:Ddo');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Ddo');
    }

    public function update(AuthUser $authUser, Ddo $ddo): bool
    {
        return $authUser->can('Update:Ddo');
    }

    public function delete(AuthUser $authUser, Ddo $ddo): bool
    {
        return $authUser->can('Delete:Ddo');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Ddo');
    }

    public function restore(AuthUser $authUser, Ddo $ddo): bool
    {
        return $authUser->can('Restore:Ddo');
    }

    public function forceDelete(AuthUser $authUser, Ddo $ddo): bool
    {
        return $authUser->can('ForceDelete:Ddo');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Ddo');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Ddo');
    }

    public function replicate(AuthUser $authUser, Ddo $ddo): bool
    {
        return $authUser->can('Replicate:Ddo');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Ddo');
    }

}