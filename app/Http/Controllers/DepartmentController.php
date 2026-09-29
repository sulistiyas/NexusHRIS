<?php

namespace App\Http\Controllers;

use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    /**
     * Tampilkan daftar departemen.
     */
    public function index(Request $request): View
    {
        $branchId = $request->query('branch_id');
        $search = $request->query('search');

        $branches = Branch::orderBy('name')->get();

        $departments = Department::query()
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when($search, function ($query, $search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->with(['branch', 'manager.user'])
            ->withCount(['designations', 'employees'])
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('departments.index', compact('departments', 'branches', 'branchId', 'search'));
    }

    /**
     * Formulir tambah departemen.
     */
    public function create(): View
    {
        $branches = Branch::orderBy('name')->get();
        $potentialManagers = Employee::with('user')->get();

        return view('departments.create', compact('branches', 'potentialManagers'));
    }

    /**
     * Simpan data departemen baru.
     */
    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        $department = Department::create($request->validated());

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'event' => 'department_created',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => $department->only(['branch_id', 'code', 'name', 'manager_id']),
        ]);

        return redirect()->route('departments.index')
            ->with('success', "Departemen {$department->name} berhasil ditambahkan.");
    }

    /**
     * Formulir ubah departemen.
     */
    public function edit(Department $department): View
    {
        $branches = Branch::orderBy('name')->get();
        $potentialManagers = Employee::with('user')->get();

        return view('departments.edit', compact('department', 'branches', 'potentialManagers'));
    }

    /**
     * Perbarui data departemen.
     */
    public function update(UpdateDepartmentRequest $request, Department $department): RedirectResponse
    {
        $oldValues = $department->only(['branch_id', 'code', 'name', 'manager_id']);

        $department->update($request->validated());

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'event' => 'department_updated',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_values' => $oldValues,
            'new_values' => $department->only(['branch_id', 'code', 'name', 'manager_id']),
        ]);

        return redirect()->route('departments.index')
            ->with('success', "Departemen {$department->name} berhasil diperbarui.");
    }

    /**
     * Hapus departemen jika belum berelasi dengan karyawan/jabatan.
     */
    public function destroy(Request $request, Department $department): RedirectResponse
    {
        if ($department->employees()->exists()) {
            return back()->with('error', 'Departemen tidak dapat dihapus karena masih memiliki karyawan terikat.');
        }

        if ($department->designations()->exists()) {
            return back()->with('error', 'Departemen tidak dapat dihapus karena masih memiliki jabatan terikat.');
        }

        $name = $department->name;
        $department->delete();

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'event' => 'department_deleted',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_values' => ['name' => $name],
        ]);

        return redirect()->route('departments.index')
            ->with('success', "Departemen {$name} berhasil dihapus.");
    }

    /**
     * Endpoint AJAX untuk cascading dropdown departemen berdasarkan cabang.
     */
    public function byBranch(Branch $branch): JsonResponse
    {
        $departments = $branch->departments()->orderBy('name')->get(['id', 'name', 'code']);

        return response()->json($departments);
    }
}
