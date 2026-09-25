<!-- Backdrop Overlay Mobile -->
<div id="sidebar-overlay" class="fixed inset-0 bg-slate-950/60 z-40 hidden lg:hidden backdrop-blur-xs transition-opacity duration-300"></div>

<!-- Sidebar Container -->
<aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-[#081627] text-slate-300 flex flex-col border-r border-sky-950/80 -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
    
    <!-- Brand / Logo Header -->
    <div class="h-16 px-6 flex items-center justify-between border-b border-sky-950/60 bg-[#061220]">
        <div class="flex items-center gap-3">
            <span class="w-9 h-9 rounded-xl bg-gradient-to-tr from-sky-600 to-blue-700 text-white font-extrabold flex items-center justify-center text-base shadow-md shadow-sky-900/50 ring-2 ring-orange-500/30">
                N
            </span>
            <span class="font-extrabold text-lg text-white tracking-tight">Nexus<span class="text-sky-400">HRIS</span></span>
        </div>
        
        <!-- Tombol Close Mobile -->
        <button id="sidebar-close" type="button" class="lg:hidden text-slate-400 hover:text-white p-1 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <!-- Navigation Links Berdasarkan Peran -->
    <nav class="flex-1 px-4 py-6 space-y-6 overflow-y-auto">
        
        <!-- Menu Utama Umum -->
        <div>
            <span class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-500">Menu Utama</span>
            <div class="mt-2 space-y-1">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-xs sm:text-sm {{ request()->routeIs('dashboard') ? 'bg-sky-600 text-white shadow-md shadow-sky-600/20' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' }} transition">
                    <span class="text-base">📊</span>
                    <span>Dashboard</span>
                </a>
            </div>
        </div>

        <!-- Menu Khusus Super Admin -->
        @role('super_admin')
            <div>
                <span class="px-3 text-[11px] font-bold uppercase tracking-wider text-orange-400/80">Administrasi Sistem</span>
                <div class="mt-2 space-y-1">
                    <a href="{{ route('admin.audit-logs') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-xs sm:text-sm {{ request()->routeIs('admin.audit-logs') ? 'bg-orange-600 text-white shadow-md shadow-orange-600/20' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' }} transition">
                        <span class="text-base">📋</span>
                        <span>Audit Trail Logs</span>
                    </a>
                    <span class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-xs sm:text-sm text-slate-500 cursor-not-allowed">
                        <span class="text-base">🏢</span>
                        <span>Cabang & Struktur</span>
                    </span>
                    <span class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-xs sm:text-sm text-slate-500 cursor-not-allowed">
                        <span class="text-base">🔐</span>
                        <span>Hak Akses (RBAC)</span>
                    </span>
                </div>
            </div>
        @endrole

        <!-- Menu Khusus HR Admin -->
        @role('hr_admin')
            <div>
                <span class="px-3 text-[11px] font-bold uppercase tracking-wider text-sky-400/80">Manajemen HR</span>
                <div class="mt-2 space-y-1">
                    <span class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-xs sm:text-sm text-slate-500 cursor-not-allowed">
                        <span class="text-base">👥</span>
                        <span>Data Karyawan</span>
                    </span>
                    <span class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-xs sm:text-sm text-slate-500 cursor-not-allowed">
                        <span class="text-base">💰</span>
                        <span>Penggajian (Payroll)</span>
                    </span>
                    <span class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-xs sm:text-sm text-slate-500 cursor-not-allowed">
                        <span class="text-base">📝</span>
                        <span>Persetujuan Cuti Final</span>
                    </span>
                </div>
            </div>
        @endrole

        <!-- Menu Khusus Manager -->
        @role('manager')
            <div>
                <span class="px-3 text-[11px] font-bold uppercase tracking-wider text-emerald-400/80">Manajemen Tim</span>
                <div class="mt-2 space-y-1">
                    <span class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-xs sm:text-sm text-slate-500 cursor-not-allowed">
                        <span class="text-base">✅</span>
                        <span>Persetujuan Cuti Anggota</span>
                    </span>
                    <span class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-xs sm:text-sm text-slate-500 cursor-not-allowed">
                        <span class="text-base">⚡</span>
                        <span>Verifikasi Lembur Tim</span>
                    </span>
                    <span class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-xs sm:text-sm text-slate-500 cursor-not-allowed">
                        <span class="text-base">📊</span>
                        <span>Monitoring Kehadiran</span>
                    </span>
                </div>
            </div>
        @endrole

        <!-- Menu Khusus Employee -->
        @role('employee')
            <div>
                <span class="px-3 text-[11px] font-bold uppercase tracking-wider text-teal-400/80">Layanan Mandiri (ESS)</span>
                <div class="mt-2 space-y-1">
                    <span class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-xs sm:text-sm text-slate-500 cursor-not-allowed">
                        <span class="text-base">📍</span>
                        <span>Presensi Kehadiran</span>
                    </span>
                    <span class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-xs sm:text-sm text-slate-500 cursor-not-allowed">
                        <span class="text-base">🏖️</span>
                        <span>Pengajuan Cuti / Izin</span>
                    </span>
                    <span class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-xs sm:text-sm text-slate-500 cursor-not-allowed">
                        <span class="text-base">📄</span>
                        <span>Slip Gaji Saya</span>
                    </span>
                </div>
            </div>
        @endrole

    </nav>

    <!-- Footer Profile Widget di Sidebar -->
    <div class="p-4 border-t border-sky-950/60 bg-[#061220]">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-sky-950 text-sky-400 font-bold flex items-center justify-center text-xs shrink-0 border border-sky-800/40">
                {{ substr(auth()->user()->name, 0, 2) }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                <p class="text-[11px] text-slate-400 truncate">{{ auth()->user()->email }}</p>
            </div>
        </div>
    </div>

</aside>
