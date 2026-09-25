<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * Tampilkan riwayat audit trail (khusus super_admin).
     */
    public function index(): View
    {
        $logs = ActivityLog::with('user')
            ->latest()
            ->paginate(15);

        return view('admin.audit-logs.index', compact('logs'));
    }
}
