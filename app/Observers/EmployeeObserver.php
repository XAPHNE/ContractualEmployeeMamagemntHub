<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

class EmployeeObserver
{
    public function creating(Employee $employee): void
    {
        if (Auth::check()) {
            $employee->created_by ??= Auth::id();
            $employee->updated_by ??= Auth::id();
        }
    }

    public function created(Employee $employee): void
    {
        ActivityLog::log("Created employee '{$employee->full_Name}' ({$employee->emp_id})", $employee);
    }

    public function updating(Employee $employee): void
    {
        if (Auth::check()) {
            $employee->updated_by = Auth::id();
        }
    }

    public function updated(Employee $employee): void
    {
        $dirty = $employee->getDirty();
        unset($dirty['updated_at']);

        if (! empty($dirty)) {
            ActivityLog::log("Updated employee '{$employee->full_Name}' ({$employee->emp_id})", $employee, ['changes' => $dirty]);
        }
    }

    public function deleting(Employee $employee): void
    {
        if (Auth::check() && ! $employee->isForceDeleting()) {
            $employee->deleted_by = Auth::id();
            $employee->saveQuietly();
        }

        ActivityLog::log("Deleted employee '{$employee->full_Name}' ({$employee->emp_id})", $employee);
    }

    public function restoring(Employee $employee): void
    {
        $employee->deleted_by = null;
        ActivityLog::log("Restored employee '{$employee->full_Name}' ({$employee->emp_id})", $employee);
    }
}
