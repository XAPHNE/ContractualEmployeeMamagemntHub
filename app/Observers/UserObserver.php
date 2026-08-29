<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserObserver
{
    /**
     * Handle the User "creating" event.
     */
    public function creating(User $user): void
    {
        if (Auth::check()) {
            $user->created_by ??= Auth::id();
            $user->updated_by ??= Auth::id();
        }
    }

    /**
     * Handle the User "updating" event.
     */
    public function updating(User $user): void
    {
        if (Auth::check()) {
            $user->updated_by = Auth::id();
        }
    }

    /**
     * Handle the User "deleting" event.
     */
    public function deleting(User $user): void
    {
        if (Auth::check() && ! $user->isForceDeleting()) {
            $user->deleted_by = Auth::id();
            $user->saveQuietly();
        }
    }

    /**
     * Handle the User "restoring" event.
     */
    public function restoring(User $user): void
    {
        $user->deleted_by = null;
    }
}
