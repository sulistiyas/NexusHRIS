@extends('layouts.app')

@section('title', 'Manajemen Shift Kerja - NexusHRIS')
@section('page_title', 'Shift Kerja')

@section('content')
<div class="space-y-6">

    <!-- Top Action & Search Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Master Shift Kerja</h2>
            <p class="text-xs sm:text-sm text-slate-500">Kelola jadwal jam kerja, jam pulang, toleransi keterlambatan, dan shift malam.</p>
        </div>
        <a href="{{ route('shifts.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-3 sm:py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-sky-600/20 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Tambah Shift</span>
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
        <form method="GET" action="{{ route('shifts.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search }}" 
                    placeholder="Cari nama shift kerja..." 
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition"
                >
                <span class="absolute left-3.5 top-3 text-slate-400 text-sm">🔍</span>
            </div>
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-900 text-white text-xs sm:text-sm font-semibold hover:bg-slate-800 transition">
                Cari
            </button>
            @if($search)
                <a href="{{ route('shifts.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-600 text-xs sm:text-sm font-semibold hover:bg-slate-100 text-center transition">
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
                        <th class="px-5 py-3.5">Nama Shift</th>
                        <th class="px-5 py-3.5">Jam Masuk</th>
                        <th class="px-5 py-3.5">Jam Pulang</th>
                        <th class="px-5 py-3.5">Toleransi Telat</th>
                        <th class="px-5 py-3.5">Tipe Shift</th>
                        <th class="px-5 py-3.5 text-center">Jadwal Aktif</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($shifts as $shift)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-4 font-semibold text-slate-900">
                                {{ $shift->name }}
                            </td>
                            <td class="px-5 py-4 font-mono text-sky-700 font-semibold">
                                {{ substr($shift->start_time, 0, 5) }}
                            </td>
                            <td class="px-5 py-4 font-mono text-slate-700">
                                {{ substr($shift->end_time, 0, 5) }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                    {{ $shift->grace_period_minutes }} menit
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                @if($shift->is_night_shift)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                        <span>🌙</span> Shift Malam
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-sky-50 text-sky-700 border border-sky-200">
                                        <span>☀️</span> Shift Normal
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                    {{ $shift->employee_shifts_count }} Jadwal
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('shifts.edit', $shift) }}" class="p-2 text-slate-400 hover:text-sky-600 hover:bg-sky-50 rounded-lg transition" title="Ubah Shift">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('shifts.destroy', $shift) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus shift {{ $shift->name }}?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Hapus Shift">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-slate-400">
                                Belum ada data shift kerja yang terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($shifts->hasPages())
            <div class="px-5 py-4 border-t border-slate-200">
                {{ $shifts->links() }}
            </div>
        @endif
    </x-card>

</div>
@endsection
