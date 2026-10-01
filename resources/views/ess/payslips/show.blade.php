@extends('layouts.app')

@section('title', 'Rincian Slip Gaji - NexusHRIS')
@section('page_title', 'Rincian Slip Gaji')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Navigation Back & Download Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('ess.payslips.index') }}" class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Rincian Slip Gaji</h2>
                <p class="text-xs sm:text-sm text-slate-500 font-mono">{{ $payslip->slip_number }} &bull; Periode {{ \Carbon\Carbon::create($payslip->payrollBatch->year, $payslip->payrollBatch->month, 1)->translatedFormat('F Y') }}</p>
            </div>
        </div>

        <a href="{{ route('ess.payslips.download', $payslip->id) }}" target="_blank" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-sky-600/20 transition">
            <span>📄</span>
            <span>Unduh Dokumen PDF Resmi</span>
        </a>
    </div>

    <!-- Banner Ringkasan Finansial -->
    <div class="bg-gradient-to-tr from-sky-950 to-[#0B1E36] rounded-2xl p-6 text-white shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="text-xs text-sky-300 font-bold uppercase tracking-wider">Gaji Bersih Diterima (Take Home Pay)</span>
                <div class="text-2xl sm:text-4xl font-extrabold font-mono mt-1 text-white">
                    Rp {{ number_format($payslip->net_salary, 0, ',', '.') }}
                </div>
                <p class="text-xs text-slate-300 mt-1">Ditransfer ke Rekening: <span class="font-bold text-white">{{ $payslip->employee->bank_name ?? 'BCA' }} - {{ $payslip->bank_account_no ?? ($payslip->employee->bank_account_no ?? '-') }}</span></p>
            </div>
            <div class="text-left sm:text-right border-t sm:border-t-0 border-sky-800/60 pt-3 sm:pt-0">
                <span class="text-xs text-slate-300">Tanggal Transfer Resmi</span>
                <div class="text-sm sm:text-base font-bold text-sky-200 font-mono mt-0.5">
                    {{ \Carbon\Carbon::parse($payslip->payrollBatch->payment_date)->translatedFormat('d F Y') }}
                </div>
            </div>
        </div>
    </div>

    <!-- 2 Kolom Komponen Gaji: Pendapatan & Potongan -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Kolom Pendapatan -->
        <x-card title="Rincian Pendapatan (Earnings)" subtitle="Gaji pokok, tunjangan tetap & lembur">
            <div class="space-y-3">
                @foreach($earnings as $item)
                    <div class="flex justify-between items-center text-xs sm:text-sm py-2 border-b border-slate-100">
                        <span class="text-slate-700 font-medium">{{ $item->component_name }}</span>
                        <span class="font-mono font-bold text-slate-900">Rp {{ number_format($item->amount, 0, ',', '.') }}</span>
                    </div>
                @endforeach
                <div class="flex justify-between items-center text-sm pt-3 font-bold text-sky-900">
                    <span>Total Pendapatan Kotor</span>
                    <span class="font-mono">Rp {{ number_format($earnings->sum('amount'), 0, ',', '.') }}</span>
                </div>
            </div>
        </x-card>

        <!-- Kolom Potongan -->
        <x-card title="Rincian Potongan (Deductions)" subtitle="Iuran regulasi BPJS, pajak PPh 21 & kasbon">
            <div class="space-y-3">
                @foreach($deductions as $item)
                    <div class="flex justify-between items-center text-xs sm:text-sm py-2 border-b border-slate-100">
                        <span class="text-slate-700 font-medium">{{ $item->component_name }}</span>
                        <span class="font-mono font-bold text-rose-600">- Rp {{ number_format($item->amount, 0, ',', '.') }}</span>
                    </div>
                @endforeach
                <div class="flex justify-between items-center text-sm pt-3 font-bold text-rose-700">
                    <span>Total Potongan</span>
                    <span class="font-mono">- Rp {{ number_format($deductions->sum('amount'), 0, ',', '.') }}</span>
                </div>
            </div>
        </x-card>

    </div>

</div>
@endsection
