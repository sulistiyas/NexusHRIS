<!-- Welcome Banner Manager -->
<div class="bg-gradient-to-r from-[#0B1E36] via-[#102A4C] to-sky-900 text-white rounded-2xl p-6 sm:p-8 shadow-xl shadow-sky-950/10 border border-sky-800/40 relative overflow-hidden">
    <div class="relative z-10">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-semibold uppercase tracking-wider mb-3">
            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
            Pusat Kendali Departemen (Line Manager)
        </div>
        <h2 class="text-xl sm:text-3xl font-extrabold tracking-tight">
            Monitoring & Persetujuan Tim, {{ auth()->user()->name }}! 🎯
        </h2>
        <p class="text-sky-200 text-xs sm:text-sm mt-1 max-w-2xl">
            Pantau produktivitas harian, persetujuan cuti tingkat 1, dan verifikasi lembur anggota tim Anda.
        </p>
    </div>
</div>

<!-- Modul Kerja Manager -->
<div>
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-base sm:text-lg font-bold text-slate-900">Modul Kendali Tim Departemen</h3>
        <span class="text-xs text-emerald-600 font-semibold bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200/60">Akses Manager</span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold mb-3">
                    ✅
                </div>
                <h4 class="font-bold text-slate-900">Approval Cuti Anggota</h4>
                <p class="text-xs text-slate-500 mt-1">Persetujuan tahap 1 (Line Manager) sebelum diteruskan ke HR Departemen.</p>
            </div>
            <span class="text-[11px] font-semibold text-emerald-600 mt-4">Review Cuti Tim</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center font-bold mb-3">
                    ⚡
                </div>
                <h4 class="font-bold text-slate-900">Verifikasi Lembur</h4>
                <p class="text-xs text-slate-500 mt-1">Validasi permohonan jam kerja lembur anggota departemen Anda.</p>
            </div>
            <span class="text-[11px] font-semibold text-orange-600 mt-4">Persetujuan Lembur</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center font-bold mb-3">
                    📊
                </div>
                <h4 class="font-bold text-slate-900">Kehadiran Anggota Hari Ini</h4>
                <p class="text-xs text-slate-500 mt-1">Pantau presensi masuk, status izin, atau ketidakhadiran tim secara langsung.</p>
            </div>
            <span class="text-[11px] font-semibold text-sky-600 mt-4">Monitoring Real-time</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold mb-3">
                    📅
                </div>
                <h4 class="font-bold text-slate-900">Jadwal Shift Tim</h4>
                <p class="text-xs text-slate-500 mt-1">Alokasi penugasan jadwal kerja harian anggota departemen.</p>
            </div>
            <span class="text-[11px] font-semibold text-indigo-600 mt-4">Pengaturan Jadwal</span>
        </div>
    </div>
</div>
