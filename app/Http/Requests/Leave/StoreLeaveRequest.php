<?php

namespace App\Http\Requests\Leave;

use App\Models\LeaveType;
use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->employee !== null;
    }

    public function rules(): array
    {
        $leaveType = LeaveType::find($this->input('leave_type_id'));
        $requiresAttachment = $leaveType?->requires_attachment ?? false;

        return [
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:500'],
            'attachment' => [
                $requiresAttachment ? 'required' : 'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:2048',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'leave_type_id.required' => 'Jenis cuti wajib dipilih.',
            'start_date.required' => 'Tanggal mulai cuti wajib diisi.',
            'start_date.after_or_equal' => 'Tanggal mulai cuti minimal hari ini.',
            'end_date.required' => 'Tanggal selesai cuti wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai cuti tidak boleh mendahului tanggal mulai.',
            'reason.required' => 'Alasan permohonan cuti wajib diisi.',
            'attachment.required' => 'Surat bukti / keterangan dokter wajib dilampirkan untuk jenis permohonan ini.',
            'attachment.max' => 'Ukuran berkas lampiran maksimal 2MB.',
        ];
    }
}
