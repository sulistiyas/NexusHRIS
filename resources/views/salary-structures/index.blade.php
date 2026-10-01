@extends('layouts.app')

@section('title', 'Master Struktur Gaji Karyawan - NexusHRIS')
@section('page_title', 'Struktur Gaji')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Master Struktur Gaji Karyawan</h2>
            <p class="text-xs sm:text-sm text-slate-500">Konfigurasi komponen gaji pokok, tunjangan tetap, dan estimasi potongan regulasi nasional.</p>
        </div>
    </div>

    @if(session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif
    @if(session('error'))
        <x-alert type="error">{{ session('error') }}</x-alert>
    @endif

    <!-- Filter Card -->
    <x-card>
        <form method="GET" action="{{ route('salary-structures.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="relative sm:col-span-4 lg:col-span-1">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search }}" 
                    placeholder="Cari NIP, nama karyawan..." 
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

            <div class="flex items-center gap-2">
                <select name="status" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10">
                    <option value="">Semua Status Gaji</option>
                    <option value="configured" @selected($statusFilter === 'configured')>Sudah Diatur</option>
                    <option value="unconfigured" @selected($statusFilter === 'unconfigured')>Belum Diatur</option>
                </select>
                <button type="submit" class="px-4 py-2.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-semibold shadow-xs transition">
                    Filter
                </button>
            </div>
        </form>
    </x-card>

    <!-- Table Card -->
    <x-card class="p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200/80 uppercase text-[11px] tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Karyawan</th>
                        <th class="px-5 py-3.5">Penempatan</th>
                        <th class="px-5 py-3.5 text-right">Gaji Pokok</th>
                        <th class="px-5 py-3.5 text-right">Tunjangan</th>
                        <th class="px-5 py-3.5 text-right">Potongan Est.</th>
                        <th class="px-5 py-3.5 text-right">Est. Take Home Pay</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($employees as $emp)
                        @php
                            $structure = $emp->salaryStructure;
                            $hasStructure = (bool) $structure;
                            $basic = (float) ($structure->basic_salary ?? 0);
                            $allowances = (float) (($structure->fixed_allowance ?? 0) + ($structure->transport_allowance ?? 0) + ($structure->meal_allowance ?? 0));
                            $deductions = (float) (($structure->bpjs_tk_deduction ?? 0) + ($structure->bpjs_kes_deduction ?? 0) + ($structure->pph21_estimated ?? 0));
                            $thp = max(0, ($basic + $allowances) - $deductions);
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-900">{{ $emp->user->name }}</div>
                                <div class="text-[11px] text-slate-500 font-mono">{{ $emp->employee_code }} &bull; {{ $emp->designation->name ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-slate-800 font-medium">{{ $emp->branch->name ?? '-' }}</div>
                                <div class="text-[11px] text-slate-500">{{ $emp->department->name ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4 text-right font-mono font-medium text-slate-900">
                                {{ $hasStructure ? 'Rp ' . number_format($basic, 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-5 py-4 text-right font-mono text-emerald-700">
                                {{ $hasStructure ? 'Rp ' . number_format($allowances, 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-5 py-4 text-right font-mono text-rose-700">
                                {{ $hasStructure ? 'Rp ' . number_format($deductions, 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-5 py-4 text-right font-mono font-bold text-sky-800">
                                {{ $hasStructure ? 'Rp ' . number_format($thp, 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if($hasStructure)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        Belum Diatur
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                <a href="{{ route('salary-structures.edit', $emp->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-sky-50 text-sky-700 hover:bg-sky-100 font-semibold text-xs border border-sky-200 transition">
                                    <span>⚙️</span>
                                    <span>{{ $hasStructure ? 'Ubah' : 'Atur' }}</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-8 text-center text-slate-400 text-xs">
                                Tidak ada data karyawan yang sesuai dengan kriteria pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($employees->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $employees->links() }}
            </div>
        @endif
    </x-card>

</div>
@endsection
