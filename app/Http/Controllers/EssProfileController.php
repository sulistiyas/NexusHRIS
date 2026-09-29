<?php

namespace App\Http\Controllers;

use App\Actions\Employee\UpdateProfilePictureAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EssProfileController extends Controller
{
    /**
     * Tampilkan portal profil mandiri (ESS).
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $employee = $user->employee;
        if ($employee) {
            $employee->load([
                'branch',
                'department',
                'designation',
                'manager.user',
                'documents',
                'emergencyContacts',
            ]);
        }

        return view('ess.profile', compact('user', 'employee'));
    }

    /**
     * Perbarui nomor kontak mandiri.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $employee = $user->employee;
        $validated = $request->validate([
            'phone' => ['nullable', 'string', 'max:30'],
            'birth_place' => ['nullable', 'string', 'max:100'],
        ]);
        if ($employee) {
            $employee->update($validated);
        }

        return back()->with('success', 'Data profil Anda berhasil diperbarui.');
    }

    /**
     * Unggah foto profil avatar baru melalui Web.
     */
    public function uploadAvatar(Request $request, UpdateProfilePictureAction $action): RedirectResponse
    {
        $employee = $request->user()->employee;

        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $action->execute($employee, $request->file('avatar'));

        return back()->with('success', 'Foto profil berhasil diperbarui.');
    }

    /**
     * Tambah kontak darurat mandiri.
     */
    public function storeEmergencyContact(Request $request): RedirectResponse
    {
        $employee = $request->user()->employee;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'relationship' => ['required', 'string', 'max:50'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
        ]);

        $employee->emergencyContacts()->create($validated);

        return back()->with('success', 'Kontak darurat berhasil ditambahkan.');
    }

    /**
     * Hapus kontak darurat mandiri.
     */
    public function destroyEmergencyContact(Request $request, int $id): RedirectResponse
    {
        $employee = $request->user()->employee;
        $contact = $employee->emergencyContacts()->findOrFail($id);
        $contact->delete();

        return back()->with('success', 'Kontak darurat berhasil dihapus.');
    }
}
