@extends('layouts.app')

@section('title', 'Ajukan Klaim Reimbursement - NexusHRIS')
@section('page_title', 'Ajukan Reimbursement')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Navigation Back -->
    <div class="flex items-center gap-3">
        <a href="{{ route('ess.reimbursements.index') }}" class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Formulir Klaim Reimbursement</h2>
            <p class="text-xs sm:text-sm text-slate-500">Isi rincian pengeluaran operasional dan unggah bukti struk pembayaran sah.</p>
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
        <form method="POST" action="{{ route('ess.reimbursements.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <!-- Kategori Biaya -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Kategori Biaya Operasional <span class="text-rose-500">*</span>
                </label>
                <select 
                    name="category_id" 
                    required 
                    class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition bg-white"
                >
                    <option value="">-- Pilih Kategori Pengeluaran --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Tanggal Klaim & Total Nominal -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Tanggal Transaksi Struk <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        name="claim_date" 
                        max="{{ date('Y-m-d') }}"
                        value="{{ old('claim_date', date('Y-m-d')) }}" 
                        required 
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition"
                    >
                    <p class="text-[11px] text-slate-400 mt-1">Sesuai tanggal yang tertera pada nota kuitansi.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Total Nominal Biaya (IDR) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-3.5 text-xs font-bold text-slate-400">Rp</span>
                        <input 
                            type="number" 
                            name="total_amount" 
                            step="100" 
                            min="1000" 
                            max="100000000" 
                            required 
                            value="{{ old('total_amount') }}" 
                            placeholder="Contoh: 150000"
                            class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 text-sm font-mono focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition"
                        >
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Nominal bulat tanpa titik koma.</p>
                </div>
            </div>

            <!-- Keterangan Klaim -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Keterangan & Keperluan Klaim <span class="text-rose-500">*</span>
                </label>
                <textarea 
                    name="description" 
                    rows="3" 
                    required 
                    placeholder="Jelaskan secara ringkas keperluan dinas terkait (misal: Pembelian kacamata kerja medis / Penggantian BBM tugas kunjungan cabang Tangerang)..."
                    class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition"
                >{{ old('description') }}</textarea>
            </div>

            <!-- Unggah Foto / Berkas Struk Kuitansi -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Foto Bukti Struk / Berkas Nota Kuitansi <span class="text-rose-500">*</span>
                </label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-300 border-dashed rounded-2xl hover:border-sky-400 transition bg-slate-50/50">
                    <div class="space-y-2 text-center">
                        <svg class="mx-auto h-10 w-10 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-xs text-slate-600 justify-center">
                            <label class="relative cursor-pointer font-bold text-sky-600 hover:text-sky-500">
                                <span>Pilih Berkas Lampiran</span>
                                <input type="file" name="receipt" accept=".jpg,.jpeg,.png,.pdf" required class="sr-only">
                            </label>
                        </div>
                        <p class="text-[11px] text-slate-500">Format: JPG, PNG, atau PDF (Ukuran maksimal 5 MB)</p>
                    </div>
                </div>
            </div>

            <!-- Aksi Tombol -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('ess.reimbursements.index') }}" class="px-5 py-3 rounded-xl border border-slate-300 text-slate-600 font-semibold text-xs sm:text-sm hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-3 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-sky-600/20 transition">
                    Ajukan Klaim Reimbursement
                </button>
            </div>

        </form>
    </x-card>

</div>
@endsection
