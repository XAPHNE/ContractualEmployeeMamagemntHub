<?php

namespace App\Models;

use App\Observers\DdoObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([DdoObserver::class])]
class Ddo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ddoId',
        'ddoName',
        'pan',
        'department_id',
        'directorate',
        'postName',
        'officeName',
        'officeAddress',
        'mobileNumber',
        'treasuryName',
        'treasuryCode',
        'email',
        'districtName',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function getDepartmentNameAttribute(): ?string
    {
        return $this->department?->name;
    }
}
