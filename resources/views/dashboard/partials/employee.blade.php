<!-- Welcome Banner Employee -->
<div class="bg-gradient-to-r from-[#0B1E36] via-[#102A4C] to-sky-900 text-white rounded-2xl p-6 sm:p-8 shadow-xl shadow-sky-950/10 border border-sky-800/40 relative overflow-hidden">
    <div class="relative z-10">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-500/20 text-teal-300 border border-teal-500/30 text-xs font-semibold uppercase tracking-wider mb-3">
            <span class="w-2 h-2 rounded-full bg-teal-400"></span>
            Portal Mandiri Karyawan (ESS)
        </div>
        <h2 class="text-xl sm:text-3xl font-extrabold tracking-tight">
            Selamat Bekerja, {{ auth()->user()->name }}! 🚀
        </h2>
        <p class="text-sky-200 text-xs sm:text-sm mt-1 max-w-2xl">
            Akses presensi online real-time, pengajuan cuti/lembur mandiri, serta unduh slip gaji bulanan Anda.
        </p>
    </div>
</div>

<!-- Modul Kerja Employee -->
<div>
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-base sm:text-lg font-bold text-slate-900">Layanan Mandiri Karyawan (ESS)</h3>
        <span class="text-xs text-teal-600 font-semibold bg-teal-50 px-2.5 py-1 rounded-lg border border-teal-200/60">Portal Karyawan</span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold mb-3">
                    📍
                </div>
                <h4 class="font-bold text-slate-900">Presensi Kehadiran</h4>
                <p class="text-xs text-slate-500 mt-1">Clock-in dan clock-out harian berbasis radius kantor dan selfie GPS.</p>
            </div>
            <span class="text-[11px] font-semibold text-emerald-600 mt-4">Absen Masuk</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center font-bold mb-3">
                    🏖️
                </div>
                <h4 class="font-bold text-slate-900">Pengajuan Cuti</h4>
                <p class="text-xs text-slate-500 mt-1">Ajukan cuti tahunan, sakit, atau izin khusus langsung dari portal Anda.</p>
            </div>
            <span class="text-[11px] font-semibold text-sky-600 mt-4">Formulir Cuti</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center font-bold mb-3">
                    🧾
                </div>
                <h4 class="font-bold text-slate-900">Klaim Reimbursement</h4>
                <p class="text-xs text-slate-500 mt-1">Unggah nota dan bukti biaya operasional/medis untuk penggantian kantor.</p>
            </div>
            <span class="text-[11px] font-semibold text-orange-600 mt-4">Ajukan Klaim</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold mb-3">
                    📄
                </div>
                <h4 class="font-bold text-slate-900">Slip Gaji Pribadi</h4>
                <p class="text-xs text-slate-500 mt-1">Akses dan unduh arsip slip gaji bulanan Anda dengan aman.</p>
            </div>
            <span class="text-[11px] font-semibold text-indigo-600 mt-4">Lihat Riwayat Gaji</span>
        </div>
    </div>
</div>
