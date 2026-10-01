<?php

namespace App\Exports;

use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MonthlyAttendanceExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles
{
    public function __construct(
        public int $month,
        public int $year,
        public ?int $branchId = null,
        public ?int $departmentId = null
    ) {}

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function collection(): Collection
    {
        $startDate = Carbon::create($this->year, $this->month, 1)->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::create($this->year, $this->month, 1)->endOfMonth()->format('Y-m-d');

        $query = Employee::with([
            'branch:id,name',
            'department:id,name',
            'attendances' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate]);
            },
        ])->where('employment_status', 'ACTIVE');

        if ($this->branchId) {
            $query->where('branch_id', $this->branchId);
        }

        if ($this->departmentId) {
            $query->where('department_id', $this->departmentId);
        }

        $employees = $query->orderBy('employee_code')->get();

        return $employees->map(function (Employee $employee) {
            $attendances = $employee->attendances;

            $hadir = $attendances->whereIn('status', ['PRESENT', 'LATE'])->count();
            $terlambatMenit = $attendances->sum('late_minutes');
            $cutiIzin = $attendances->whereIn('status', ['LEAVE', 'PERMISSION'])->count();
            $sakit = $attendances->where('status', 'SICK')->count();
            $alpa = $attendances->where('status', 'ABSENT')->count();

            return [
                'employee_code' => $employee->employee_code,
                'name' => $employee->full_name,
                'branch' => $employee->branch?->name ?? '-',
                'department' => $employee->department?->name ?? '-',
                'hadir' => $hadir,
                'terlambat_menit' => $terlambatMenit,
                'cuti_izin' => $cutiIzin,
                'sakit' => $sakit,
                'alpa' => $alpa,
            ];
        });
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'NIP / Kode Karyawan',
            'Nama Lengkap',
            'Kantor Cabang',
            'Departemen',
            'Jumlah Hadir',
            'Total Terlambat (Menit)',
            'Cuti / Izin',
            'Sakit',
            'Mangkir / Alpa',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0B1E36'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}
