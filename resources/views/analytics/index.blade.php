@extends('layouts.app')

@section('title', 'Executive Analytics Dashboard - NexusHRIS')
@section('page_title', 'Analitik Eksekutif')

@section('content')
<div class="space-y-6">

    <!-- Top Executive Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Executive Analytics & Business Intelligence</h2>
            <p class="text-xs sm:text-sm text-slate-500">Pemantauan metrik strategis: rasio presensi real-time, tren payroll, dan distribusi SDM.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold text-xs sm:text-sm transition">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span>Unduh Laporan (.xlsx)</span>
            </a>
            <form method="POST" action="{{ route('analytics.refresh') }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-sky-600/20 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span>Segarkan Data</span>
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif

    <!-- 4 Baris Kartu Counter Metrik Utama -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Kartu 1: Total SDM Aktif -->
        <div class="bg-gradient-to-tr from-sky-950 to-[#0B1E36] rounded-2xl p-5 text-white shadow-md relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-sky-300">Karyawan Aktif</span>
                <span class="p-2 rounded-xl bg-sky-900/60 text-sky-300 text-sm">👥</span>
            </div>
            <div class="text-3xl font-extrabold font-mono mt-3">
                {{ $metrics['headcount']['active'] }}
                <span class="text-sm font-normal text-sky-300">/ {{ $metrics['headcount']['total'] }} Total</span>
            </div>
            <p class="text-[11px] text-sky-200/70 mt-1">
                {{ $metrics['headcount']['inactive'] }} Karyawan Nonaktif / Resign
            </p>
        </div>

        <!-- Kartu 2: Rasio Kehadiran Hari Ini -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Tingkat Hadir Hari Ini</span>
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600 text-sm">📍</span>
            </div>
            <div class="text-3xl font-extrabold font-mono text-slate-900 mt-3">
                {{ $metrics['today_attendance']['attendance_rate'] }}%
            </div>
            <p class="text-[11px] text-emerald-700 font-semibold mt-1">
                {{ $metrics['today_attendance']['total_present'] }} dari {{ $metrics['today_attendance']['total_expected'] }} karyawan hadir
            </p>
        </div>

        <!-- Kartu 3: Total Pengeluaran Klaim Bulan Ini -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Reimbursement Bulan Ini</span>
                <span class="p-2 rounded-xl bg-amber-50 text-amber-600 text-sm">💳</span>
            </div>
            <div class="text-2xl font-extrabold font-mono text-slate-900 mt-3">
                Rp {{ number_format($metrics['reimbursement']['disbursed_month_amount'], 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-amber-700 font-semibold mt-1">
                {{ $metrics['reimbursement']['disbursed_month_count'] }} Klaim Cair • {{ $metrics['reimbursement']['pending_count'] }} Menunggu
            </p>
        </div>

        <!-- Kartu 4: Utilisasi Inventaris Aset -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Aset Dipinjamkan</span>
                <span class="p-2 rounded-xl bg-sky-50 text-sky-600 text-sm">💻</span>
            </div>
            <div class="text-3xl font-extrabold font-mono text-slate-900 mt-3">
                {{ $metrics['assets']['assigned'] }}
                <span class="text-sm font-normal text-slate-400">/ {{ $metrics['assets']['total'] }} Aset</span>
            </div>
            <p class="text-[11px] text-sky-700 font-semibold mt-1">
                {{ $metrics['assets']['available'] }} Tersedia • {{ $metrics['assets']['under_maintenance'] }} Servis
            </p>
        </div>

    </div>

    <!-- Grid Dua Grafik Utama -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Grafik 1: Tren Pengeluaran Payroll 6 Bulan Terakhir -->
        <div class="lg:col-span-2">
            <x-card title="Tren Pengeluaran Payroll (6 Bulan Terakhir)">
                <div class="h-80 w-full relative">
                    <canvas 
                        id="chart-payroll-trend" 
                        data-payroll="{{ json_encode($metrics['payroll_trends']) }}"
                    ></canvas>
                </div>
            </x-card>
        </div>

        <!-- Grafik 2: Rasio Kehadiran Hari Ini (Donut) -->
        <div>
            <x-card title="Distribusi Kehadiran Hari Ini">
                <div class="h-80 w-full relative flex items-center justify-center">
                    <canvas 
                        id="chart-attendance-donut" 
                        data-attendance="{{ json_encode($metrics['today_attendance']) }}"
                    ></canvas>
                </div>
            </x-card>
        </div>

    </div>

    <!-- Grid Distribusi Headcount -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Distribusi Departemen -->
        <x-card title="Sebaran Headcount per Departemen">
            <div class="h-72 w-full relative">
                <canvas 
                    id="chart-department-headcount" 
                    data-departments="{{ json_encode($metrics['headcount']['by_department']) }}"
                ></canvas>
            </div>
        </x-card>

        <!-- Distribusi Kantor Cabang -->
        <x-card title="Ringkasan Karyawan per Kantor Cabang">
            <div class="space-y-3">
                @forelse($metrics['headcount']['by_branch'] as $branch)
                    @php
                        $percentage = $metrics['headcount']['active'] > 0 
                            ? round(($branch['count'] / $metrics['headcount']['active']) * 100, 1) 
                            : 0;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-xs font-semibold mb-1">
                            <span class="text-slate-800">{{ $branch['name'] }}</span>
                            <span class="font-mono text-slate-500">{{ $branch['count'] }} orang ({{ $percentage }}%)</span>
                        </div>
                        <progress class="w-full h-2.5 rounded-full accent-sky-600 bg-slate-100" value="{{ $percentage }}" max="100"></progress>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 italic">Belum ada data cabang.</p>
                @endforelse
            </div>
        </x-card>

    </div>

</div>
@endsection
