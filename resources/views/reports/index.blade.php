@extends('layouts.app')

@section('title', 'Pusat Ekspor Laporan Excel (.xlsx) - NexusHRIS')
@section('page_title', 'Ekspor Laporan')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div>
        <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Pusat Laporan & Ekspor Spreadsheet (.xlsx)</h2>
        <p class="text-xs sm:text-sm text-slate-500">Unduh data rekapitulasi kehadiran dan rincian payroll ke format spreadsheet Excel resmi.</p>
    </div>

    @if(session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif
    @if(session('error'))
        <x-alert type="error">{{ session('error') }}</x-alert>
    @endif

    <!-- Dua Kartu Generator Laporan -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Laporan 1: Rekapitulasi Presensi Bulanan -->
        <x-card title="1. Rekapitulasi Presensi & Kehadiran Bulanan">
            <div class="space-y-4">
                <p class="text-xs text-slate-600">
                    Menghasilkan file Excel (.xlsx) memuat statistik kehadiran seluruh karyawan aktif: total hari hadir, keterlambatan (menit), cuti, izin, sakit, dan mangkir.
                </p>

                <form method="GET" action="{{ route('reports.attendances.export') }}" class="space-y-4 pt-2 border-t border-slate-100">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Bulan <span class="text-rose-500">*</span>
                            </label>
                            <select name="month" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white focus:border-sky-500">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $m == $currentMonth ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Tahun <span class="text-rose-500">*</span>
                            </label>
                            <select name="year" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white focus:border-sky-500">
                                @for($y = $currentYear; $y >= $currentYear - 3; $y--)
                                    <option value="{{ $y }}" {{ $y == $currentYear ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Kantor Cabang
                            </label>
                            <select name="branch_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white focus:border-sky-500">
                                <option value="">Semua Cabang</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Departemen
                            </label>
                            <select name="department_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white focus:border-sky-500">
                                <option value="">Semua Departemen</option>
                                @foreach($departments as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-emerald-600/20 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            <span>Unduh Laporan Presensi (.xlsx)</span>
                        </button>
                    </div>
                </form>
            </div>
        </x-card>

        <!-- Laporan 2: Rekapitulasi Penggajian & Payroll -->
        <x-card title="2. Rekapitulasi Penggajian & Payroll">
            <div class="space-y-4">
                <p class="text-xs text-slate-600">
                    Menghasilkan file Excel (.xlsx) memuat rincian gaji kotor, tunjangan, upah lemur, potongan BPJS TK & Kesehatan, PPh 21 TER, cicilan kasbon, dan Take Home Pay (Gaji Bersih).
                </p>

                <form method="GET" action="{{ route('reports.payroll.export') }}" class="space-y-4 pt-2 border-t border-slate-100">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Pilih Batch Payroll Terproses
                        </label>
                        <select name="payroll_batch_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white focus:border-sky-500">
                            <option value="">-- Berdasarkan Bulan & Tahun Di Bawah --</option>
                            @foreach($payrollBatches as $batch)
                                <option value="{{ $batch->id }}">
                                    {{ $batch->batch_number }} • Periode {{ \Carbon\Carbon::create(null, $batch->month, 1)->translatedFormat('F') }} {{ $batch->year }} ({{ $batch->status }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Bulan
                            </label>
                            <select name="month" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white focus:border-sky-500">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $m == $currentMonth ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Tahun
                            </label>
                            <select name="year" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white focus:border-sky-500">
                                @for($y = $currentYear; $y >= $currentYear - 3; $y--)
                                    <option value="{{ $y }}" {{ $y == $currentYear ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-sky-600/20 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            <span>Unduh Laporan Payroll (.xlsx)</span>
                        </button>
                    </div>
                </form>
            </div>
        </x-card>

    </div>

</div>
@endsection
