<?php

namespace App\Exports;

use App\Models\Payslip;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PayrollSummaryExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles
{
    public function __construct(
        public ?int $payrollBatchId = null,
        public ?int $month = null,
        public ?int $year = null
    ) {}

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function collection(): Collection
    {
        $query = Payslip::with([
            'employee.department:id,name',
            'employee.designation:id,name',
            'items',
            'payrollBatch',
        ]);

        if ($this->payrollBatchId) {
            $query->where('payroll_batch_id', $this->payrollBatchId);
        } elseif ($this->month && $this->year) {
            $query->whereHas('payrollBatch', function ($q) {
                $q->where('month', $this->month)->where('year', $this->year);
            });
        }

        $payslips = $query->get();

        return $payslips->map(function (Payslip $slip) {
            $items = $slip->items;

            $bpjsTk = $items->filter(fn ($i) => str_contains(strtolower($i->component_name), 'bpjs') && (str_contains(strtolower($i->component_name), 'ketenagakerjaan') || str_contains(strtolower($i->component_name), 'tk') || str_contains(strtolower($i->component_name), 'jht')))->sum('amount');
            $bpjsKes = $items->filter(fn ($i) => str_contains(strtolower($i->component_name), 'bpjs') && (str_contains(strtolower($i->component_name), 'kesehatan') || str_contains(strtolower($i->component_name), 'kes')))->sum('amount');
            $pph21 = $items->filter(fn ($i) => str_contains(strtolower($i->component_name), 'pph') || str_contains(strtolower($i->component_name), 'pajak'))->sum('amount');
            $kasbon = $items->filter(fn ($i) => str_contains(strtolower($i->component_name), 'kasbon') || str_contains(strtolower($i->component_name), 'pinjaman'))->sum('amount');

            return [
                'employee_code' => $slip->employee?->employee_code ?? '-',
                'name' => $slip->employee?->full_name ?? '-',
                'department' => $slip->employee?->department?->name ?? '-',
                'designation' => $slip->employee?->designation?->name ?? '-',
                'basic_salary' => (float) $slip->basic_salary,
                'total_allowances' => (float) $slip->total_allowances,
                'total_overtime' => (float) $slip->total_overtime_pay,
                'bpjs_tk' => (float) $bpjsTk,
                'bpjs_kes' => (float) $bpjsKes,
                'pph21' => (float) $pph21,
                'kasbon' => (float) $kasbon,
                'total_deductions' => (float) $slip->total_deductions,
                'net_salary' => (float) $slip->net_salary,
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
            'Departemen',
            'Jabatan',
            'Gaji Pokok (IDR)',
            'Tunjangan (IDR)',
            'Upah Lembur (IDR)',
            'Potongan BPJS TK (IDR)',
            'Potongan BPJS Kes (IDR)',
            'Potongan PPh 21 (IDR)',
            'Potongan Kasbon (IDR)',
            'Total Potongan (IDR)',
            'Take Home Pay (IDR)',
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
