<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\PasswordHistory;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserObserver
{
    public function creating(User $user): void
    {
        if (Auth::check()) {
            $user->created_by ??= Auth::id();
            $user->updated_by ??= Auth::id();
        }
    }

    public function created(User $user): void
    {
        if ($user->password) {
            PasswordHistory::create([
                'user_id' => $user->id,
                'password' => $user->password,
            ]);
        }

        ActivityLog::log("Created user '{$user->name}' ({$user->email})", $user);
    }

    public function updating(User $user): void
    {
        if (Auth::check()) {
            $user->updated_by = Auth::id();
        }

        if ($user->isDirty('password')) {
            PasswordHistory::create([
                'user_id' => $user->id,
                'password' => $user->password,
            ]);
        }
    }

    public function updated(User $user): void
    {
        $dirty = $user->getDirty();
        unset($dirty['password'], $dirty['updated_at']);

        if (! empty($dirty)) {
            ActivityLog::log("Updated user '{$user->name}'", $user, ['changes' => $dirty]);
        }
    }

    public function deleting(User $user): void
    {
        if (Auth::check() && ! $user->isForceDeleting()) {
            $user->deleted_by = Auth::id();
            $user->saveQuietly();
        }

        ActivityLog::log("Deleted user '{$user->name}'", $user);
    }

    public function restoring(User $user): void
    {
        $user->deleted_by = null;
        ActivityLog::log("Restored user '{$user->name}'", $user);
    }
}
