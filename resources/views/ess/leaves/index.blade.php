@extends('layouts.app')

@section('title', 'Manajemen Cuti & Izin - NexusHRIS')
@section('page_title', 'Cuti & Izin')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Portal Cuti & Izin Karyawan</h2>
            <p class="text-xs sm:text-sm text-slate-500">Pantau sisa kuota saldo cuti tahun {{ $year }} dan ajukan cuti baru.</p>
        </div>
        <a href="{{ route('ess.leaves.create') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 sm:py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-sky-600/20 transition">
            <span>+</span>
            <span>Ajukan Cuti Baru</span>
        </a>
    </div>

    <!-- Alert Notifikasi -->
    @if(session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif
    @if(session('error'))
        <x-alert type="error">{{ session('error') }}</x-alert>
    @endif

    <!-- Kartu Saldo Cuti -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($balances as $bal)
            <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">{{ $bal->leaveType->code }}</span>
                    <h3 class="font-extrabold text-slate-800 text-sm mt-0.5">{{ $bal->leaveType->name }}</h3>
                </div>
                <div class="mt-4 flex items-baseline justify-between pt-3 border-t border-slate-100">
                    <div>
                        <span class="text-2xl font-extrabold text-sky-700">{{ $bal->remaining_days }}</span>
                        <span class="text-xs text-slate-400 font-medium">Hari Tersisa</span>
                    </div>
                    <span class="text-[11px] text-slate-400">
                        {{ $bal->used_days }} / {{ $bal->total_entitled }} Hari Terpakai
                    </span>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Tabel Riwayat Pengajuan Cuti -->
    <x-card class="overflow-hidden p-0">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800">Riwayat Permohonan Cuti</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm text-slate-600">
                <thead class="bg-slate-100 text-slate-700 uppercase font-semibold tracking-wider text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Jenis Cuti</th>
                        <th class="px-5 py-3.5">Tanggal Pelaksanaan</th>
                        <th class="px-5 py-3.5">Durasi</th>
                        <th class="px-5 py-3.5">Alasan</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($leaveRequests as $req)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-4 font-semibold text-slate-900">
                                {{ $req->leaveType->name }}
                            </td>
                            <td class="px-5 py-4 font-mono text-slate-700">
                                {{ $req->start_date->format('d M Y') }} s.d {{ $req->end_date->format('d M Y') }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-bold text-sky-700">{{ $req->total_days }} Hari Kerja</span>
                            </td>
                            <td class="px-5 py-4 max-w-xs truncate text-slate-500">
                                {{ $req->reason }}
                            </td>
                            <td class="px-5 py-4">
                                @if($req->status === 'APPROVED')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        DISETUJUI
                                    </span>
                                @elseif($req->status === 'REJECTED')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        DITOLAK
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        MENUNGGU PERSETUJUAN
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('ess.leaves.show', $req) }}" class="px-3 py-1.5 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-100 font-semibold text-xs transition">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                Anda belum memiliki riwayat pengajuan cuti.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leaveRequests->hasPages())
            <div class="px-5 py-4 border-t border-slate-200">
                {{ $leaveRequests->links() }}
            </div>
        @endif
    </x-card>

</div>
@endsection
