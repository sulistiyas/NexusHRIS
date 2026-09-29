@extends('layouts.app')

@section('title', 'Surat Perintah Lembur (Overtime) - NexusHRIS')
@section('page_title', 'Pengajuan Lembur')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Pengajuan Lembur Mandiri (ESS)</h2>
            <p class="text-xs sm:text-sm text-slate-500">Kelola dan ajukan surat perintah lembur (Overtime) untuk diverifikasi oleh atasan.</p>
        </div>
        <a href="{{ route('ess.overtimes.create') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 sm:py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-sky-600/20 transition">
            <span>+</span>
            <span>Ajukan Lembur Baru</span>
        </a>
    </div>

    @if(session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif
    @if(session('error'))
        <x-alert type="error">{{ session('error') }}</x-alert>
    @endif

    <x-card class="overflow-hidden p-0">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800">Daftar Riwayat Lembur</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm text-slate-600">
                <thead class="bg-slate-100 text-slate-700 uppercase font-semibold tracking-wider text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Tanggal</th>
                        <th class="px-5 py-3.5">Jam Kerja Lembur</th>
                        <th class="px-5 py-3.5">Total Durasi</th>
                        <th class="px-5 py-3.5">Uraian Tugas / Alasan</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5">Diverifikasi Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($overtimes as $ot)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-4 font-mono font-bold text-slate-900">
                                {{ $ot->date->format('d M Y') }}
                            </td>
                            <td class="px-5 py-4 font-mono text-slate-700">
                                {{ substr($ot->start_time, 0, 5) }} s.d {{ substr($ot->end_time, 0, 5) }}
                            </td>
                            <td class="px-5 py-4 font-bold text-sky-700">
                                {{ $ot->total_hours }} Jam
                            </td>
                            <td class="px-5 py-4 max-w-xs truncate text-slate-500">
                                {{ $ot->reason }}
                            </td>
                            <td class="px-5 py-4">
                                @if($ot->status === 'APPROVED')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        DISETUJUI
                                    </span>
                                @elseif($ot->status === 'REJECTED')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        DITOLAK
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        MENUNGGU PERSETUJUAN
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-500">
                                {{ $ot->approvedBy->user->name ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                Belum ada riwayat pengajuan lembur.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($overtimes->hasPages())
            <div class="px-5 py-4 border-t border-slate-200">
                {{ $overtimes->links() }}
            </div>
        @endif
    </x-card>

</div>
@endsection
