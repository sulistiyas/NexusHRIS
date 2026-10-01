@extends('layouts.app')

@section('title', 'Slip Gaji Saya - NexusHRIS')
@section('page_title', 'Slip Gaji Saya')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Riwayat Slip Gaji Saya</h2>
            <p class="text-xs sm:text-sm text-slate-500">Akses dan unduh dokumen slip gaji bulanan resmi yang telah disetujui perusahaan.</p>
        </div>
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
                        <th class="px-5 py-3.5">Periode Penggajian</th>
                        <th class="px-5 py-3.5">No. Slip</th>
                        <th class="px-5 py-3.5 text-right">Pendapatan Kotor</th>
                        <th class="px-5 py-3.5 text-right">Total Potongan</th>
                        <th class="px-5 py-3.5 text-right">Gaji Bersih (THP)</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-center">Aksi Dokumen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payslips as $ps)
                        @php
                            $gross = $ps->basic_salary + $ps->total_allowances + $ps->total_overtime_pay;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-4 font-bold text-slate-900">
                                {{ \Carbon\Carbon::create($ps->payrollBatch->year, $ps->payrollBatch->month, 1)->translatedFormat('F Y') }}
                                <div class="text-[11px] text-slate-400 font-sans font-normal">
                                    Dibayar: {{ \Carbon\Carbon::parse($ps->payrollBatch->payment_date)->format('d M Y') }}
                                </div>
                            </td>
                            <td class="px-5 py-4 font-mono font-medium text-slate-700">
                                {{ $ps->slip_number }}
                            </td>
                            <td class="px-5 py-4 text-right font-mono text-slate-900">
                                Rp {{ number_format($gross, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 text-right font-mono text-rose-600">
                                - Rp {{ number_format($ps->total_deductions, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 text-right font-mono font-bold text-sky-800 text-sm sm:text-base">
                                Rp {{ number_format($ps->net_salary, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if($ps->payrollBatch->status === 'PAID')
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Sudah Ditransfer
                                    </span>
                                @else
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-semibold bg-sky-50 text-sky-700 border border-sky-200">
                                        Disetujui
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('ess.payslips.show', $ps->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                                        <span>🔍</span>
                                        <span>Rincian</span>
                                    </a>
                                    <a href="{{ route('ess.payslips.download', $ps->id) }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs shadow-xs transition">
                                        <span>📄</span>
                                        <span>Unduh PDF</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-slate-400 text-xs">
                                Belum ada riwayat slip gaji resmi yang diterbitkan untuk akun Anda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payslips->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $payslips->links() }}
            </div>
        @endif
    </x-card>

</div>
@endsection
