<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCashAdvanceRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna berhak mengajukan kasbon.
     */
    public function authorize(): bool
    {
        return $this->user()->employee !== null;
    }

    /**
     * Aturan validasi pengajuan kasbon.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:50000', 'max:50000000'],
            'installment_months' => ['required', 'integer', 'min:1', 'max:12'],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
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
            'amount' => 'Nominal Kasbon',
            'installment_months' => 'Jangka Waktu Cicilan (Bulan)',
            'reason' => 'Alasan Pengajuan',
        ];
    }
}
