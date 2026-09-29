<?php

namespace App\Http\Requests\Branch;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBranchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['super_admin', 'hr_admin']) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'unique:branches,code'],
            'name' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'radius_meters' => ['required', 'integer', 'min:5', 'max:5000'],
            'timezone' => ['required', 'string', 'in:Asia/Jakarta,Asia/Makassar,Asia/Jayapura'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Kode cabang wajib diisi.',
            'code.unique' => 'Kode cabang sudah digunakan oleh cabang lain.',
            'name.required' => 'Nama cabang kantor wajib diisi.',
            'radius_meters.required' => 'Radius absensi wajib diisi.',
            'radius_meters.min' => 'Radius absensi minimal 5 meter.',
            'radius_meters.max' => 'Radius absensi maksimal 5000 meter (5 km).',
            'latitude.between' => 'Titik latitude harus berada dalam rentang -90 hingga 90.',
            'longitude.between' => 'Titik longitude harus berada dalam rentang -180 hingga 180.',
            'timezone.required' => 'Zona waktu wajib dipilih.',
            'timezone.in' => 'Zona waktu harus merupakan Asia/Jakarta, Asia/Makassar, atau Asia/Jayapura.',
        ];
    }
}
