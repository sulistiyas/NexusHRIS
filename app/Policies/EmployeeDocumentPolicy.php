<?php

namespace App\Policies;

use App\Models\EmployeeDocument;
use App\Models\User;

class EmployeeDocumentPolicy
{
    /**
     * Tentukan apakah user dapat melihat/mengunduh dokumen.
     */
    public function view(User $user, EmployeeDocument $document): bool
    {
        // Super Admin dan HR Admin bebas melihat semua dokumen
        if ($user->hasAnyRole(['super_admin', 'hr_admin'])) {
            return true;
        }

        // Karyawan hanya boleh melihat dokumen miliknya sendiri
        return $user->employee && $user->employee->id === $document->employee_id;
    }

    /**
     * Tentukan apakah user dapat menghapus dokumen.
     */
    public function delete(User $user, EmployeeDocument $document): bool
    {
        return $user->hasAnyRole(['super_admin', 'hr_admin']);
    }
}
