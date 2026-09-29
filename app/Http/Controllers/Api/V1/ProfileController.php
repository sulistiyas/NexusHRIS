<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Employee\UpdateProfilePictureAction;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\V1\EmergencyContactResource;
use App\Http\Resources\V1\EmployeeResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends BaseApiController
{
    /**
     * Ambil data profil karyawan yang sedang login.
     */
    public function show(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;

        if (! $employee) {
            return $this->sendError('Data profil karyawan tidak ditemukan untuk akun ini.', null, Response::HTTP_NOT_FOUND);
        }

        $employee->load(['branch', 'department', 'designation', 'manager.user', 'emergencyContacts']);

        return $this->sendSuccess(new EmployeeResource($employee), 'Profil berhasil dimuat.');
    }

    /**
     * Perbarui data pribadi mandiri (telepon & alamat).
     */
    public function update(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;

        if (! $employee) {
            return $this->sendError('Data profil karyawan tidak ditemukan.', null, Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'phone' => ['nullable', 'string', 'max:30'],
            'birth_place' => ['nullable', 'string', 'max:100'],
        ]);

        $employee->update($validated);

        return $this->sendSuccess(new EmployeeResource($employee), 'Profil berhasil diperbarui.');
    }

    /**
     * Unggah foto profil avatar baru via REST API.
     */
    public function uploadAvatar(Request $request, UpdateProfilePictureAction $action): JsonResponse
    {
        $employee = $request->user()->employee;

        if (! $employee) {
            return $this->sendError('Data profil karyawan tidak ditemukan.', null, Response::HTTP_NOT_FOUND);
        }

        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'], // Max 2MB
        ]);

        $url = $action->execute($employee, $request->file('avatar'));

        return $this->sendSuccess(['avatar_url' => url($url)], 'Foto profil berhasil diperbarui.');
    }

    /**
     * Ambil daftar kontak darurat karyawan.
     */
    public function emergencyContacts(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;

        if (! $employee) {
            return $this->sendError('Data profil karyawan tidak ditemukan.', null, Response::HTTP_NOT_FOUND);
        }

        return $this->sendSuccess(
            EmergencyContactResource::collection($employee->emergencyContacts),
            'Daftar kontak darurat berhasil dimuat.'
        );
    }

    /**
     * Tambah kontak darurat baru.
     */
    public function storeEmergencyContact(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;

        if (! $employee) {
            return $this->sendError('Data profil karyawan tidak ditemukan.', null, Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'relationship' => ['required', 'string', 'max:50'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
        ]);

        $contact = $employee->emergencyContacts()->create($validated);

        return $this->sendSuccess(new EmergencyContactResource($contact), 'Kontak darurat berhasil ditambahkan.', Response::HTTP_CREATED);
    }

    /**
     * Hapus kontak darurat.
     */
    public function destroyEmergencyContact(Request $request, int $id): JsonResponse
    {
        $employee = $request->user()->employee;

        if (! $employee) {
            return $this->sendError('Data profil karyawan tidak ditemukan.', null, Response::HTTP_NOT_FOUND);
        }

        $contact = $employee->emergencyContacts()->find($id);

        if (! $contact) {
            return $this->sendError('Kontak darurat tidak ditemukan.', null, Response::HTTP_NOT_FOUND);
        }

        $contact->delete();

        return $this->sendSuccess(null, 'Kontak darurat berhasil dihapus.');
    }
}
