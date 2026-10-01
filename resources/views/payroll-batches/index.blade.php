@extends('layouts.app')

@section('title', 'Periode Penggajian Bulanan - NexusHRIS')
@section('page_title', 'Periode Payroll')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Periode Penggajian (Payroll Batches)</h2>
            <p class="text-xs sm:text-sm text-slate-500">Kelola siklus perhitungan gaji bulanan, rekonsiliasi lembur & kasbon massal.</p>
        </div>
        <a href="{{ route('payroll-batches.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-sky-600/20 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Proses Payroll Baru</span>
        </a>
    </div>

    @if(session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif
    @if(session('error'))
        <x-alert type="error">{{ session('error') }}</x-alert>
    @endif

    <!-- Table Card -->
    <x-card class="p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200/80 uppercase text-[11px] tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">No. Batch</th>
                        <th class="px-5 py-3.5">Periode</th>
                        <th class="px-5 py-3.5">Rentang Cut-off</th>
                        <th class="px-5 py-3.5 text-center">Jml Karyawan</th>
                        <th class="px-5 py-3.5 text-right">Total Bruto</th>
                        <th class="px-5 py-3.5 text-right">Total Net (THP)</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($batches as $b)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-4 font-mono font-bold text-slate-900">
                                {{ $b->batch_number }}
                                <div class="text-[11px] text-slate-400 font-sans">Transfer: {{ \Carbon\Carbon::parse($b->payment_date)->format('d M Y') }}</div>
                            </td>
                            <td class="px-5 py-4 font-medium text-slate-800">
                                {{ \Carbon\Carbon::create($b->year, $b->month, 1)->translatedFormat('F Y') }}
                            </td>
                            <td class="px-5 py-4 text-slate-600 font-mono text-[11px]">
                                {{ \Carbon\Carbon::parse($b->cut_off_start)->format('d M Y') }} &mdash; {{ \Carbon\Carbon::parse($b->cut_off_end)->format('d M Y') }}
                            </td>
                            <td class="px-5 py-4 text-center font-mono font-bold text-slate-700">
                                {{ $b->payslips_count }} Slip
                            </td>
                            <td class="px-5 py-4 text-right font-mono text-slate-900">
                                Rp {{ number_format($b->total_gross, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 text-right font-mono font-bold text-sky-800">
                                Rp {{ number_format($b->total_net, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if($b->status === 'GENERATED' || $b->status === 'DRAFT')
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        Perlu Review
                                    </span>
                                @elseif($b->status === 'APPROVED')
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-semibold bg-sky-50 text-sky-700 border border-sky-200">
                                        Disetujui
                                    </span>
                                @else
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Dibayarkan (PAID)
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                <a href="{{ route('payroll-batches.show', $b->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-sky-50 text-sky-700 hover:bg-sky-100 font-semibold text-xs border border-sky-200 transition">
                                    <span>🔍</span>
                                    <span>Rincian</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-8 text-center text-slate-400 text-xs">
                                Belum ada riwayat periode batch payroll yang diproses.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($batches->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $batches->links() }}
            </div>
        @endif
    </x-card>

</div>
@endsection
