<?php

namespace App\Http\Controllers;

use App\Actions\Employee\CreateEmployeeAction;
use App\Actions\Employee\GenerateEmployeeCodeAction;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function __construct(
        protected CreateEmployeeAction $createEmployeeAction,
        protected GenerateEmployeeCodeAction $generateCodeAction
    ) {}

    /**
     * Tampilkan daftar karyawan dengan filter & pagination.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $branchId = $request->query('branch_id');
        $departmentId = $request->query('department_id');
        $status = $request->query('status');

        $branches = Branch::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        $employees = Employee::query()
            ->with(['user', 'branch', 'department', 'designation', 'manager.user'])
            ->when($search, function ($query, $search): void {
                $query->where('employee_code', 'like', "%{$search}%")
                    ->orWhere('nik_ktp', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            })
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))
            ->when($status, fn ($q) => $q->where('employment_status', $status))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('employees.index', compact('employees', 'branches', 'departments', 'search', 'branchId', 'departmentId', 'status'));
    }

    /**
     * Tampilkan formulir input karyawan baru.
     */
    public function create(): View
    {
        $branches = Branch::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $designations = Designation::orderBy('title')->get();
        $managers = Employee::with('user')->orderBy('id')->get();
        $suggestedCode = $this->generateCodeAction->execute();

        return view('employees.create', compact('branches', 'departments', 'designations', 'managers', 'suggestedCode'));
    }

    /**
     * Simpan karyawan baru & buat akun otomatis.
     */
    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $employee = $this->createEmployeeAction->execute(
            $request->validated(),
            $request->user()
        );

        return redirect()->route('employees.index')
            ->with('success', "Karyawan {$employee->user->name} ({$employee->employee_code}) berhasil didaftarkan.");
    }

    /**
     * Tampilkan profil detail lengkap karyawan.
     */
    public function show(Employee $employee): View
    {
        $employee->load([
            'user.roles',
            'branch',
            'department',
            'designation',
            'manager.user',
            'documents',
            'emergencyContacts',
        ]);

        return view('employees.show', compact('employee'));
    }

    /**
     * Tampilkan formulir ubah data karyawan.
     */
    public function edit(Employee $employee): View
    {
        $employee->load(['user.roles']);
        $branches = Branch::orderBy('name')->get();
        $departments = Department::where('branch_id', $employee->branch_id)->orderBy('name')->get();
        $designations = Designation::where('department_id', $employee->department_id)->orderBy('title')->get();
        $managers = Employee::where('id', '!=', $employee->id)->with('user')->get();

        return view('employees.edit', compact('employee', 'branches', 'departments', 'designations', 'managers'));
    }

    /**
     * Perbarui data karyawan.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $employee, $request): void {
            // Perbarui akun user
            $employee->user->update([
                'name' => $data['name'],
                'email' => $data['email'],
            ]);

            if (! empty($data['role'])) {
                $employee->user->syncRoles([$data['role']]);
            }

            // Perbarui data profil karyawan
            $employee->update([
                'employee_code' => $data['employee_code'],
                'nik_ktp' => $data['nik_ktp'],
                'npwp' => $data['npwp'] ?? null,
                'bpjs_tk' => $data['bpjs_tk'] ?? null,
                'bpjs_kes' => $data['bpjs_kes'] ?? null,
                'branch_id' => $data['branch_id'],
                'department_id' => $data['department_id'],
                'designation_id' => $data['designation_id'],
                'manager_id' => $data['manager_id'] ?? null,
                'gender' => $data['gender'] ?? null,
                'birth_date' => $data['birth_date'] ?? null,
                'birth_place' => $data['birth_place'] ?? null,
                'phone' => $data['phone'] ?? null,
                'employment_status' => $data['employment_status'],
                'join_date' => $data['join_date'],
                'contract_end_date' => $data['contract_end_date'] ?? null,
                'bank_name' => $data['bank_name'] ?? null,
                'bank_account_no' => $data['bank_account_no'] ?? null,
                'bank_account_holder' => $data['bank_account_holder'] ?? null,
            ]);

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'event' => 'employee_updated',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_values' => ['employee_code' => $employee->employee_code, 'name' => $employee->user->name],
            ]);
        });

        return redirect()->route('employees.index')
            ->with('success', "Data karyawan {$employee->user->name} berhasil diperbarui.");
    }

    /**
     * Hapus karyawan & akun terkait.
     */
    public function destroy(Request $request, Employee $employee): RedirectResponse
    {
        $name = $employee->user->name;

        DB::transaction(function () use ($employee, $request, $name): void {
            $user = $employee->user;
            $employee->delete();
            $user->delete();

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'event' => 'employee_deleted',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'old_values' => ['name' => $name],
            ]);
        });

        return redirect()->route('employees.index')
            ->with('success', "Data karyawan {$name} berhasil dihapus.");
    }
}
