<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DdoResource;
use App\Models\Ddo;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DdoApiController extends Controller
{
    /**
     * Display a listing of DDO / Employee records for SAP ERP integration.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Ddo::query();

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
            if ($request->filled('ddoId')) {
                $query->where('ddoId', $request->input('ddoId'));
            }

            if ($request->filled('pan')) {
                $query->where('pan', $request->input('pan'));
            }

            if ($request->filled('departmentName')) {
                $query->where('departmentName', $request->input('departmentName'));
            }

            if ($request->filled('districtName')) {
                $query->where('districtName', $request->input('districtName'));
            }

            if ($request->filled('treasuryCode')) {
                $query->where('treasuryCode', $request->input('treasuryCode'));
            }

            // 3. Global Search Filter
            if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('ddoId', 'like', "%{$search}%")
                        ->orWhere('ddoName', 'like', "%{$search}%")
                        ->orWhere('pan', 'like', "%{$search}%")
                        ->orWhere('departmentName', 'like', "%{$search}%")
                        ->orWhere('districtName', 'like', "%{$search}%")
                        ->orWhere('treasuryCode', 'like', "%{$search}%");
                });
            }

            // Order by most recently updated for delta sync reliability
            $query->latest('updated_at');

            // 4. Return without pagination if requested (?all=true or ?paginate=false)
            if ($request->boolean('all') || $request->input('paginate') === 'false') {
                $records = $query->get();
                $data = DdoResource::collection($records)->resolve();

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
            $data = DdoResource::collection($paginator->items())->resolve();

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
                'message' => 'An error occurred while fetching DDO records.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Display a single DDO record by 8-digit ddoId or primary ID.
     */
    public function show(string $id): JsonResponse
    {
        $ddo = Ddo::where('ddoId', $id)
            ->orWhere('id', $id)
            ->first();

        if (! $ddo) {
            return response()->json([
                'status' => 'error',
                'code' => 'NOT_FOUND',
                'message' => "Employee/DDO record with ID '{$id}' was not found.",
            ], 404);
        }

        $data = (new DdoResource($ddo))->resolve();

        return response()->json([
            'status' => 'success',
            'timestamp' => now()->toIso8601String(),
            'count' => 1,
            'data' => $data,
        ], 200);
    }
}
