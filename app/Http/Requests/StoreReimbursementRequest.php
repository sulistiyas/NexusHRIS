<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreReimbursementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->employee !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:reimbursement_categories,id'],
            'claim_date' => ['required', 'date', 'before_or_equal:today'],
            'total_amount' => ['required', 'numeric', 'min:1000', 'max:100000000'],
            'description' => ['required', 'string', 'max:1000'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'receipt_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'receipts' => ['nullable', 'array'],
            'receipts.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    /**
     * Custom attribute names for validation errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'Kategori Biaya',
            'claim_date' => 'Tanggal Transaksi',
            'total_amount' => 'Nominal Biaya',
            'description' => 'Keterangan Klaim',
            'receipt' => 'Bukti Struk/Kuitansi',
            'receipt_image' => 'Bukti Struk/Kuitansi',
            'receipts.*' => 'Berkas Bukti Kuitansi',
        ];
    }
}
