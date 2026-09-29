@extends('layouts.app')

@section('title', 'Rekap Presensi Karyawan - NexusHRIS')
@section('page_title', 'Data Presensi')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Monitoring & Rekap Presensi</h2>
            <p class="text-xs sm:text-sm text-slate-500">Pantau kehadiran harian seluruh karyawan, titik lokasi GPS, dan foto verifikasi.</p>
        </div>
    </div>

    <!-- Filter Card -->
    <x-card>
        <form method="GET" action="{{ route('attendances.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Tanggal</label>
                <input type="date" name="date" value="{{ $date }}" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm font-mono focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10">
            </div>

            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Cari Karyawan</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Nama / NIP..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10">
            </div>

            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Cabang</label>
                <select name="branch_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10">
                    <option value="">Semua Cabang</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10">
                    <option value="">Semua Status</option>
                    <option value="PRESENT" {{ $status == 'PRESENT' ? 'selected' : '' }}>PRESENT (Tepat Waktu)</option>
                    <option value="LATE" {{ $status == 'LATE' ? 'selected' : '' }}>LATE (Terlambat)</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 rounded-xl bg-slate-900 text-white text-xs sm:text-sm font-semibold hover:bg-slate-800 transition">
                    Filter
                </button>
                <a href="{{ route('attendances.index') }}" class="px-3 py-2 rounded-xl border border-slate-300 text-slate-600 text-xs sm:text-sm font-semibold hover:bg-slate-100 transition">
                    Reset
                </a>
            </div>
        </form>
    </x-card>

    <!-- Table Card -->
    <x-card class="overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm text-slate-600">
                <thead class="bg-slate-100 text-slate-700 uppercase font-semibold tracking-wider text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Karyawan</th>
                        <th class="px-5 py-3.5">Cabang & Shift</th>
                        <th class="px-5 py-3.5">Clock-In</th>
                        <th class="px-5 py-3.5">Clock-Out</th>
                        <th class="px-5 py-3.5">Tipe</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-center">Selfie</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendances as $row)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-3.5">
                                <span class="font-bold text-slate-900 block">{{ $row->employee->user->name ?? '-' }}</span>
                                <span class="text-xs text-slate-400 font-mono">{{ $row->employee->employee_code }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-semibold text-slate-800 block">{{ $row->employee->branch?->name ?? '-' }}</span>
                                <span class="text-xs text-slate-500">{{ $row->shift?->name ?? 'Default' }}</span>
                            </td>
                            <td class="px-5 py-3.5 font-mono">
                                <span class="font-bold text-sky-700">{{ substr($row->clock_in ?? '-', 0, 5) }}</span>
                                @if($row->late_minutes > 0)
                                    <span class="block text-[11px] text-rose-500 font-semibold">+{{ $row->late_minutes }}m telat</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 font-mono">
                                <span class="font-bold text-slate-700">{{ substr($row->clock_out ?? '-', 0, 5) }}</span>
                                @if($row->early_leave_minutes > 0)
                                    <span class="block text-[11px] text-amber-500 font-semibold">-{{ $row->early_leave_minutes }}m pulang cepat</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $row->work_type === 'WFO' ? 'bg-sky-50 text-sky-700 border border-sky-200' : 'bg-purple-50 text-purple-700 border border-purple-200' }}">
                                    {{ $row->work_type }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                @if($row->status === 'PRESENT')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        TEPAT WAKTU
                                    </span>
                                @elseif($row->status === 'LATE')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        TERLAMBAT
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                        {{ $row->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if($row->in_selfie_path)
                                    <a href="{{ asset('storage/' . $row->in_selfie_path) }}" target="_blank" class="inline-block" title="Lihat Foto Selfie">
                                        <img src="{{ asset('storage/' . $row->in_selfie_path) }}" alt="Foto Selfie" class="w-9 h-9 rounded-lg object-cover border border-slate-200 hover:scale-110 transition">
                                    </a>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-slate-400">
                                Tidak ada catatan presensi pada tanggal ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($attendances->hasPages())
            <div class="px-5 py-4 border-t border-slate-200">
                {{ $attendances->links() }}
            </div>
        @endif
    </x-card>

</div>
@endsection
