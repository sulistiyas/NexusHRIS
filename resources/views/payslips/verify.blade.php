<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Keabsahan Slip Gaji - NexusHRIS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden">
        
        <!-- Header Brand -->
        <div class="bg-gradient-to-r from-sky-950 to-[#0B1E36] p-6 text-white text-center">
            <div class="inline-flex w-12 h-12 rounded-2xl bg-sky-600 text-white font-black items-center justify-center text-xl shadow-lg ring-4 ring-sky-500/20 mb-3">
                N
            </div>
            <h1 class="text-lg font-extrabold tracking-tight">Nexus<span class="text-sky-400">HRIS</span></h1>
            <p class="text-xs text-sky-200/80">Sistem Verifikasi Otentisitas Dokumen Digital</p>
        </div>

        <div class="p-6 space-y-6">
            @if($payslip)
                <!-- Badge Valid -->
                <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 text-center space-y-1">
                    <div class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center mx-auto text-lg font-bold">
                        ✓
                    </div>
                    <div class="text-sm font-extrabold text-emerald-800">DOKUMEN TERVERIFIKASI SAH</div>
                    <p class="text-xs text-emerald-600">Dokumen slip gaji ini resmi diterbitkan oleh PT NEXUS HRIS INDONESIA.</p>
                </div>

                <!-- Info Rincian Dokumen -->
                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 space-y-2 text-xs">
                    <div class="flex justify-between py-1 border-b border-slate-200/60">
                        <span class="text-slate-500">Nomor Dokumen:</span>
                        <span class="font-mono font-bold text-slate-800">{{ $payslip->slip_number }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-200/60">
                        <span class="text-slate-500">Nama Karyawan:</span>
                        <span class="font-bold text-slate-800">{{ $payslip->employee->user->name }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-200/60">
                        <span class="text-slate-500">NIP / ID Pegawai:</span>
                        <span class="font-mono font-medium text-slate-800">{{ $payslip->employee->employee_code }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-200/60">
                        <span class="text-slate-500">Departemen / Posisi:</span>
                        <span class="font-medium text-slate-800">{{ $payslip->employee->designation->name ?? '-' }} ({{ $payslip->employee->department->name ?? '-' }})</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-200/60">
                        <span class="text-slate-500">Periode Gaji:</span>
                        <span class="font-bold text-slate-800">{{ \Carbon\Carbon::create($payslip->payrollBatch->year, $payslip->payrollBatch->month, 1)->translatedFormat('F Y') }}</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-500">Status Pembayaran:</span>
                        <span class="font-bold text-emerald-700 uppercase">{{ $payslip->payrollBatch->status }}</span>
                    </div>
                </div>

                <div class="text-[11px] text-center text-slate-400">
                    Diverifikasi pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB
                </div>
            @else
                <!-- Badge Invalid -->
                <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 text-center space-y-1">
                    <div class="w-10 h-10 rounded-full bg-rose-600 text-white flex items-center justify-center mx-auto text-lg font-bold">
                        ✕
                    </div>
                    <div class="text-sm font-extrabold text-rose-800">DOKUMEN TIDAK DITEMUKAN</div>
                    <p class="text-xs text-rose-600">Nomor dokumen <span class="font-mono">{{ $slipNumber }}</span> tidak terdaftar dalam basis data sistem penggajian resmi.</p>
                </div>
            @endif

            <div class="text-center">
                <a href="{{ route('login') }}" class="text-xs font-semibold text-sky-600 hover:text-sky-800 transition">
                    &larr; Kembali ke Portal NexusHRIS
                </a>
            </div>
        </div>

    </div>

</body>
</html>
