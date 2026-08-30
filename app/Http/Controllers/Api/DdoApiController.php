<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DdoResource;
use App\Models\Ddo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DdoApiController extends Controller
{
    /**
     * Display a listing of the DDOs.
     */
    public function index(Request $request): AnonymousResourceCollection | JsonResponse
    {
        $query = Ddo::query();

        // Optional search filter
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

        // Direct field filters
        if ($request->filled('departmentName')) {
            $query->where('departmentName', $request->input('departmentName'));
        }

        if ($request->filled('districtName')) {
            $query->where('districtName', $request->input('districtName'));
        }

        if ($request->filled('treasuryCode')) {
            $query->where('treasuryCode', $request->input('treasuryCode'));
        }

        // Return all records without pagination if requested
        if ($request->boolean('all') || $request->input('paginate') === 'false') {
            return DdoResource::collection($query->get());
        }

        $perPage = min((int) $request->input('per_page', 15), 100);

        return DdoResource::collection($query->paginate($perPage));
    }

    /**
     * Display the specified DDO by ddoId or primary ID.
     */
    public function show(string $id): JsonResponse | DdoResource
    {
        $ddo = Ddo::where('ddoId', $id)
            ->orWhere('id', $id)
            ->first();

        if (! $ddo) {
            return response()->json([
                'status' => 'error',
                'message' => "Employee/DDO with ID '{$id}' not found.",
            ], 404);
        }

        return new DdoResource($ddo);
    }
}
