<?php

namespace App\Http\Controllers;

use App\Services\Analytics\DashboardMetricService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AnalyticsDashboardController extends Controller
{
    /**
     * Display the visual executive analytics dashboard.
     */
    public function index(DashboardMetricService $metricService): View
    {
        $metrics = $metricService->getMetrics();

        return view('analytics.index', compact('metrics'));
    }

    /**
     * Refresh the cached dashboard metrics.
     */
    public function refresh(DashboardMetricService $metricService): RedirectResponse
    {
        $metricService->clearCache();
        $metricService->getMetrics(fresh: true);

        return redirect()->route('analytics.index')
            ->with('success', 'Metrik analitik eksekutif berhasil dimuat ulang secara real-time.');
    }
}
