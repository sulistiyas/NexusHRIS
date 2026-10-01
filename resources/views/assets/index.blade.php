@extends('layouts.app')

@section('title', 'Manajemen Aset Perusahaan - NexusHRIS')
@section('page_title', 'Inventaris Aset')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Manajemen Aset & Inventaris Perusahaan</h2>
            <p class="text-xs sm:text-sm text-slate-500">Pendataan perangkat kerja, status peminjaman karyawan, dan riwayat serah terima fisik.</p>
        </div>
        <a href="{{ route('assets.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-sky-600/20 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Tambah Aset Baru</span>
        </a>
    </div>

    @if(session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif
    @if(session('error'))
        <x-alert type="error">{{ session('error') }}</x-alert>
    @endif

    <!-- Counter Metrik Aset -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 sm:gap-4">
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Aset</span>
            <p class="text-2xl font-extrabold font-mono text-slate-900 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-emerald-50/70 border border-emerald-200 rounded-2xl p-4 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Tersedia</span>
            <p class="text-2xl font-extrabold font-mono text-emerald-900 mt-1">{{ $stats['available'] }}</p>
        </div>
        <div class="bg-sky-50/70 border border-sky-200 rounded-2xl p-4 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-sky-700">Dipinjamkan</span>
            <p class="text-2xl font-extrabold font-mono text-sky-900 mt-1">{{ $stats['assigned'] }}</p>
        </div>
        <div class="bg-amber-50/70 border border-amber-200 rounded-2xl p-4 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700">Dalam Servis</span>
            <p class="text-2xl font-extrabold font-mono text-amber-900 mt-1">{{ $stats['maintenance'] }}</p>
        </div>
        <div class="bg-rose-50/70 border border-rose-200 rounded-2xl p-4 shadow-xs col-span-2 sm:col-span-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-rose-700">Dihapus / Rusak</span>
            <p class="text-2xl font-extrabold font-mono text-rose-900 mt-1">{{ $stats['disposed'] }}</p>
        </div>
    </div>

    <!-- Filter & Pencarian -->
    <x-card class="p-4">
        <form method="GET" action="{{ route('assets.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search }}" 
                    placeholder="Cari kode tag aset, nama perangkat, atau nomor seri..." 
                    class="w-full pl-4 pr-10 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition"
                >
            </div>

            <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0">
                <select name="category_id" class="px-3 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-medium text-slate-700 focus:border-sky-500 bg-white">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>

                <select name="status" class="px-3 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-medium text-slate-700 focus:border-sky-500 bg-white">
                    <option value="ALL" {{ $status === 'ALL' ? 'selected' : '' }}>Semua Status</option>
                    <option value="AVAILABLE" {{ $status === 'AVAILABLE' ? 'selected' : '' }}>Tersedia (Available)</option>
                    <option value="ASSIGNED" {{ $status === 'ASSIGNED' ? 'selected' : '' }}>Dipinjamkan (Assigned)</option>
                    <option value="UNDER_MAINTENANCE" {{ $status === 'UNDER_MAINTENANCE' ? 'selected' : '' }}>Servis (Maintenance)</option>
                    <option value="DISPOSED" {{ $status === 'DISPOSED' ? 'selected' : '' }}>Dihapus (Disposed)</option>
                </select>

                <button type="submit" class="px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs sm:text-sm shadow-sm transition whitespace-nowrap">
                    Filter
                </button>

                @if($search || $categoryId || $status !== 'ALL')
                    <a href="{{ route('assets.index') }}" class="px-3 py-2.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-xs sm:text-sm font-medium transition whitespace-nowrap">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </x-card>

    <!-- Tabel Daftar Aset -->
    <x-card class="p-0 overflow-hidden" title="Daftar Inventaris Aset Kantor">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 font-semibold uppercase text-[11px] tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Tag Aset</th>
                        <th class="px-5 py-3.5">Nama Perangkat</th>
                        <th class="px-5 py-3.5">Kategori</th>
                        <th class="px-5 py-3.5">Kondisi Fisik</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5">Pemegang Saat Ini</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($assets as $asset)
                        @php
                            $activeAssignment = $asset->assignments->first();
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-3.5 font-mono font-bold text-sky-700">
                                {{ $asset->asset_tag }}
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-900">{{ $asset->name }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">SN: {{ $asset->serial_number ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-medium text-slate-800">{{ $asset->category?->name ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                @if($asset->condition === 'EXCELLENT')
                                    <span class="inline-flex items-center gap-1 text-emerald-700 font-medium text-xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Sangat Baik
                                    </span>
                                @elseif($asset->condition === 'GOOD')
                                    <span class="inline-flex items-center gap-1 text-sky-700 font-medium text-xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span> Baik
                                    </span>
                                @elseif($asset->condition === 'DAMAGED')
                                    <span class="inline-flex items-center gap-1 text-amber-700 font-medium text-xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Rusak
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-rose-700 font-medium text-xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Hilang
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if($asset->status === 'AVAILABLE')
                                    <x-badge color="emerald">Tersedia</x-badge>
                                @elseif($asset->status === 'ASSIGNED')
                                    <x-badge color="sky">Dipinjamkan</x-badge>
                                @elseif($asset->status === 'UNDER_MAINTENANCE')
                                    <x-badge color="amber">Dalam Servis</x-badge>
                                @elseif($asset->status === 'DISPOSED')
                                    <x-badge color="rose">Dihapus</x-badge>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if($activeAssignment && $activeAssignment->employee)
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-sky-100 text-sky-700 text-xs font-bold flex items-center justify-center">
                                            {{ substr($activeAssignment->employee->full_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-semibold text-slate-800 text-xs">{{ $activeAssignment->employee->full_name }}</div>
                                            <div class="text-[10px] text-slate-400">Sejak {{ $activeAssignment->assigned_date?->format('d/m/Y') }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-slate-400 text-xs italic">Gudang / Tersedia</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    <a href="{{ route('assets.show', $asset) }}" class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition" title="Rincian & Serah Terima">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    <a href="{{ route('assets.edit', $asset) }}" class="p-2 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-700 text-xs font-semibold transition" title="Ubah Data">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    @if($asset->status !== 'ASSIGNED')
                                        <form method="POST" action="{{ route('assets.destroy', $asset) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-semibold transition" title="Hapus Aset">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-slate-400">
                                Tidak ada data inventaris aset yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($assets->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $assets->links() }}
            </div>
        @endif
    </x-card>

</div>
@endsection
