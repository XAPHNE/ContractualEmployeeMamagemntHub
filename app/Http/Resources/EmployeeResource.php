<?php

namespace App\Http\Resources;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Employee
 */
class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'emp_id' => $this->emp_id,
            'full_Name' => $this->full_Name,
            'first_Name' => $this->first_Name,
            'middle_Name' => $this->middle_Name,
            'last_Name' => $this->last_Name,
            'type' => $this->type,
            'mobile' => $this->mobile,
            'employee_code' => $this->employee_code,
            'pan' => $this->pan,
            'gender' => $this->gender,
            'dob' => $this->dob,
            'designation' => $this->designation,
            'grade' => $this->grade,
            'pay_band' => $this->pay_band,
            'grade_pay' => $this->grade_pay,
            'date_of_joining' => is_string($this->date_of_joining) ? $this->date_of_joining : $this->date_of_joining?->toDateString(),
            'dor' => is_string($this->dor) ? $this->dor : $this->dor?->toDateString(),
            'gpf_nps' => $this->gpf_nps,
            'email' => $this->email,
            'present_address' => $this->present_address,
            'permanent_address' => $this->permanent_address,
            'pincode' => $this->pincode,
            'district' => $this->district,
            'active' => $this->active,
            'ac_number' => $this->ac_number,
            'ac_type' => $this->ac_type,
            'ac_name' => $this->ac_name,
            'ac_bank' => $this->ac_bank,
            'ac_branch' => $this->ac_branch,
            'ac_ifsc' => $this->ac_ifsc,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
