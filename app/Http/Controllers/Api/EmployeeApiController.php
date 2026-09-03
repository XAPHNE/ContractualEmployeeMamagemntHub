<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeApiController extends Controller
{
    /**
     * Display a listing of Employee records for Government of Assam & external integrations.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Employee::query();

            // 1. Delta Sync / Incremental Update Support
            $since = $request->input('updated_since')
                ?? $request->input('since')
                ?? $request->input('from_date');

            if (! empty($since)) {
                try {
                    $fromDate = Carbon::parse($since);
                    $query->where('updated_at', '>=', $fromDate);
                } catch (Exception) {
                    return response()->json([
                        'status' => 'error',
                        'code' => 'INVALID_DATE_FORMAT',
                        'message' => "Invalid date format for 'updated_since'. Please use ISO 8601 format (e.g. 2026-08-30T00:00:00Z).",
                    ], 422);
                }
            }

            // 2. Exact Field Filters
            if ($request->filled('emp_id')) {
                $query->where('emp_id', $request->input('emp_id'));
            }

            if ($request->filled('pan')) {
                $query->where('pan', $request->input('pan'));
            }

            if ($request->filled('district')) {
                $query->where('district', $request->input('district'));
            }

            if ($request->filled('type')) {
                $query->where('type', $request->input('type'));
            }

            if ($request->filled('mobile')) {
                $query->where('mobile', $request->input('mobile'));
            }

            if ($request->filled('email')) {
                $query->where('email', $request->input('email'));
            }

            if ($request->filled('employee_code')) {
                $query->where('employee_code', $request->input('employee_code'));
            }

            // 3. Global Search Filter
            if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('emp_id', 'like', "%{$search}%")
                        ->orWhere('full_Name', 'like', "%{$search}%")
                        ->orWhere('pan', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhere('designation', 'like', "%{$search}%")
                        ->orWhere('district', 'like', "%{$search}%")
                        ->orWhere('employee_code', 'like', "%{$search}%");
                });
            }

            // Order by most recently updated for delta sync reliability
            $query->latest('updated_at');

            // 4. Return without pagination if requested (?all=true or ?paginate=false)
            if ($request->boolean('all') || $request->input('paginate') === 'false') {
                $records = $query->get();
                $data = EmployeeResource::collection($records)->resolve();

                return response()->json([
                    'status' => 'success',
                    'timestamp' => now()->toIso8601String(),
                    'count' => count($data),
                    'total' => count($data),
                    'data' => $data,
                ], 200);
            }

            // 5. Paginated Response
            $perPage = min(max((int) $request->input('per_page', 50), 1), 500);
            $paginator = $query->paginate($perPage);
            $data = EmployeeResource::collection($paginator->items())->resolve();

            return response()->json([
                'status' => 'success',
                'timestamp' => now()->toIso8601String(),
                'count' => count($data),
                'total' => $paginator->total(),
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total_pages' => $paginator->lastPage(),
                    'has_more_pages' => $paginator->hasMorePages(),
                ],
                'data' => $data,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'code' => 'INTERNAL_SERVER_ERROR',
                'message' => 'An error occurred while fetching Employee records.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Display a single Employee record by emp_id or primary ID.
     */
    public function show(string $id): JsonResponse
    {
        $employee = Employee::where('emp_id', $id)
            ->orWhere('id', $id)
            ->first();

        if (! $employee) {
            return response()->json([
                'status' => 'error',
                'code' => 'NOT_FOUND',
                'message' => "Employee record with ID '{$id}' was not found.",
            ], 404);
        }

        $data = (new EmployeeResource($employee))->resolve();

        return response()->json([
            'status' => 'success',
            'timestamp' => now()->toIso8601String(),
            'count' => 1,
            'data' => $data,
        ], 200);
    }
}
