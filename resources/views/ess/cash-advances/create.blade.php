@extends('layouts.app')

@section('title', 'Ajukan Pinjaman Kasbon - NexusHRIS')
@section('page_title', 'Ajukan Kasbon')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Navigation Back -->
    <div class="flex items-center gap-3">
        <a href="{{ route('ess.cash-advances.index') }}" class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Formulir Pengajuan Kasbon</h2>
            <p class="text-xs sm:text-sm text-slate-500">Ajukan permohonan pinjaman dana darurat dengan cicilan potong gaji.</p>
        </div>
    </div>

    @if($hasPending)
        <x-alert type="warning">
            Perhatian: Anda masih memiliki pengajuan kasbon yang berstatus <strong>Menunggu Persetujuan</strong>. Anda tidak dapat mengajukan kasbon baru sebelum permohonan sebelumnya selesai diproses.
        </x-alert>
    @endif

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
        <form method="POST" action="{{ route('ess.cash-advances.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Nominal Kasbon yang Diajukan (Rp) <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3.5 top-3 text-xs font-bold text-slate-400">Rp</span>
                    <input 
                        type="number" 
                        name="amount" 
                        step="10000" 
                        min="50000" 
                        max="50000000" 
                        required 
                        value="{{ old('amount', 500000) }}" 
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition"
                    >
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Minimal pengajuan Rp 50.000, maksimal Rp 50.000.000.</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Jangka Waktu Cicilan (Tenor Bulan) <span class="text-rose-500">*</span>
                </label>
                <select name="installment_months" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" @selected(old('installment_months', 1) == $m)>
                            {{ $m }} Bulan (Dipotong otomatis per bulan)
                        </option>
                    @endfor
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Nominal potongan bulanan = Total pinjaman dibagi jumlah bulan cicilan.</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Keperluan / Alasan Pengajuan <span class="text-rose-500">*</span>
                </label>
                <textarea 
                    name="reason" 
                    rows="3" 
                    required 
                    placeholder="Jelaskan kebutuhan dana darurat Anda (misal: Biaya pengobatan keluarga, perbaikan kendaraan)..." 
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition"
                >{{ old('reason') }}</textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('ess.cash-advances.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 font-semibold text-xs sm:text-sm transition">
                    Batal
                </a>
                <button 
                    type="submit" 
                    @disabled($hasPending) 
                    class="px-6 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-sky-600/20 disabled:bg-slate-300 disabled:cursor-not-allowed transition"
                >
                    Kirim Permohonan Kasbon
                </button>
            </div>
        </form>
    </x-card>

</div>
@endsection
