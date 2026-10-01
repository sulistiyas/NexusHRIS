@extends('layouts.app')

@section('title', 'Proses Batch Penggajian Baru - NexusHRIS')
@section('page_title', 'Proses Batch Payroll')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Navigation Back -->
    <div class="flex items-center gap-3">
        <a href="{{ route('payroll-batches.index') }}" class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Jalankan Batch Penggajian Bulanan</h2>
            <p class="text-xs sm:text-sm text-slate-500">Tentukan periode cut-off untuk kalkulasi otomatis seluruh karyawan aktif.</p>
        </div>
    </div>

    @if($errors->any())
        <x-alert type="error">
            <ul class="list-disc list-inside text-xs space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <x-card>
        <form method="POST" action="{{ route('payroll-batches.store') }}" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Bulan Periode <span class="text-rose-500">*</span>
                    </label>
                    <select name="month" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @selected(old('month', $currentMonth) == $m)>
                                {{ \Carbon\Carbon::create(2026, $m, 1)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Tahun Periode <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="number" 
                        name="year" 
                        min="2020" 
                        max="2050" 
                        required 
                        value="{{ old('year', $currentYear) }}" 
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10"
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Tanggal Awal Cut-off <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        name="cut_off_start" 
                        required 
                        value="{{ old('cut_off_start', $defaultStart) }}" 
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10"
                    >
                    <p class="text-[11px] text-slate-400 mt-1">Awal penarikan data lembur & kehadiran.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Tanggal Akhir Cut-off <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        name="cut_off_end" 
                        required 
                        value="{{ old('cut_off_end', $defaultEnd) }}" 
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10"
                    >
                    <p class="text-[11px] text-slate-400 mt-1">Batas akhir penarikan data lembur & kehadiran.</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Tanggal Pembayaran Gaji / Transfer Bank <span class="text-rose-500">*</span>
                </label>
                <input 
                    type="date" 
                    name="payment_date" 
                    required 
                    value="{{ old('payment_date', $defaultPayDate) }}" 
                    class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10"
                >
            </div>

            <div class="bg-sky-50 border border-sky-200/80 rounded-xl p-4 text-xs text-sky-800 space-y-1">
                <div class="font-bold">Informasi Eksekusi Otomatis:</div>
                <p>&bull; Sistem akan mengambil seluruh karyawan aktif yang telah memiliki Struktur Gaji.</p>
                <p>&bull; Menghitung akumulasi jam lembur yang telah berstatus <strong>APPROVED</strong> sesuai formula PP 35/2021.</p>
                <p>&bull; Menghitung potongan BPJS Ketenagakerjaan & BPJS Kesehatan resmi.</p>
                <p>&bull; Mengalkulasi pajak PPh 21 skema TER 2024 (PMK 168/2023).</p>
                <p>&bull; Mengambil potongan cicilan Kasbon karyawan yang aktif.</p>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('payroll-batches.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 font-semibold text-xs sm:text-sm transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-sky-600/20 transition">
                    Kalkulasi Batch Sekarang
                </button>
            </div>
        </form>
    </x-card>

</div>
@endsection
