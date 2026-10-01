<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSalaryStructureRequest;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\SalaryStructure;
use App\Services\Payroll\BpjsCalculatorService;
use App\Services\Payroll\Pph21CalculatorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalaryStructureController extends Controller
{
    public function __construct(
        protected BpjsCalculatorService $bpjsCalculator,
        protected Pph21CalculatorService $pph21Calculator
    ) {}

    /**
     * Tampilkan daftar master struktur gaji karyawan.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $branchId = $request->query('branch_id');
        $departmentId = $request->query('department_id');
        $statusFilter = $request->query('status'); // 'configured' atau 'unconfigured'

        $employeesQuery = Employee::with(['user', 'branch', 'department', 'designation', 'salaryStructure'])
            ->when($search, function ($query, $term) {
                $query->where(function ($q) use ($term) {
                    $q->where('employee_code', 'like', "%{$term}%")
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"));
                });
            })
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId));

        if ($statusFilter === 'configured') {
            $employeesQuery->has('salaryStructure');
        } elseif ($statusFilter === 'unconfigured') {
            $employeesQuery->doesntHave('salaryStructure');
        }

        $employees = $employeesQuery->orderBy('employee_code', 'asc')->paginate(15)->withQueryString();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('salary-structures.index', compact('employees', 'branches', 'departments', 'search', 'branchId', 'departmentId', 'statusFilter'));
    }

    /**
     * Tampilkan form pengaturan gaji karyawan tertentu.
     */
    public function edit(Employee $employee): View
    {
        $employee->load(['user', 'branch', 'department', 'designation', 'salaryStructure']);
        $salaryStructure = $employee->salaryStructure ?? new SalaryStructure;

        // Hitung estimasi kalkulasi awal untuk ditampilkan di kartu ringkasan
        $basic = (float) ($salaryStructure->basic_salary ?? 0);
        $fixed = (float) ($salaryStructure->fixed_allowance ?? 0);
        $transport = (float) ($salaryStructure->transport_allowance ?? 0);
        $meal = (float) ($salaryStructure->meal_allowance ?? 0);

        $gross = $basic + $fixed + $transport + $meal;
        $bpjs = $this->bpjsCalculator->calculate($basic, $fixed);
        $tax = $this->pph21Calculator->calculate($gross, 'TK/0');

        $deductions = $bpjs['employee']['total_deduction'] + $tax['tax_amount'];
        $estimatedThp = max(0, $gross - $deductions);

        return view('salary-structures.edit', compact(
            'employee',
            'salaryStructure',
            'gross',
            'bpjs',
            'tax',
            'deductions',
            'estimatedThp'
        ));
    }

    /**
     * Simpan pembaruan struktur gaji karyawan.
     */
    public function update(UpdateSalaryStructureRequest $request, Employee $employee): RedirectResponse
    {
        $basicSalary = (float) $request->validated('basic_salary');
        $fixedAllowance = (float) ($request->validated('fixed_allowance') ?? 0);
        $transportAllowance = (float) ($request->validated('transport_allowance') ?? 0);
        $mealAllowance = (float) ($request->validated('meal_allowance') ?? 0);
        $ptkpStatus = $request->validated('ptkp_status') ?? 'TK/0';

        // Hitung iuran BPJS dan PPh 21 TER secara otomatis berdasarkan regulasi
        $bpjsResult = $this->bpjsCalculator->calculate($basicSalary, $fixedAllowance);
        $bpjsTkDeduction = $bpjsResult['employee']['total_bpjs_tk'];
        $bpjsKesDeduction = $bpjsResult['employee']['bpjs_kes'];

        $totalGross = $basicSalary + $fixedAllowance + $transportAllowance + $mealAllowance;
        $taxResult = $this->pph21Calculator->calculate($totalGross, $ptkpStatus);
        $pph21Estimated = $taxResult['tax_amount'];

        SalaryStructure::updateOrCreate(
            ['employee_id' => $employee->id],
            [
                'basic_salary' => $basicSalary,
                'fixed_allowance' => $fixedAllowance,
                'transport_allowance' => $transportAllowance,
                'meal_allowance' => $mealAllowance,
                'bpjs_tk_deduction' => $bpjsTkDeduction,
                'bpjs_kes_deduction' => $bpjsKesDeduction,
                'pph21_estimated' => $pph21Estimated,
            ]
        );

        return redirect()->route('salary-structures.index')
            ->with('success', "Struktur gaji karyawan {$employee->user->name} ({$employee->employee_code}) berhasil diperbarui.");
    }
}
