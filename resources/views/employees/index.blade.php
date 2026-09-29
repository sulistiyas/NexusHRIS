@extends('layouts.app')

@section('title', 'Master Data Karyawan - NexusHRIS')
@section('page_title', 'Data Karyawan')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Master Data Karyawan</h2>
            <p class="text-xs sm:text-sm text-slate-500">Kelola direktori seluruh karyawan, akun akses, dan penempatan cabang.</p>
        </div>
        <a href="{{ route('employees.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-3 sm:py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-sky-600/20 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Tambah Karyawan</span>
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
        <form method="GET" action="{{ route('employees.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="relative sm:col-span-4 lg:col-span-1">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search }}" 
                    placeholder="Cari NIP, nama, NIK, email..." 
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition"
                >
                <span class="absolute left-3.5 top-3 text-slate-400 text-sm">🔍</span>
            </div>

            <div>
                <select name="branch_id" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10">
                    <option value="">Semua Cabang</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" @selected($branchId == $b->id)>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="department_id" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10">
                    <option value="">Semua Departemen</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}" @selected($departmentId == $d->id)>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <select name="status" class="flex-1 px-3 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10">
                    <option value="">Semua Status</option>
                    <option value="PKWTT" @selected($status === 'PKWTT')>Tetap (PKWTT)</option>
                    <option value="PKWT" @selected($status === 'PKWT')>Kontrak (PKWT)</option>
                    <option value="PROBATION" @selected($status === 'PROBATION')>Probation</option>
                    <option value="INTERN" @selected($status === 'INTERN')>Internship</option>
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
                        <th class="px-5 py-4">Karyawan</th>
                        <th class="px-5 py-4">NIP & NIK</th>
                        <th class="px-5 py-4">Departemen & Jabatan</th>
                        <th class="px-5 py-4">Cabang</th>
                        <th class="px-5 py-4 text-center">Status</th>
                        <th class="px-5 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($employees as $emp)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-sky-100 text-sky-700 font-bold flex items-center justify-center text-xs shrink-0 border border-sky-200">
                                        {{ substr($emp->user->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('employees.show', $emp) }}" class="font-bold text-slate-900 hover:text-sky-600 transition block">
                                            {{ $emp->user->name }}
                                        </a>
                                        <span class="text-[11px] text-slate-400 block">{{ $emp->user->email }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-mono font-bold text-sky-700 block">{{ $emp->employee_code }}</span>
                                <span class="text-[11px] font-mono text-slate-400 block">NIK: {{ $emp->nik_ktp }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-medium text-slate-800 block">{{ $emp->designation->title }}</span>
                                <span class="text-[11px] text-slate-500 block">{{ $emp->department->name }}</span>
                            </td>
                            <td class="px-5 py-4 text-slate-600">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-100 text-slate-700">
                                    {{ $emp->branch->name }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                @php
                                $badgeColor = match($emp->employment_status) {
                                    'PKWTT' => 'bg-emerald-100 text-emerald-800',
                                    'PKWT' => 'bg-sky-100 text-sky-800',
                                    'PROBATION' => 'bg-amber-100 text-amber-800',
                                    default => 'bg-slate-100 text-slate-800'
                                };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badgeColor }}">
                                    {{ $emp->employment_status }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('employees.show', $emp) }}" class="p-2 rounded-lg text-slate-500 hover:text-sky-600 hover:bg-sky-50 transition" title="Detail">
                                        👁️
                                    </a>
                                    <a href="{{ route('employees.edit', $emp) }}" class="p-2 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-amber-50 transition" title="Ubah">
                                        ✏️
                                    </a>
                                    <form method="POST" action="{{ route('employees.destroy', $emp) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data karyawan dan akun ini?');">
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
                                <div class="text-3xl mb-2">👥</div>
                                <p class="font-medium">Belum ada data karyawan yang cocok.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($employees->hasPages())
            <div class="px-5 py-4 border-t border-slate-200">
                {{ $employees->links() }}
            </div>
        @endif
    </x-card>

</div>
@endsection
