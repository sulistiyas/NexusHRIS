@extends('layouts.app')

@section('title', 'Rekapitulasi Batch Penggajian - NexusHRIS')
@section('page_title', 'Rincian Batch')

@section('content')
<div class="space-y-6">

    <!-- Header Navigation Back & Batch Status -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('payroll-batches.index') }}" class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Batch: {{ $payrollBatch->batch_number }}</h2>
                <p class="text-xs sm:text-sm text-slate-500">
                    Periode {{ \Carbon\Carbon::create($payrollBatch->year, $payrollBatch->month, 1)->translatedFormat('F Y') }} &bull; Cut-off: {{ \Carbon\Carbon::parse($payrollBatch->cut_off_start)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($payrollBatch->cut_off_end)->format('d/m/Y') }}
                </p>
            </div>
        </div>

        <!-- Tombol Aksi Status Batch -->
        <div class="flex items-center gap-2">
            @if($payrollBatch->status === 'GENERATED' || $payrollBatch->status === 'DRAFT')
                <form method="POST" action="{{ route('payroll-batches.approve', $payrollBatch->id) }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-xs transition">
                        ✓ Setujui Batch (Approve)
                    </button>
                </form>
            @elseif($payrollBatch->status === 'APPROVED')
                <form method="POST" action="{{ route('payroll-batches.pay', $payrollBatch->id) }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-semibold shadow-xs transition">
                        💰 Tandai Selesai Dibayarkan (PAID)
                    </button>
                </form>
            @else
                <span class="inline-flex px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    ✓ STATUS: TELAH DIBAYARKAN (PAID)
                </span>
            @endif
        </div>
    </div>

    @if(session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif
    @if(session('error'))
        <x-alert type="error">{{ session('error') }}</x-alert>
    @endif

    <!-- 3 Kolom Ringkasan Finansial -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-bold text-slate-500 uppercase">Total Pendapatan Kotor (Gross)</span>
            <div class="text-2xl font-black text-slate-900 font-mono mt-1">
                Rp {{ number_format($payrollBatch->total_gross, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-slate-400 mt-0.5">Gaji pokok, tunjangan, dan lembur.</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-bold text-rose-600 uppercase">Total Potongan (Deductions)</span>
            <div class="text-2xl font-black text-rose-700 font-mono mt-1">
                Rp {{ number_format($payrollBatch->total_deductions, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-slate-400 mt-0.5">BPJS TK, BPJS Kesehatan, PPh 21 & Kasbon.</p>
        </div>

        <div class="bg-gradient-to-tr from-sky-900 to-[#0B1E36] p-5 rounded-2xl text-white shadow-md">
            <span class="text-xs font-bold text-sky-200 uppercase">Total Gaji Bersih (Net / THP)</span>
            <div class="text-2xl font-black font-mono mt-1 text-white">
                Rp {{ number_format($payrollBatch->total_net, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-sky-200/70 mt-0.5">Total dana yang dibayarkan ke karyawan.</p>
        </div>
    </div>

    <!-- Tabel Rekapitulasi Slip Gaji Karyawan -->
    <x-card class="p-0 overflow-hidden" title="Rekapitulasi Slip Gaji Karyawan ({{ $payrollBatch->payslips->count() }} Orang)">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200/80 uppercase text-[11px] tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Karyawan</th>
                        <th class="px-5 py-3.5">No. Slip</th>
                        <th class="px-5 py-3.5 text-right">Gaji Pokok</th>
                        <th class="px-5 py-3.5 text-right">Tunjangan</th>
                        <th class="px-5 py-3.5 text-right">Lembur</th>
                        <th class="px-5 py-3.5 text-right">Potongan</th>
                        <th class="px-5 py-3.5 text-right">Take Home Pay</th>
                        <th class="px-5 py-3.5 text-center">Cetak Slip</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payrollBatch->payslips as $slip)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-900">{{ $slip->employee->user->name ?? '-' }}</div>
                                <div class="text-[11px] text-slate-500 font-mono">{{ $slip->employee->employee_code ?? '-' }} &bull; {{ $slip->employee->department->name ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4 font-mono text-slate-700">
                                {{ $slip->slip_number }}
                                <div class="text-[11px] text-slate-400 font-sans">Bank: {{ $slip->bank_account_no ?? 'BCA' }}</div>
                            </td>
                            <td class="px-5 py-4 text-right font-mono text-slate-900">
                                Rp {{ number_format($slip->basic_salary, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 text-right font-mono text-emerald-700">
                                Rp {{ number_format($slip->total_allowances, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 text-right font-mono text-amber-700">
                                Rp {{ number_format($slip->total_overtime_pay, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 text-right font-mono text-rose-700">
                                - Rp {{ number_format($slip->total_deductions, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 text-right font-mono font-bold text-sky-800 text-sm">
                                Rp {{ number_format($slip->net_salary, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 text-center">
                                <a href="{{ route('payslips.download', $slip->id) }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs border border-slate-300 transition">
                                    <span>📄</span>
                                    <span>PDF</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-8 text-center text-slate-400 text-xs">
                                Tidak ada slip gaji yang berhasil dikalkulasi dalam batch ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

</div>
@endsection
