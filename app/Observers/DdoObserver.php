<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Ddo;
use Illuminate\Support\Facades\Auth;

class DdoObserver
{
    public function creating(Ddo $ddo): void
    {
        if (Auth::check()) {
            $ddo->created_by ??= Auth::id();
            $ddo->updated_by ??= Auth::id();
        }
    }

    public function created(Ddo $ddo): void
    {
        ActivityLog::log("Created employee/DDO '{$ddo->ddoName}' ({$ddo->ddoId})", $ddo);
    }

    public function updating(Ddo $ddo): void
    {
        if (Auth::check()) {
            $ddo->updated_by = Auth::id();
        }
    }

    public function updated(Ddo $ddo): void
    {
        $dirty = $ddo->getDirty();
        unset($dirty['updated_at']);

        if (! empty($dirty)) {
            ActivityLog::log("Updated employee/DDO '{$ddo->ddoName}' ({$ddo->ddoId})", $ddo, ['changes' => $dirty]);
        }
    }

    public function deleting(Ddo $ddo): void
    {
        if (Auth::check() && ! $ddo->isForceDeleting()) {
            $ddo->deleted_by = Auth::id();
            $ddo->saveQuietly();
        }

        ActivityLog::log("Deleted employee/DDO '{$ddo->ddoName}' ({$ddo->ddoId})", $ddo);
    }

    public function restoring(Ddo $ddo): void
    {
        $ddo->deleted_by = null;
        ActivityLog::log("Restored employee/DDO '{$ddo->ddoName}' ({$ddo->ddoId})", $ddo);
    }
}
