<?php

namespace App\Http\Controllers;

use App\Http\Requests\Leave\StoreLeaveRequest;
use App\Models\ActivityLog;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Services\LeaveService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EssLeaveController extends Controller
{
    public function __construct(protected LeaveService $leaveService) {}

    /**
     * Dashboard ringkasan saldo dan daftar pengajuan cuti pribadi.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return redirect()->route('dashboard')->with('error', 'Akun Anda tidak terhubung dengan profil karyawan.');
        }

        $year = (int) Carbon::now()->year;
        $leaveTypes = LeaveType::all();

        // Inisialisasi saldo cuti tahun ini
        $balances = $leaveTypes->map(function ($type) use ($employee, $year) {
            return $this->leaveService->getOrCreateBalance($employee, $type, $year);
        });

        // Riwayat pengajuan cuti pribadi
        $leaveRequests = LeaveRequest::query()
            ->where('employee_id', $employee->id)
            ->with(['leaveType', 'approvals.approver.user'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('ess.leaves.index', compact('employee', 'balances', 'leaveRequests', 'year'));
    }

    /**
     * Tampilkan formulir pengajuan cuti.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return redirect()->route('dashboard');
        }

        $leaveTypes = LeaveType::all();

        return view('ess.leaves.create', compact('employee', 'leaveTypes'));
    }

    /**
     * Simpan pengajuan cuti baru.
     */
    public function store(StoreLeaveRequest $request): RedirectResponse
    {
        $employee = $request->user()->employee;

        $leaveRequest = $this->leaveService->submitLeaveRequest(
            $employee,
            $request->validated(),
            $request->file('attachment')
        );

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'event' => 'leave_request_submitted',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => [
                'leave_request_id' => $leaveRequest->id,
                'leave_type' => $leaveRequest->leaveType->name,
                'total_days' => $leaveRequest->total_days,
            ],
        ]);

        return redirect()->route('ess.leaves.index')
            ->with('success', "Permohonan cuti selama {$leaveRequest->total_days} hari berhasil diajukan.");
    }

    /**
     * Tampilkan rincian pengajuan cuti dan linimasa approval.
     */
    public function show(LeaveRequest $leaf): View
    {
        $leaf->load(['leaveType', 'employee.user', 'approvals.approver.user']);

        return view('ess.leaves.show', ['leaveRequest' => $leaf]);
    }
}
