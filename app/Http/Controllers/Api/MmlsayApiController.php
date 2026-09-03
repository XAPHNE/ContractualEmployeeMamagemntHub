<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MmlsayApiController extends Controller
{
    /**
     * Handle incoming MMLSAY health insurance data query.
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $pan = $request->input('pan');
            $type = $request->input('type');
            $requestedMonth = (int) $request->input('month');
            $requestedFinYear = $request->input('finYear');

            if (! $pan) {
                return response()->json([
                    'status' => 'ERROR',
                    'message' => "The 'pan' query parameter is required.",
                ], 422);
            }

            $query = Employee::with(['ddo', 'contributions']);

            if ($pan) {
                $query->where('pan', $pan);
            }

            if ($type) {
                $query->where('type', $type);
            }

            $employee = $query->first();

            if (! $employee) {
                return response()->json([
                    'status' => 'ERROR',
                    'message' => "Employee record with PAN '{$pan}' was not found.",
                ], 404);
            }

            // Format profile date strings as DD-MM-YYYY as expected by MMLSAY spec
            $dob = $employee->dob ? (strtotime($employee->dob) ? date('d-m-Y', strtotime($employee->dob)) : $employee->dob) : '';
            $doj = $employee->date_of_joining ? $employee->date_of_joining->format('d-m-Y') : '';
            $dor = $employee->dor ? $employee->dor->format('d-m-Y') : '';

            $profile = [
                'id' => (string) $employee->id,
                'full_Name' => $employee->full_Name ?? '',
                'first_Name' => $employee->first_Name ?? '',
                'middle_Name' => $employee->middle_Name ?? '',
                'last_Name' => $employee->last_Name ?? '',
                'type' => $employee->type ?? 'EMPLOYEE',
                'mobile' => $employee->mobile ?? '',
                'employee_code' => $employee->employee_code ?? '',
                'pan' => $employee->pan ?? '',
                'gender' => $employee->gender ?? '',
                'dob' => $dob,
                'designation' => $employee->designation ?? '',
                'grade' => $employee->grade ?? '',
                'pay_band' => $employee->pay_band ?? '',
                'grade_pay' => $employee->grade_pay ? (float) $employee->grade_pay : 0.0,
                'date_of_joining' => $doj,
                'dor' => $dor,
                'gpf_nps' => $employee->gpf_nps ?? '',
                'email' => $employee->email,
                'present_address' => $employee->present_address ?? '',
                'permanent_address' => $employee->permanent_address ?? '',
                'district' => $employee->district,
                'active' => (string) ($employee->active ?? 'true'),
            ];

            $bank = [
                'ac_number' => $employee->ac_number ?? '',
                'ac_type' => $employee->ac_type ?? 'S',
                'ac_name' => $employee->ac_name ?? '',
                'ac_bank' => $employee->ac_bank ?? '',
                'ac_branch' => $employee->ac_branch ?? '',
                'ac_ifsc' => $employee->ac_ifsc ?? '',
            ];

            $ddoModel = $employee->ddo;
            $ddo = [
                'department' => $ddoModel?->departmentName ?? '',
                'department_id' => $ddoModel ? (string) $ddoModel->id : '',
                'department_district' => $ddoModel?->districtName ?? '',
                'treasury' => $ddoModel?->treasuryName ?? '',
                'treasury_id' => $ddoModel?->treasuryCode ?? '',
                'ddo' => $ddoModel?->ddoName ?? '',
                'ddo_id' => $ddoModel?->ddoId ?? '',
                'office_name' => $ddoModel?->officeName ?? '',
            ];

            // Contributions processing
            $allContributions = $employee->contributions->sortBy('contribution_date');

            $firstContribution = $allContributions->first();
            $lastContribution = $allContributions->last();

            // Find specific or current requested contribution
            $currentContrib = null;
            if ($requestedFinYear && $requestedMonth) {
                $currentContrib = $allContributions->first(function ($item) use ($requestedFinYear, $requestedMonth) {
                    return $item->fin_year === $requestedFinYear && (int) $item->month === (int) $requestedMonth;
                });
            }

            if (! $currentContrib && $lastContribution) {
                $currentContrib = $lastContribution;
            }

            $contributionAmount = $currentContrib ? (float) $currentContrib->contribution_amount : 0.0;
            $totalContribution = (float) $allContributions->sum('contribution_amount');

            $contributionInfo = [
                'contribution_amount' => $contributionAmount,
                'contribution_started_date' => $firstContribution ? $firstContribution->contribution_date->format('Y-m-d') : null,
                'last_contribution_date' => $lastContribution ? $lastContribution->contribution_date->format('Y-m-d') : null,
                'finYear' => $requestedFinYear ?: ($currentContrib ? $currentContrib->fin_year : ''),
                'month' => $requestedMonth ? (string) $requestedMonth : ($currentContrib ? (string) $currentContrib->month : ''),
                'total_contribution' => $totalContribution,
                'message' => 'SUCCESSFUL',
            ];

            $contributionHistory = $allContributions->map(function ($contrib) {
                return [
                    'month' => (int) $contrib->month,
                    'contribution_amount' => (float) $contrib->contribution_amount,
                    'contribution_date' => $contrib->contribution_date->format('Y-m-d'),
                    'finYear' => $contrib->fin_year,
                ];
            })->values()->toArray();

            return response()->json([
                'profile' => $profile,
                'bank' => $bank,
                'ddo' => $ddo,
                'contribution_info' => $contributionInfo,
                'contribution_history' => $contributionHistory,
                'status' => 'SUCCESS',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'An error occurred while fetching MMLSAY record.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
