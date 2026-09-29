@extends('layouts.app')

@section('title', 'Manajemen Departemen - NexusHRIS')
@section('page_title', 'Departemen')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Daftar Departemen</h2>
            <p class="text-xs sm:text-sm text-slate-500">Kelola departemen perusahaan yang terhubung dengan cabang dan kepala departemen.</p>
        </div>
        <a href="{{ route('departments.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-3 sm:py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-sky-600/20 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Tambah Departemen</span>
        </a>
    </div>

    @if(session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif
    @if(session('error'))
        <x-alert type="error">{{ session('error') }}</x-alert>
    @endif

    <!-- Filter Card -->
    <x-card>
        <form method="GET" action="{{ route('departments.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="relative sm:col-span-2">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search }}" 
                    placeholder="Cari berdasarkan nama departemen atau kode..." 
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition"
                >
                <span class="absolute left-3.5 top-3 text-slate-400 text-sm">🔍</span>
            </div>

            <div class="flex gap-2">
                <select name="branch_id" class="flex-1 px-3 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10">
                    <option value="">Semua Cabang</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected($branchId == $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-900 text-white text-xs sm:text-sm font-semibold hover:bg-slate-800 transition">
                    Filter
                </button>
            </div>
        </form>
    </x-card>

    <!-- Table Card -->
    <x-card class="overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm text-slate-600">
                <thead class="bg-slate-100 text-slate-700 uppercase font-semibold tracking-wider text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-4">Kode</th>
                        <th class="px-5 py-4">Nama Departemen</th>
                        <th class="px-5 py-4">Cabang</th>
                        <th class="px-5 py-4">Manajer / Kepala</th>
                        <th class="px-5 py-4 text-center">Jabatan</th>
                        <th class="px-5 py-4 text-center">Karyawan</th>
                        <th class="px-5 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($departments as $dept)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-4 font-mono font-bold text-sky-700">{{ $dept->code }}</td>
                            <td class="px-5 py-4 font-bold text-slate-900">{{ $dept->name }}</td>
                            <td class="px-5 py-4 text-slate-600">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-100 text-slate-700">
                                    {{ $dept->branch->name }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-600">
                                {{ $dept->manager?->user?->name ?? '-' }}
                            </td>
                            <td class="px-5 py-4 text-center font-semibold text-slate-700">{{ $dept->designations_count }}</td>
                            <td class="px-5 py-4 text-center font-semibold text-slate-700">{{ $dept->employees_count }}</td>
                            <td class="px-5 py-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('departments.edit', $dept) }}" class="p-2 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-amber-50 transition" title="Ubah">
                                        ✏️
                                    </a>
                                    <form method="POST" action="{{ route('departments.destroy', $dept) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus departemen ini?');">
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
                                <div class="text-3xl mb-2">📑</div>
                                <p class="font-medium">Belum ada data departemen yang cocok.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($departments->hasPages())
            <div class="px-5 py-4 border-t border-slate-200">
                {{ $departments->links() }}
            </div>
        @endif
    </x-card>

</div>
@endsection
