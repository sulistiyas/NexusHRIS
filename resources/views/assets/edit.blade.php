@extends('layouts.app')

@section('title', 'Ubah Data Aset ' . $asset->asset_tag . ' - NexusHRIS')
@section('page_title', 'Ubah Aset')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Navigation Back -->
    <div class="flex items-center gap-3">
        <a href="{{ route('assets.show', $asset) }}" class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Ubah Data Aset {{ $asset->asset_tag }}</h2>
            <p class="text-xs sm:text-sm text-slate-500">Perbarui spesifikasi, kondisi fisik, atau status ketersediaan aset kantor.</p>
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
        <form method="POST" action="{{ route('assets.update', $asset) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Baris 1: Kategori & Tag Aset -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Kategori Aset <span class="text-rose-500">*</span>
                    </label>
                    <select 
                        name="category_id" 
                        required 
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition bg-white"
                    >
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $asset->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Kode / Tag Aset Unik <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="asset_tag" 
                        value="{{ old('asset_tag', $asset->asset_tag) }}" 
                        required 
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-mono focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition"
                    >
                </div>
            </div>

            <!-- Baris 2: Nama Perangkat & Serial Number -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Nama Perangkat / Aset <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="name" 
                        value="{{ old('name', $asset->name) }}" 
                        required 
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Nomor Seri (Serial Number)
                    </label>
                    <input 
                        type="text" 
                        name="serial_number" 
                        value="{{ old('serial_number', $asset->serial_number) }}" 
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-mono focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition"
                    >
                </div>
            </div>

            <!-- Baris 3: Tanggal Pembelian & Harga Beli -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Tanggal Pengadaan / Pembelian
                    </label>
                    <input 
                        type="date" 
                        name="purchase_date" 
                        value="{{ old('purchase_date', $asset->purchase_date?->format('Y-m-d')) }}" 
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Harga Pembelian (IDR)
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-3.5 text-xs font-bold text-slate-400">Rp</span>
                        <input 
                            type="number" 
                            name="purchase_cost" 
                            step="1000" 
                            min="0" 
                            value="{{ old('purchase_cost', (int) $asset->purchase_cost) }}" 
                            class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 text-sm font-mono focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition"
                        >
                    </div>
                </div>
            </div>

            <!-- Baris 4: Kondisi Fisik & Status -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Kondisi Fisik Aset <span class="text-rose-500">*</span>
                    </label>
                    <select 
                        name="condition" 
                        required 
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition bg-white"
                    >
                        <option value="EXCELLENT" {{ old('condition', $asset->condition) === 'EXCELLENT' ? 'selected' : '' }}>Sangat Baik (EXCELLENT)</option>
                        <option value="GOOD" {{ old('condition', $asset->condition) === 'GOOD' ? 'selected' : '' }}>Baik / Layak Pakai (GOOD)</option>
                        <option value="DAMAGED" {{ old('condition', $asset->condition) === 'DAMAGED' ? 'selected' : '' }}>Rusak Ringan/Berat (DAMAGED)</option>
                        <option value="LOST" {{ old('condition', $asset->condition) === 'LOST' ? 'selected' : '' }}>Hilang (LOST)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Status Ketersediaan <span class="text-rose-500">*</span>
                    </label>
                    <select 
                        name="status" 
                        required 
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition bg-white"
                    >
                        <option value="AVAILABLE" {{ old('status', $asset->status) === 'AVAILABLE' ? 'selected' : '' }}>Tersedia di Gudang (AVAILABLE)</option>
                        <option value="ASSIGNED" {{ old('status', $asset->status) === 'ASSIGNED' ? 'selected' : '' }}>Sedang Dipinjamkan (ASSIGNED)</option>
                        <option value="UNDER_MAINTENANCE" {{ old('status', $asset->status) === 'UNDER_MAINTENANCE' ? 'selected' : '' }}>Dalam Perbaikan (UNDER_MAINTENANCE)</option>
                        <option value="DISPOSED" {{ old('status', $asset->status) === 'DISPOSED' ? 'selected' : '' }}>Dihapus / Dibuang (DISPOSED)</option>
                    </select>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('assets.show', $asset) }}" class="px-5 py-3 rounded-xl border border-slate-300 text-slate-600 font-semibold text-xs sm:text-sm hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-3 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-sky-600/20 transition">
                    Perbarui Data Aset
                </button>
            </div>

        </form>
    </x-card>

</div>
@endsection
