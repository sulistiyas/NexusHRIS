<?php

namespace App\Services\Analytics;

use App\Models\Asset;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\PayrollBatch;
use App\Models\Reimbursement;
use Illuminate\Support\Facades\Cache;

class DashboardMetricService
{
    public const CACHE_KEY = 'executive_dashboard_metrics';

    public const CACHE_TTL_MINUTES = 15;

    /**
     * Get dashboard metrics, from cache or compiled fresh.
     *
     * @return array<string, mixed>
     */
    public function getMetrics(bool $fresh = false): array
    {
        if ($fresh) {
            $this->clearCache();
        }

        return Cache::remember(self::CACHE_KEY, now()->addMinutes(self::CACHE_TTL_MINUTES), function (): array {
            return $this->compileMetrics();
        });
    }

    /**
     * Clear the dashboard metrics cache.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Compile all real-time metrics across modules.
     *
     * @return array<string, mixed>
     */
    public function compileMetrics(): array
    {
        $today = now()->format('Y-m-d');
        $currentMonth = (int) now()->format('m');
        $currentYear = (int) now()->format('Y');

        // 1. Headcount & Employee Stats
        $totalEmployees = Employee::count();
        $activeEmployees = Employee::whereHas('user', function ($q) {
            $q->where('is_active', true);
        })->count();
        $inactiveEmployees = $totalEmployees - $activeEmployees;

        $byDepartment = Department::withCount(['employees' => function ($q) {
            $q->whereHas('user', fn ($uq) => $uq->where('is_active', true));
        }])->get()->map(fn ($dept) => [
            'name' => $dept->name,
            'count' => $dept->employees_count,
        ])->values()->all();

        $byBranch = Branch::withCount(['employees' => function ($q) {
            $q->whereHas('user', fn ($uq) => $uq->where('is_active', true));
        }])->get()->map(fn ($branch) => [
            'name' => $branch->name,
            'count' => $branch->employees_count,
        ])->values()->all();

        // 2. Attendance Ratios Today
        $todayAttendances = Attendance::where('date', $today)->get();
        $presentOntime = $todayAttendances->where('status', 'PRESENT')->where('late_minutes', 0)->count();
        $presentLate = $todayAttendances->filter(fn ($att) => $att->status === 'LATE' || ($att->status === 'PRESENT' && $att->late_minutes > 0))->count();
        $leavePermission = $todayAttendances->whereIn('status', ['LEAVE', 'PERMISSION'])->count();
        $sick = $todayAttendances->where('status', 'SICK')->count();
        $absent = $todayAttendances->where('status', 'ABSENT')->count();
        $totalPresent = $presentOntime + $presentLate;
        $attendanceRate = $activeEmployees > 0 ? round(($totalPresent / $activeEmployees) * 100, 1) : 0;

        // 3. Payroll Trend (6 Months)
        $payrollTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $targetDate = now()->subMonths($i);
            $m = (int) $targetDate->format('m');
            $y = (int) $targetDate->format('Y');
            $label = $targetDate->translatedFormat('M Y');

            $batches = PayrollBatch::where('month', $m)->where('year', $y)->get();
            $gross = (float) $batches->sum('total_gross');
            $net = (float) $batches->sum('total_net');
            $deductions = (float) $batches->sum('total_deductions');

            $payrollTrend[] = [
                'month' => $m,
                'year' => $y,
                'label' => $label,
                'gross' => $gross,
                'net' => $net,
                'deductions' => $deductions,
            ];
        }

        // 4. Reimbursement Summary
        $reimbursementPendingCount = Reimbursement::where('status', 'PENDING')->count();
        $reimbursementPendingAmount = (float) Reimbursement::where('status', 'PENDING')->sum('total_amount');
        $reimbursementApprovedCount = Reimbursement::where('status', 'APPROVED')->count();
        $reimbursementApprovedAmount = (float) Reimbursement::where('status', 'APPROVED')->sum('total_amount');

        $disbursedThisMonth = Reimbursement::where('status', 'DISBURSED')
            ->whereMonth('disbursed_at', $currentMonth)
            ->whereYear('disbursed_at', $currentYear);
        $reimbursementDisbursedMonthCount = $disbursedThisMonth->count();
        $reimbursementDisbursedMonthAmount = (float) $disbursedThisMonth->sum('total_amount');

        // 5. Assets Summary
        $totalAssets = Asset::count();
        $availableAssets = Asset::where('status', 'AVAILABLE')->count();
        $assignedAssets = Asset::where('status', 'ASSIGNED')->count();
        $maintenanceAssets = Asset::where('status', 'UNDER_MAINTENANCE')->count();
        $disposedAssets = Asset::where('status', 'DISPOSED')->count();

        return [
            'compiled_at' => now()->toISOString(),
            'headcount' => [
                'total' => $totalEmployees,
                'active' => $activeEmployees,
                'inactive' => $inactiveEmployees,
                'by_department' => $byDepartment,
                'by_branch' => $byBranch,
            ],
            'today_attendance' => [
                'date' => $today,
                'total_expected' => $activeEmployees,
                'total_present' => $totalPresent,
                'present_ontime' => $presentOntime,
                'present_late' => $presentLate,
                'leave_permission' => $leavePermission,
                'sick' => $sick,
                'absent' => $absent,
                'attendance_rate' => $attendanceRate,
            ],
            'payroll_trends' => $payrollTrend,
            'reimbursement' => [
                'pending_count' => $reimbursementPendingCount,
                'pending_amount' => $reimbursementPendingAmount,
                'approved_count' => $reimbursementApprovedCount,
                'approved_amount' => $reimbursementApprovedAmount,
                'disbursed_month_count' => $reimbursementDisbursedMonthCount,
                'disbursed_month_amount' => $reimbursementDisbursedMonthAmount,
            ],
            'assets' => [
                'total' => $totalAssets,
                'available' => $availableAssets,
                'assigned' => $assignedAssets,
                'under_maintenance' => $maintenanceAssets,
                'disposed' => $disposedAssets,
            ],
        ];
    }
}
