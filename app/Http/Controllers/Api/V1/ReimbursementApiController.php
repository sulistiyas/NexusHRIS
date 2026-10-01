<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Reimbursement\SubmitReimbursementAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReimbursementRequest;
use App\Http\Resources\V1\ReimbursementCategoryResource;
use App\Http\Resources\V1\ReimbursementResource;
use App\Models\Reimbursement;
use App\Models\ReimbursementCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReimbursementApiController extends Controller
{
    /**
     * Get list of reimbursement claims for authenticated employee.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $employee = $request->user()->employee;

        $reimbursements = Reimbursement::with(['category', 'attachments'])
            ->where('employee_id', $employee?->id)
            ->latest('claim_date')
            ->paginate(15);

        return ReimbursementResource::collection($reimbursements);
    }

    /**
     * Submit a new reimbursement claim.
     */
    public function store(StoreReimbursementRequest $request, SubmitReimbursementAction $action): JsonResponse
    {
        $employee = $request->user()->employee;

        if (! $employee) {
            return response()->json([
                'message' => 'Profil karyawan tidak ditemukan untuk akun ini.',
            ], 422);
        }

        $files = $request->file('receipts') ?? $request->file('receipt') ?? $request->file('receipt_image');

        $reimbursement = $action->execute(
            employee: $employee,
            data: $request->validated(),
            files: $files,
            userId: $request->user()->id,
            ip: $request->ip(),
            userAgent: $request->userAgent()
        );

        $reimbursement->load(['category', 'attachments']);

        return (new ReimbursementResource($reimbursement))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Get detail of a single reimbursement claim.
     */
    public function show(Request $request, int $id): JsonResponse|ReimbursementResource
    {
        $employee = $request->user()->employee;

        $reimbursement = Reimbursement::with(['category', 'attachments', 'approvedBy'])
            ->findOrFail($id);

        if ($reimbursement->employee_id !== $employee?->id && ! $request->user()->hasRole(['super_admin', 'hr_admin', 'manager'])) {
            return response()->json([
                'message' => 'Anda tidak memiliki otorisasi untuk melihat klaim ini.',
            ], 403);
        }

        return new ReimbursementResource($reimbursement);
    }

    /**
     * Get available reimbursement categories.
     */
    public function categories(): AnonymousResourceCollection
    {
        $categories = ReimbursementCategory::orderBy('name')->get();

        return ReimbursementCategoryResource::collection($categories);
    }
}
