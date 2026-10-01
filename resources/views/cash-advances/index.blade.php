@extends('layouts.app')

@section('title', 'Persetujuan Kasbon Karyawan - NexusHRIS')
@section('page_title', 'Persetujuan Kasbon')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Verifikasi & Persetujuan Kasbon</h2>
            <p class="text-xs sm:text-sm text-slate-500">Tinjau permohonan pinjaman dana karyawan sebelum diaktifkan ke skema potong gaji.</p>
        </div>
    </div>

    @if(session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif
    @if(session('error'))
        <x-alert type="error">{{ session('error') }}</x-alert>
    @endif

    <!-- Mini KPI Stat Card -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-xs font-bold text-amber-600 uppercase">Menunggu Review</span>
            <div class="text-2xl font-black text-slate-900 font-mono mt-1">{{ $countPending }}</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-xs font-bold text-sky-600 uppercase">Pinjaman Aktif</span>
            <div class="text-2xl font-black text-slate-900 font-mono mt-1">{{ $countActive }}</div>
        </div>
    </div>

    <!-- Filter Card -->
    <x-card>
        <form method="GET" action="{{ route('cash-advance-approvals.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="relative sm:col-span-2">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search }}" 
                    placeholder="Cari nomor pengajuan (CA-...), nama karyawan, NIP..." 
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition"
                >
                <span class="absolute left-3.5 top-3 text-slate-400 text-sm">🔍</span>
            </div>

            <div class="flex items-center gap-2">
                <select name="status" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10">
                    <option value="">Semua Status</option>
                    <option value="PENDING" @selected($status === 'PENDING')>PENDING (Menunggu)</option>
                    <option value="ACTIVE" @selected($status === 'ACTIVE')>ACTIVE (Aktif)</option>
                    <option value="PAID_OFF" @selected($status === 'PAID_OFF')>PAID_OFF (Lunas)</option>
                    <option value="REJECTED" @selected($status === 'REJECTED')>REJECTED (Ditolak)</option>
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
                        <th class="px-5 py-3.5">No. Dokumen</th>
                        <th class="px-5 py-3.5 text-right">Nominal Pinjaman</th>
                        <th class="px-5 py-3.5 text-center">Tenor</th>
                        <th class="px-5 py-3.5 text-right">Cicilan / Bln</th>
                        <th class="px-5 py-3.5 text-right">Sisa Tagihan</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-center">Aksi Verifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($cashAdvances as $ca)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-900">{{ $ca->employee->user->name ?? '-' }}</div>
                                <div class="text-[11px] text-slate-500 font-mono">{{ $ca->employee->employee_code ?? '-' }} &bull; {{ $ca->employee->department->name ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4 font-mono font-medium text-slate-800">
                                {{ $ca->request_number }}
                                <div class="text-[11px] text-slate-400 font-sans truncate max-w-xs">{{ $ca->reason }}</div>
                            </td>
                            <td class="px-5 py-4 text-right font-mono font-bold text-slate-900">
                                Rp {{ number_format($ca->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 text-center font-mono font-medium">
                                {{ $ca->installment_months }} Bln
                            </td>
                            <td class="px-5 py-4 text-right font-mono text-rose-600 font-medium">
                                Rp {{ number_format($ca->monthly_deduction, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 text-right font-mono font-bold text-sky-800">
                                Rp {{ number_format($ca->remaining_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if($ca->status === 'PENDING')
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        Menunggu
                                    </span>
                                @elseif($ca->status === 'ACTIVE')
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-semibold bg-sky-50 text-sky-700 border border-sky-200">
                                        Aktif
                                    </span>
                                @elseif($ca->status === 'PAID_OFF')
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Lunas
                                    </span>
                                @else
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        Ditolak
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if($ca->status === 'PENDING')
                                    <div class="flex items-center justify-center gap-1.5">
                                        <form method="POST" action="{{ route('cash-advance-approvals.approve', $ca->id) }}">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-xs transition">
                                                Setujui
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('cash-advance-approvals.reject', $ca->id) }}">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-semibold shadow-xs transition">
                                                Tolak
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-slate-400 text-xs">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-8 text-center text-slate-400 text-xs">
                                Tidak ada data permohonan pinjaman kasbon.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($cashAdvances->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $cashAdvances->links() }}
            </div>
        @endif
    </x-card>

</div>
@endsection
