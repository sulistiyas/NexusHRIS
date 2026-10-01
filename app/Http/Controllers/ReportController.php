<?php

namespace App\Http\Controllers;

use App\Exports\MonthlyAttendanceExport;
use App\Exports\PayrollSummaryExport;
use App\Models\Branch;
use App\Models\Department;
use App\Models\PayrollBatch;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    /**
     * Display reports dashboard with export forms.
     */
    public function index(): View
    {
        $branches = Branch::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $payrollBatches = PayrollBatch::latest('year')->latest('month')->get();

        $currentMonth = (int) now()->format('m');
        $currentYear = (int) now()->format('Y');

        return view('reports.index', compact(
            'branches',
            'departments',
            'payrollBatches',
            'currentMonth',
            'currentYear'
        ));
    }

    /**
     * Export monthly attendance summary to Excel (.xlsx).
     */
    public function exportAttendance(Request $request): BinaryFileResponse
    {
        $validated = $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2020,2035'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
        ]);

        $month = (int) $validated['month'];
        $year = (int) $validated['year'];
        $branchId = ! empty($validated['branch_id']) ? (int) $validated['branch_id'] : null;
        $departmentId = ! empty($validated['department_id']) ? (int) $validated['department_id'] : null;

        $fileName = sprintf('Rekap_Presensi_%04d_%02d.xlsx', $year, $month);

        return Excel::download(
            new MonthlyAttendanceExport($month, $year, $branchId, $departmentId),
            $fileName
        );
    }

    /**
     * Export payroll summary to Excel (.xlsx).
     */
    public function exportPayroll(Request $request): BinaryFileResponse
    {
        $validated = $request->validate([
            'payroll_batch_id' => ['nullable', 'integer', 'exists:payroll_batches,id'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2020,2035'],
        ]);

        $batchId = ! empty($validated['payroll_batch_id']) ? (int) $validated['payroll_batch_id'] : null;
        $month = ! empty($validated['month']) ? (int) $validated['month'] : (int) now()->format('m');
        $year = ! empty($validated['year']) ? (int) $validated['year'] : (int) now()->format('Y');

        $fileName = $batchId
            ? sprintf('Rekap_Penggajian_Batch_%d.xlsx', $batchId)
            : sprintf('Rekap_Penggajian_%04d_%02d.xlsx', $year, $month);

        return Excel::download(
            new PayrollSummaryExport($batchId, $month, $year),
            $fileName
        );
    }
}
