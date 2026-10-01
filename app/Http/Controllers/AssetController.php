<?php

namespace App\Http\Controllers;

use App\Actions\Asset\AssignAssetAction;
use App\Actions\Asset\ReturnAssetAction;
use App\Http\Requests\AssignAssetRequest;
use App\Http\Requests\ReturnAssetRequest;
use App\Http\Requests\StoreAssetRequest;
use App\Http\Requests\UpdateAssetRequest;
use App\Models\ActivityLog;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetCategory;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetController extends Controller
{
    /**
     * Display a listing of corporate assets.
     */
    public function index(Request $request): View
    {
        $status = $request->input('status', 'ALL');
        $categoryId = $request->input('category_id');
        $search = $request->input('search');

        $query = Asset::with([
            'category',
            'assignments' => function ($q) {
                $q->whereNull('returned_date')->with('employee');
            },
        ])->latest('id');

        if ($status !== 'ALL') {
            $query->where('status', $status);
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('asset_tag', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        $assets = $query->paginate(15)->withQueryString();
        $categories = AssetCategory::orderBy('name')->get();

        $stats = [
            'total' => Asset::count(),
            'available' => Asset::where('status', 'AVAILABLE')->count(),
            'assigned' => Asset::where('status', 'ASSIGNED')->count(),
            'maintenance' => Asset::where('status', 'UNDER_MAINTENANCE')->count(),
            'disposed' => Asset::where('status', 'DISPOSED')->count(),
        ];

        return view('assets.index', compact('assets', 'categories', 'status', 'categoryId', 'search', 'stats'));
    }

    /**
     * Show the form for creating a new asset.
     */
    public function create(): View
    {
        $categories = AssetCategory::orderBy('name')->get();

        return view('assets.create', compact('categories'));
    }

    /**
     * Store a newly created asset in storage.
     */
    public function store(StoreAssetRequest $request): RedirectResponse
    {
        $asset = Asset::create($request->validated());

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'event' => 'asset_created',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => [
                'asset_tag' => $asset->asset_tag,
                'name' => $asset->name,
                'status' => $asset->status,
            ],
        ]);

        return redirect()->route('assets.index')
            ->with('success', "Aset {$asset->name} ({$asset->asset_tag}) berhasil ditambahkan ke inventaris.");
    }

    /**
     * Display the specified asset.
     */
    public function show(Asset $asset): View
    {
        $asset->load([
            'category',
            'assignments.employee.department',
            'assignments.employee.designation',
        ]);

        $activeEmployees = Employee::with('user', 'department')
            ->whereHas('user', fn ($q) => $q->where('is_active', true))
            ->get()
            ->sortBy(fn ($emp) => $emp->user?->name)
            ->values();

        return view('assets.show', compact('asset', 'activeEmployees'));
    }

    /**
     * Show the form for editing the specified asset.
     */
    public function edit(Asset $asset): View
    {
        $categories = AssetCategory::orderBy('name')->get();

        return view('assets.edit', compact('asset', 'categories'));
    }

    /**
     * Update the specified asset in storage.
     */
    public function update(UpdateAssetRequest $request, Asset $asset): RedirectResponse
    {
        $asset->update($request->validated());

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'event' => 'asset_updated',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => [
                'asset_tag' => $asset->asset_tag,
                'name' => $asset->name,
                'status' => $asset->status,
            ],
        ]);

        return redirect()->route('assets.index')
            ->with('success', "Data aset {$asset->name} ({$asset->asset_tag}) berhasil diperbarui.");
    }

    /**
     * Remove the specified asset from storage.
     */
    public function destroy(Request $request, Asset $asset): RedirectResponse
    {
        if ($asset->status === 'ASSIGNED') {
            return back()->with('error', 'Aset yang sedang dipinjamkan tidak dapat dihapus. Silakan lakukan proses pengembalian terlebih dahulu.');
        }

        $tag = $asset->asset_tag;
        $name = $asset->name;

        $asset->delete();

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'event' => 'asset_deleted',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => ['asset_tag' => $tag, 'name' => $name],
        ]);

        return redirect()->route('assets.index')
            ->with('success', "Aset {$name} ({$tag}) telah dihapus dari sistem.");
    }

    /**
     * Assign asset to an employee.
     */
    public function assign(AssignAssetRequest $request, Asset $asset, AssignAssetAction $action): RedirectResponse
    {
        $employee = Employee::findOrFail($request->validated('employee_id'));

        try {
            $action->execute(
                asset: $asset,
                employee: $employee,
                data: $request->validated(),
                userId: $request->user()->id,
                ip: $request->ip(),
                userAgent: $request->userAgent()
            );

            return redirect()->route('assets.show', $asset)
                ->with('success', "Aset {$asset->name} berhasil diserahkan kepada {$employee->full_name}.");
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Return an assigned asset.
     */
    public function returnAsset(ReturnAssetRequest $request, AssetAssignment $assignment, ReturnAssetAction $action): RedirectResponse
    {
        try {
            $action->execute(
                assignment: $assignment,
                data: $request->validated(),
                userId: $request->user()->id,
                ip: $request->ip(),
                userAgent: $request->userAgent()
            );

            return redirect()->route('assets.show', $assignment->asset_id)
                ->with('success', "Aset {$assignment->asset->name} berhasil dicatat pengembaliannya.");
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
