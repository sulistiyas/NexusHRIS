<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * Tampilkan rekap data presensi seluruh karyawan.
     */
    public function index(Request $request): View
    {
        $date = $request->query('date', Carbon::today()->toDateString());
        $search = $request->query('search');
        $branchId = $request->query('branch_id');
        $departmentId = $request->query('department_id');
        $status = $request->query('status');

        $attendances = Attendance::query()
            ->where('date', $date)
            ->with(['employee.user', 'employee.branch', 'employee.department', 'shift'])
            ->when($search, function ($query, $search): void {
                $query->whereHas('employee.user', function ($q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhereHas('employee', function ($q) use ($search): void {
                    $q->where('employee_code', 'like', "%{$search}%");
                });
            })
            ->when($branchId, function ($query, $branchId): void {
                $query->whereHas('employee', fn ($q) => $q->where('branch_id', $branchId));
            })
            ->when($departmentId, function ($query, $departmentId): void {
                $query->whereHas('employee', fn ($q) => $q->where('department_id', $departmentId));
            })
            ->when($status, function ($query, $status): void {
                $query->where('status', $status);
            })
            ->orderBy('clock_in')
            ->paginate(15)
            ->withQueryString();

        $branches = Branch::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('attendances.index', compact('attendances', 'date', 'search', 'branchId', 'departmentId', 'status', 'branches', 'departments'));
    }
}
