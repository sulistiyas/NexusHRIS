<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReturnAssetRequest extends FormRequest
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
            'returned_date' => ['required', 'date', 'before_or_equal:today'],
            'return_condition' => ['required', 'in:EXCELLENT,GOOD,DAMAGED'],
            'notes' => ['nullable', 'string', 'max:1000'],
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
            'returned_date' => 'Tanggal Pengembalian',
            'return_condition' => 'Kondisi Saat Kembali',
            'notes' => 'Catatan Pengembalian / Kerusakan',
        ];
    }
}
