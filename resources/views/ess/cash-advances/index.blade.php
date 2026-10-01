@extends('layouts.app')

@section('title', 'Layanan Kasbon Saya - NexusHRIS')
@section('page_title', 'Pinjaman / Kasbon')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Pinjaman / Kasbon Karyawan</h2>
            <p class="text-xs sm:text-sm text-slate-500">Layanan pengajuan dana darurat mandiri dengan skema cicilan potong gaji otomatis.</p>
        </div>
        <a href="{{ route('ess.cash-advances.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-sky-600/20 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Ajukan Kasbon Baru</span>
        </a>
    </div>

    @if(session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif
    @if(session('error'))
        <x-alert type="error">{{ session('error') }}</x-alert>
    @endif

    <!-- Status Saldo Pinjaman Aktif -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-gradient-to-tr from-sky-900 to-[#0B1E36] rounded-2xl p-5 text-white shadow-md">
            <div class="text-xs text-sky-200 font-bold uppercase tracking-wider">Sisa Saldo Kasbon Berjalan</div>
            <div class="text-2xl sm:text-3xl font-extrabold font-mono mt-1 text-white">
                Rp {{ number_format($activeDebt, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-sky-200/70 mt-1">Total sisa kewajiban cicilan yang akan dipotong pada payroll bulanan.</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex flex-col justify-center">
            <div class="text-xs text-slate-500 font-bold uppercase tracking-wider">Ketentuan Pengajuan</div>
            <p class="text-xs text-slate-600 mt-1">
                Tenor cicilan berkisar antara 1 s.d. 12 bulan. Setiap permohonan akan ditinjau oleh HR & Finance sebelum dicairkan.
            </p>
        </div>
    </div>

    <!-- Tabel Riwayat Kasbon -->
    <x-card class="p-0 overflow-hidden" title="Riwayat Pengajuan Kasbon">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200/80 uppercase text-[11px] tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">No. Pengajuan</th>
                        <th class="px-5 py-3.5">Tanggal</th>
                        <th class="px-5 py-3.5 text-right">Nominal Pinjaman</th>
                        <th class="px-5 py-3.5 text-center">Tenor</th>
                        <th class="px-5 py-3.5 text-right">Cicilan / Bln</th>
                        <th class="px-5 py-3.5 text-right">Sisa Pinjaman</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($cashAdvances as $ca)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-4 font-mono font-bold text-slate-900">
                                {{ $ca->request_number }}
                                <div class="text-[11px] text-slate-400 font-sans truncate max-w-xs">{{ $ca->reason }}</div>
                            </td>
                            <td class="px-5 py-4 text-slate-600">
                                {{ $ca->created_at->format('d M Y') }}
                            </td>
                            <td class="px-5 py-4 text-right font-mono font-bold text-slate-900">
                                Rp {{ number_format($ca->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 text-center font-mono font-medium text-slate-700">
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
                                        Menunggu Review
                                    </span>
                                @elseif($ca->status === 'ACTIVE')
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-semibold bg-sky-50 text-sky-700 border border-sky-200">
                                        Aktif Berjalan
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-slate-400 text-xs">
                                Anda belum memiliki riwayat pengajuan pinjaman/kasbon.
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
