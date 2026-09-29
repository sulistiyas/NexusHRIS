<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\V1\BranchResource;
use App\Models\Branch;
use Illuminate\Http\JsonResponse;

class BranchApiController extends BaseApiController
{
    /**
     * Ambil daftar semua cabang kantor untuk sinkronisasi mobile / geofence.
     */
    public function index(): JsonResponse
    {
        $branches = Branch::query()
            ->withCount(['departments', 'employees'])
            ->orderBy('name')
            ->get();

        return $this->sendSuccess(
            BranchResource::collection($branches),
            'Daftar cabang berhasil dimuat.'
        );
    }

    /**
     * Ambil detail satu cabang kantor.
     */
    public function show(Branch $branch): JsonResponse
    {
        $branch->loadCount(['departments', 'employees']);

        return $this->sendSuccess(
            new BranchResource($branch),
            'Detail cabang berhasil dimuat.'
        );
    }
}
