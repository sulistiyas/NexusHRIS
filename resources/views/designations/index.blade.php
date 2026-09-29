@extends('layouts.app')

@section('title', 'Manajemen Jabatan - NexusHRIS')
@section('page_title', 'Jabatan')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Daftar Jabatan</h2>
            <p class="text-xs sm:text-sm text-slate-500">Kelola hierarki posisi dan tingkatan golongan (grade) karyawan.</p>
        </div>
        <a href="{{ route('designations.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-3 sm:py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-sky-600/20 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Tambah Jabatan</span>
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
        <form method="GET" action="{{ route('designations.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="relative sm:col-span-2">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search }}" 
                    placeholder="Cari nama jabatan atau tingkatan golongan..." 
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition"
                >
                <span class="absolute left-3.5 top-3 text-slate-400 text-sm">🔍</span>
            </div>

            <div class="flex gap-2">
                <select name="department_id" class="flex-1 px-3 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10">
                    <option value="">Semua Departemen</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" @selected($departmentId == $dept->id)>
                            {{ $dept->name }} ({{ $dept->branch->name }})
                        </option>
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
                        <th class="px-5 py-4">Nama Jabatan</th>
                        <th class="px-5 py-4">Departemen</th>
                        <th class="px-5 py-4">Cabang</th>
                        <th class="px-5 py-4 text-center">Grade Level</th>
                        <th class="px-5 py-4 text-center">Karyawan</th>
                        <th class="px-5 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($designations as $designation)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-4 font-bold text-slate-900">{{ $designation->title }}</td>
                            <td class="px-5 py-4 font-medium text-slate-700">{{ $designation->department->name }}</td>
                            <td class="px-5 py-4 text-slate-500">{{ $designation->department->branch->name }}</td>
                            <td class="px-5 py-4 text-center">
                                @if($designation->grade_level)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        {{ $designation->grade_level }}
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center font-semibold text-slate-700">{{ $designation->employees_count }}</td>
                            <td class="px-5 py-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('designations.edit', $designation) }}" class="p-2 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-amber-50 transition" title="Ubah">
                                        ✏️
                                    </a>
                                    <form method="POST" action="{{ route('designations.destroy', $designation) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jabatan ini?');">
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
                            <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                                <div class="text-3xl mb-2">💼</div>
                                <p class="font-medium">Belum ada data jabatan yang cocok.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($designations->hasPages())
            <div class="px-5 py-4 border-t border-slate-200">
                {{ $designations->links() }}
            </div>
        @endif
    </x-card>

</div>
@endsection
