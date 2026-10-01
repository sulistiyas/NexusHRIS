<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\AssetAssignmentResource;
use App\Http\Resources\V1\AssetResource;
use App\Models\Asset;
use App\Models\AssetAssignment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AssetApiController extends Controller
{
    /**
     * Get list of assets currently assigned to the authenticated employee (US-25).
     */
    public function myAssets(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $employee = $request->user()->employee;

        if (! $employee) {
            return response()->json([
                'message' => 'Profil karyawan tidak ditemukan.',
            ], 422);
        }

        $assignments = AssetAssignment::with(['asset.category'])
            ->where('employee_id', $employee->id)
            ->whereNull('returned_date')
            ->latest('assigned_date')
            ->get();

        return AssetAssignmentResource::collection($assignments);
    }

    /**
     * List all corporate assets (for managers / HR).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $assets = Asset::with(['category', 'assignments' => function ($q) {
            $q->whereNull('returned_date')->with('employee');
        }])->latest('id')->paginate(15);

        return AssetResource::collection($assets);
    }
}
