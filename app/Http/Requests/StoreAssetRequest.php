<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAssetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('manage_assets') || $this->user()?->hasRole(['super_admin', 'hr_admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:asset_categories,id'],
            'asset_tag' => ['required', 'string', 'max:50', 'unique:assets,asset_tag'],
            'name' => ['required', 'string', 'max:150'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'purchase_cost' => ['nullable', 'numeric', 'min:0'],
            'purchase_date' => ['nullable', 'date'],
            'condition' => ['required', 'in:EXCELLENT,GOOD,DAMAGED,LOST'],
            'status' => ['required', 'in:AVAILABLE,ASSIGNED,UNDER_MAINTENANCE,DISPOSED'],
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
            'category_id' => 'Kategori Aset',
            'asset_tag' => 'Kode / Tag Aset',
            'name' => 'Nama Perangkat / Aset',
            'serial_number' => 'Nomor Seri (Serial Number)',
            'purchase_cost' => 'Harga Pembelian',
            'purchase_date' => 'Tanggal Pembelian',
            'condition' => 'Kondisi Fisik',
            'status' => 'Status Operasional',
        ];
    }
}
