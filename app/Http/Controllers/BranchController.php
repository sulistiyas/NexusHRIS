<?php

namespace App\Http\Controllers;

use App\Http\Requests\Branch\StoreBranchRequest;
use App\Http\Requests\Branch\UpdateBranchRequest;
use App\Models\ActivityLog;
use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchController extends Controller
{
    /**
     * Tampilkan daftar cabang kantor.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $branches = Branch::query()
            ->when($search, function ($query, $search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            })
            ->withCount(['departments', 'employees'])
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('branches.index', compact('branches', 'search'));
    }

    /**
     * Tampilkan formulir tambah cabang baru.
     */
    public function create(): View
    {
        return view('branches.create');
    }

    /**
     * Simpan cabang baru ke database.
     */
    public function store(StoreBranchRequest $request): RedirectResponse
    {
        $branch = Branch::create($request->validated());

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'event' => 'branch_created',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => $branch->only(['code', 'name', 'latitude', 'longitude', 'radius_meters']),
        ]);

        return redirect()->route('branches.index')
            ->with('success', "Cabang {$branch->name} ({$branch->code}) berhasil ditambahkan.");
    }

    /**
     * Tampilkan rincian detail cabang.
     */
    public function show(Branch $branch): View
    {
        $branch->load([
            'departments' => fn ($query) => $query->withCount('employees')->with('designations'),
        ])->loadCount('employees');

        return view('branches.show', compact('branch'));
    }

    /**
     * Tampilkan formulir ubah cabang.
     */
    public function edit(Branch $branch): View
    {
        return view('branches.edit', compact('branch'));
    }

    /**
     * Perbarui data cabang.
     */
    public function update(UpdateBranchRequest $request, Branch $branch): RedirectResponse
    {
        $oldValues = $branch->only(['code', 'name', 'latitude', 'longitude', 'radius_meters', 'timezone']);

        $branch->update($request->validated());

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'event' => 'branch_updated',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_values' => $oldValues,
            'new_values' => $branch->only(['code', 'name', 'latitude', 'longitude', 'radius_meters', 'timezone']),
        ]);

        return redirect()->route('branches.index')
            ->with('success', "Data cabang {$branch->name} berhasil diperbarui.");
    }

    /**
     * Hapus cabang jika belum memiliki relasi karyawan/departemen.
     */
    public function destroy(Request $request, Branch $branch): RedirectResponse
    {
        if ($branch->employees()->exists()) {
            return back()->with('error', 'Cabang tidak dapat dihapus karena masih memiliki data karyawan aktif.');
        }

        if ($branch->departments()->exists()) {
            return back()->with('error', 'Cabang tidak dapat dihapus karena masih memiliki departemen terdaftar.');
        }

        $branchName = $branch->name;
        $branch->delete();

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'event' => 'branch_deleted',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_values' => ['name' => $branchName],
        ]);

        return redirect()->route('branches.index')
            ->with('success', "Cabang {$branchName} berhasil dihapus.");
    }
}
