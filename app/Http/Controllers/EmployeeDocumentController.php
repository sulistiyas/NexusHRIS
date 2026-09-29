<?php

namespace App\Http\Controllers;

use App\Http\Requests\Employee\UploadDocumentRequest;
use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeDocumentController extends Controller
{
    /**
     * Unggah berkas dokumen digital karyawan ke penyimpanan privat.
     */
    public function store(UploadDocumentRequest $request, Employee $employee): RedirectResponse
    {
        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $docType = $request->validated('document_type');

        // Simpan ke storage/app/private/documents/{employee_id}/
        $path = $file->store("documents/{$employee->id}");

        $doc = $employee->documents()->create([
            'document_type' => $docType,
            'file_path' => $path,
            'file_name' => $originalName,
            'expiry_date' => $request->validated('expiry_date'),
        ]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'event' => 'employee_document_uploaded',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => [
                'employee_id' => $employee->id,
                'document_type' => $docType,
                'file_name' => $originalName,
            ],
        ]);

        return back()->with('success', "Dokumen {$docType} ({$originalName}) berhasil diunggah.");
    }

    /**
     * Unduh berkas dokumen privat dengan proteksi otorisasi Policy.
     */
    public function download(Request $request, EmployeeDocument $document): StreamedResponse
    {
        Gate::authorize('view', $document);

        if (! Storage::exists($document->file_path)) {
            abort(404, 'File dokumen tidak ditemukan di penyimpanan server.');
        }

        return Storage::download($document->file_path, $document->file_name);
    }

    /**
     * Hapus berkas dokumen.
     */
    public function destroy(Request $request, EmployeeDocument $document): RedirectResponse
    {
        Gate::authorize('delete', $document);

        if (Storage::exists($document->file_path)) {
            Storage::delete($document->file_path);
        }

        $type = $document->document_type;
        $name = $document->file_name;
        $document->delete();

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'event' => 'employee_document_deleted',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_values' => ['type' => $type, 'file_name' => $name],
        ]);

        return back()->with('success', "Dokumen {$type} ({$name}) berhasil dihapus.");
    }
}
