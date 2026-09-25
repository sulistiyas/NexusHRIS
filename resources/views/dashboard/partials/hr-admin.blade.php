<!-- Welcome Banner HR Admin -->
<div class="bg-gradient-to-r from-[#0B1E36] via-[#102A4C] to-sky-900 text-white rounded-2xl p-6 sm:p-8 shadow-xl shadow-sky-950/10 border border-sky-800/40 relative overflow-hidden">
    <div class="relative z-10">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-500/20 text-sky-300 border border-sky-500/30 text-xs font-semibold uppercase tracking-wider mb-3">
            <span class="w-2 h-2 rounded-full bg-sky-400"></span>
            Portal Personalia & Penggajian (HR Admin)
        </div>
        <h2 class="text-xl sm:text-3xl font-extrabold tracking-tight">
            Manajemen SDM & Operasional, {{ auth()->user()->name }}! 👥
        </h2>
        <p class="text-sky-200 text-xs sm:text-sm mt-1 max-w-2xl">
            Kelola data kepegawaian, persetujuan cuti akhir, jadwal kerja, dan pemrosesan batch payroll bulanan.
        </p>
    </div>
</div>

<!-- Modul Kerja HR Admin -->
<div>
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-base sm:text-lg font-bold text-slate-900">Modul Kerja Personalia & HR</h3>
        <span class="text-xs text-sky-600 font-semibold bg-sky-50 px-2.5 py-1 rounded-lg border border-sky-200/60">Akses HR Admin</span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center font-bold mb-3">
                    👥
                </div>
                <h4 class="font-bold text-slate-900">Master Data Karyawan</h4>
                <p class="text-xs text-slate-500 mt-1">Arsip berkas, data identitas, status PKWT/PKWTT, dan rekam karir.</p>
            </div>
            <span class="text-[11px] font-semibold text-sky-600 mt-4">Kelola Karyawan</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold mb-3">
                    💰
                </div>
                <h4 class="font-bold text-slate-900">Payroll Engine</h4>
                <p class="text-xs text-slate-500 mt-1">Kalkulasi gaji pokok, tunjangan, potongan BPJS/PPh21, dan cetak slip.</p>
            </div>
            <span class="text-[11px] font-semibold text-emerald-600 mt-4">Batch Penggajian</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center font-bold mb-3">
                    📝
                </div>
                <h4 class="font-bold text-slate-900">Approval Cuti Final</h4>
                <p class="text-xs text-slate-500 mt-1">Persetujuan tahap akhir pengajuan cuti tahunan dan izin istirahat sakit.</p>
            </div>
            <span class="text-[11px] font-semibold text-orange-600 mt-4">Menunggu Review</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold mb-3">
                    ⏰
                </div>
                <h4 class="font-bold text-slate-900">Rekap Presensi & Shift</h4>
                <p class="text-xs text-slate-500 mt-1">Konfigurasi jadwal kerja, grace period keterlambatan, dan lembur.</p>
            </div>
            <span class="text-[11px] font-semibold text-indigo-600 mt-4">Jadwal Shift</span>
        </div>
    </div>
</div>
