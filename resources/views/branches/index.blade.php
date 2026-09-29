@extends('layouts.app')

@section('title', 'Manajemen Kantor Cabang - NexusHRIS')
@section('page_title', 'Kantor Cabang')

@section('content')
<div class="space-y-6">

    <!-- Top Action & Search Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Daftar Kantor Cabang</h2>
            <p class="text-xs sm:text-sm text-slate-500">Kelola titik lokasi kantor, koordinat GPS, dan radius toleransi presensi.</p>
        </div>
        <a href="{{ route('branches.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-3 sm:py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-sky-600/20 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Tambah Cabang</span>
        </a>
    </div>

    <!-- Alert Notifikasi -->
    @if(session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif
    @if(session('error'))
        <x-alert type="error">{{ session('error') }}</x-alert>
    @endif

    <!-- Filter & Search Card -->
    <x-card>
        <form method="GET" action="{{ route('branches.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search }}" 
                    placeholder="Cari berdasarkan nama, kode cabang, atau alamat..." 
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition"
                >
                <span class="absolute left-3.5 top-3 text-slate-400 text-sm">🔍</span>
            </div>
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-900 text-white text-xs sm:text-sm font-semibold hover:bg-slate-800 transition">
                Cari
            </button>
            @if($search)
                <a href="{{ route('branches.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-600 text-xs sm:text-sm font-semibold hover:bg-slate-100 text-center transition">
                    Reset
                </a>
            @endif
        </form>
    </x-card>

    <!-- Table List Card -->
    <x-card class="overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm text-slate-600">
                <thead class="bg-slate-100 text-slate-700 uppercase font-semibold tracking-wider text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-4">Kode</th>
                        <th class="px-5 py-4">Nama Cabang</th>
                        <th class="px-5 py-4">Koordinat GPS</th>
                        <th class="px-5 py-4 text-center">Radius</th>
                        <th class="px-5 py-4 text-center">Departemen</th>
                        <th class="px-5 py-4 text-center">Karyawan</th>
                        <th class="px-5 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($branches as $branch)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-4 font-mono font-bold text-sky-700">
                                {{ $branch->code }}
                            </td>
                            <td class="px-5 py-4">
                                <a href="{{ route('branches.show', $branch) }}" class="font-bold text-slate-900 hover:text-sky-600 transition block">
                                    {{ $branch->name }}
                                </a>
                                <span class="text-[11px] text-slate-400 block truncate max-w-xs">{{ $branch->address ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-4 font-mono text-[11px] text-slate-500">
                                @if($branch->latitude && $branch->longitude)
                                    📍 {{ number_format($branch->latitude, 4) }}, {{ number_format($branch->longitude, 4) }}
                                @else
                                    <span class="text-amber-500 font-sans">Belum diatur</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-100 text-sky-800">
                                    {{ $branch->radius_meters }} m
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center font-semibold text-slate-700">
                                {{ $branch->departments_count }}
                            </td>
                            <td class="px-5 py-4 text-center font-semibold text-slate-700">
                                {{ $branch->employees_count }}
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('branches.show', $branch) }}" class="p-2 rounded-lg text-slate-500 hover:text-sky-600 hover:bg-sky-50 transition" title="Detail">
                                        👁️
                                    </a>
                                    <a href="{{ route('branches.edit', $branch) }}" class="p-2 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-amber-50 transition" title="Ubah">
                                        ✏️
                                    </a>
                                    <form method="POST" action="{{ route('branches.destroy', $branch) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus cabang ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                                <div class="text-3xl mb-2">🏢</div>
                                <p class="font-medium">Belum ada data cabang kantor yang cocok.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($branches->hasPages())
            <div class="px-5 py-4 border-t border-slate-200">
                {{ $branches->links() }}
            </div>
        @endif
    </x-card>

</div>
@endsection
