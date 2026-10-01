<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Analytics\DashboardMetricService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardApiController extends Controller
{
    /**
     * Get quick executive metric summary for mobile apps (US-26).
     */
    public function summary(Request $request, DashboardMetricService $metricService): JsonResponse
    {
        $metrics = $metricService->getMetrics();

        return response()->json([
            'status' => 'success',
            'data' => $metrics,
        ]);
    }
}
