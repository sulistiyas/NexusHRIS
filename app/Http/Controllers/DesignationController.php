<?php

namespace App\Http\Controllers;

use App\Http\Requests\Designation\StoreDesignationRequest;
use App\Http\Requests\Designation\UpdateDesignationRequest;
use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DesignationController extends Controller
{
    /**
     * Tampilkan daftar jabatan.
     */
    public function index(Request $request): View
    {
        $departmentId = $request->query('department_id');
        $search = $request->query('search');

        $departments = Department::with('branch')->orderBy('name')->get();

        $designations = Designation::query()
            ->when($departmentId, fn ($query) => $query->where('department_id', $departmentId))
            ->when($search, function ($query, $search): void {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('grade_level', 'like', "%{$search}%");
            })
            ->with(['department.branch'])
            ->withCount('employees')
            ->orderBy('title')
            ->paginate(15)
            ->withQueryString();

        return view('designations.index', compact('designations', 'departments', 'departmentId', 'search'));
    }

    /**
     * Formulir tambah jabatan baru.
     */
    public function create(): View
    {
        $departments = Department::with('branch')->orderBy('name')->get();

        return view('designations.create', compact('departments'));
    }

    /**
     * Simpan data jabatan baru.
     */
    public function store(StoreDesignationRequest $request): RedirectResponse
    {
        $designation = Designation::create($request->validated());

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'event' => 'designation_created',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => $designation->only(['department_id', 'title', 'grade_level']),
        ]);

        return redirect()->route('designations.index')
            ->with('success', "Jabatan {$designation->title} berhasil ditambahkan.");
    }

    /**
     * Formulir ubah jabatan.
     */
    public function edit(Designation $designation): View
    {
        $departments = Department::with('branch')->orderBy('name')->get();

        return view('designations.edit', compact('designation', 'departments'));
    }

    /**
     * Perbarui data jabatan.
     */
    public function update(UpdateDesignationRequest $request, Designation $designation): RedirectResponse
    {
        $oldValues = $designation->only(['department_id', 'title', 'grade_level']);

        $designation->update($request->validated());

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'event' => 'designation_updated',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_values' => $oldValues,
            'new_values' => $designation->only(['department_id', 'title', 'grade_level']),
        ]);

        return redirect()->route('designations.index')
            ->with('success', "Jabatan {$designation->title} berhasil diperbarui.");
    }

    /**
     * Hapus jabatan jika belum terhubung dengan karyawan.
     */
    public function destroy(Request $request, Designation $designation): RedirectResponse
    {
        if ($designation->employees()->exists()) {
            return back()->with('error', 'Jabatan tidak dapat dihapus karena sedang ditempati oleh karyawan.');
        }

        $title = $designation->title;
        $designation->delete();

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'event' => 'designation_deleted',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_values' => ['title' => $title],
        ]);

        return redirect()->route('designations.index')
            ->with('success', "Jabatan {$title} berhasil dihapus.");
    }

    /**
     * Endpoint AJAX untuk cascading dropdown jabatan berdasarkan departemen.
     */
    public function byDepartment(Department $department): JsonResponse
    {
        $designations = $department->designations()->orderBy('title')->get(['id', 'title', 'grade_level']);

        return response()->json($designations);
    }
}
