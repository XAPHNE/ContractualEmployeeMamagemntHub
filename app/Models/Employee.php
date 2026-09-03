<?php

namespace App\Models;

use App\Observers\EmployeeObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([EmployeeObserver::class])]
class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'emp_id',
        'full_Name',
        'first_Name',
        'middle_Name',
        'last_Name',
        'type',
        'mobile',
        'employee_code',
        'pan',
        'gender',
        'dob',
        'designation',
        'grade',
        'pay_band',
        'grade_pay',
        'date_of_joining',
        'dor',
        'gpf_nps',
        'email',
        'present_address',
        'permanent_address',
        'pincode',
        'district',
        'active',
        'ac_number',
        'ac_type',
        'ac_name',
        'ac_bank',
        'ac_branch',
        'ac_ifsc',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'date_of_joining' => 'date',
            'dor' => 'date',
        ];
    }

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
}
