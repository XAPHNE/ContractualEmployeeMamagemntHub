<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;

class DepartmentObserver
{
    /**
     * Handle the Department "creating" event.
     */
    public function creating(Department $department): void
    {
        if (empty($department->dept_id)) {
            $max = Department::withTrashed()->max('dept_id');
            $department->dept_id = (string) ($max ? ((int) $max + 1) : 1);
        }

        if (Auth::check()) {
            $department->created_by ??= Auth::id();
            $department->updated_by ??= Auth::id();
        }
    }

    /**
     * Handle the Department "created" event.
     */
    public function created(Department $department): void
    {
        ActivityLog::log("Created department '{$department->name}' ({$department->dept_id})", $department);
    }

    /**
     * Handle the Department "updating" event.
     */
    public function updating(Department $department): void
    {
        if (Auth::check()) {
            $department->updated_by = Auth::id();
        }
    }

    /**
     * Handle the Department "updated" event.
     */
    public function updated(Department $department): void
    {
        $dirty = $department->getDirty();
        unset($dirty['updated_at']);

        if (! empty($dirty)) {
            ActivityLog::log("Updated department '{$department->name}' ({$department->dept_id})", $department, ['changes' => $dirty]);
        }
    }

    /**
     * Handle the Department "deleting" event.
     */
    public function deleting(Department $department): void
    {
        if (Auth::check() && ! $department->isForceDeleting()) {
            $department->deleted_by = Auth::id();
            $department->saveQuietly();
        }

        ActivityLog::log("Deleted department '{$department->name}' ({$department->dept_id})", $department);
    }

    /**
     * Handle the Department "restored" event.
     */
    public function restored(Department $department): void
    {
        $department->deleted_by = null;
        ActivityLog::log("Restored department '{$department->name}' ({$department->dept_id})", $department);
    }
}
