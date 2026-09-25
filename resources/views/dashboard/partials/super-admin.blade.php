<!-- Welcome Banner Super Admin -->
<div class="bg-gradient-to-r from-[#0B1E36] via-[#102A4C] to-sky-900 text-white rounded-2xl p-6 sm:p-8 shadow-xl shadow-sky-950/10 border border-sky-800/40 relative overflow-hidden">
    <div class="relative z-10">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-500/20 text-orange-300 border border-orange-500/30 text-xs font-semibold uppercase tracking-wider mb-3">
            <span class="w-2 h-2 rounded-full bg-orange-400"></span>
            Hak Akses Penuh Sistem (Super Administrator)
        </div>
        <h2 class="text-xl sm:text-3xl font-extrabold tracking-tight">
            Panel Konfigurasi Sistem, {{ auth()->user()->name }}! ⚙️
        </h2>
        <p class="text-sky-200 text-xs sm:text-sm mt-1 max-w-2xl">
            Anda memiliki kendali tertinggi atas konfigurasi peran, struktur kantor, audit log, dan integrasi API NexusHRIS.
        </p>
    </div>
</div>

<!-- Modul Kerja Super Admin -->
<div>
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-base sm:text-lg font-bold text-slate-900">Modul Kerja Super Administrator</h3>
        <span class="text-xs text-orange-600 font-semibold bg-orange-50 px-2.5 py-1 rounded-lg border border-orange-200/60">Hak Akses Penuh</span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('admin.audit-logs') }}" class="group bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md hover:border-orange-500/40 transition duration-200 flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center font-bold mb-3 group-hover:scale-105 transition">
                    📋
                </div>
                <h4 class="font-bold text-slate-900 group-hover:text-orange-600 transition">Audit Trail Logs</h4>
                <p class="text-xs text-slate-500 mt-1">Pantau seluruh rekaman aktivitas sistem dan login keamanan secara real-time.</p>
            </div>
            <span class="text-[11px] font-semibold text-orange-600 mt-4 flex items-center gap-1">Akses Log &rarr;</span>
        </a>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center font-bold mb-3">
                    🏢
                </div>
                <h4 class="font-bold text-slate-900">Cabang & Departemen</h4>
                <p class="text-xs text-slate-500 mt-1">Kelola master cabang, koordinat GPS kantor, dan struktur hierarki divisi.</p>
            </div>
            <span class="text-[11px] font-semibold text-sky-600 mt-4">Terkonfigurasi</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold mb-3">
                    🔐
                </div>
                <h4 class="font-bold text-slate-900">Manajemen Peran (RBAC)</h4>
                <p class="text-xs text-slate-500 mt-1">Konfigurasi matriks permissions Spatie dan penugasan peran pengguna.</p>
            </div>
            <span class="text-[11px] font-semibold text-purple-600 mt-4">4 Peran Aktif</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold mb-3">
                    ⚡
                </div>
                <h4 class="font-bold text-slate-900">Mobile REST API</h4>
                <p class="text-xs text-slate-500 mt-1">Endpoint Sanctum v1 terstandarisasi untuk integrasi mobile apps.</p>
            </div>
            <span class="text-[11px] font-semibold text-emerald-600 mt-4">Status Online</span>
        </div>
    </div>
</div>
