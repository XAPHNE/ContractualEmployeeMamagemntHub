<?php

namespace App\Observers;

use App\Models\Ddo;
use Illuminate\Support\Facades\Auth;

class DdoObserver
{
    /**
     * Handle the Ddo "creating" event.
     */
    public function creating(Ddo $ddo): void
    {
        if (Auth::check()) {
            $ddo->created_by ??= Auth::id();
            $ddo->updated_by ??= Auth::id();
        }
    }

    /**
     * Handle the Ddo "updating" event.
     */
    public function updating(Ddo $ddo): void
    {
        if (Auth::check()) {
            $ddo->updated_by = Auth::id();
        }
    }

    /**
     * Handle the Ddo "deleting" event.
     */
    public function deleting(Ddo $ddo): void
    {
        if (Auth::check() && ! $ddo->isForceDeleting()) {
            $ddo->deleted_by = Auth::id();
            $ddo->saveQuietly();
        }
    }

    /**
     * Handle the Ddo "restoring" event.
     */
    public function restoring(Ddo $ddo): void
    {
        $ddo->deleted_by = null;
    }
}
