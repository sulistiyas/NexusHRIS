<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSalaryStructureRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna berhak melakukan request ini.
     */
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['super_admin', 'hr_admin']);
    }

    /**
     * Aturan validasi komponen gaji.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'fixed_allowance' => ['nullable', 'numeric', 'min:0'],
            'transport_allowance' => ['nullable', 'numeric', 'min:0'],
            'meal_allowance' => ['nullable', 'numeric', 'min:0'],
            'ptkp_status' => ['nullable', 'string', 'in:TK/0,TK/1,TK/2,TK/3,K/0,K/1,K/2,K/3'],
        ];
    }

    /**
     * Kustomisasi nama atribut pesan validasi.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'basic_salary' => 'Gaji Pokok',
            'fixed_allowance' => 'Tunjangan Tetap',
            'transport_allowance' => 'Tunjangan Transportasi',
            'meal_allowance' => 'Tunjangan Makan',
            'ptkp_status' => 'Status PTKP Pajak',
        ];
    }
}
