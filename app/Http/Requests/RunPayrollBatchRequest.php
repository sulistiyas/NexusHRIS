<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RunPayrollBatchRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna berhak memproses batch payroll.
     */
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['super_admin', 'hr_admin']);
    }

    /**
     * Aturan validasi pembuatan batch payroll.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'min:2020', 'max:2050'],
            'cut_off_start' => ['required', 'date'],
            'cut_off_end' => ['required', 'date', 'after_or_equal:cut_off_start'],
            'payment_date' => ['required', 'date'],
        ];
    }

    /**
     * Kustomisasi nama atribut.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'month' => 'Bulan Periode',
            'year' => 'Tahun Periode',
            'cut_off_start' => 'Tanggal Awal Cut-off',
            'cut_off_end' => 'Tanggal Akhir Cut-off',
            'payment_date' => 'Tanggal Pembayaran Transfer',
        ];
    }
}
