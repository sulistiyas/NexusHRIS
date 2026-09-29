@extends('layouts.app')

@section('title', 'Verifikasi & Persetujuan Lembur - NexusHRIS')
@section('page_title', 'Verifikasi Lembur')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Verifikasi Surat Perintah Lembur</h2>
            <p class="text-xs sm:text-sm text-slate-500">Tinjau permohonan lembur bawahan dan berikan persetujuan untuk perhitungan payroll.</p>
        </div>
    </div>

    @if(session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif
    @if(session('error'))
        <x-alert type="error">{{ session('error') }}</x-alert>
    @endif

    <x-card class="overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm text-slate-600">
                <thead class="bg-slate-100 text-slate-700 uppercase font-semibold tracking-wider text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Karyawan</th>
                        <th class="px-5 py-3.5">Tanggal</th>
                        <th class="px-5 py-3.5">Jam Lembur</th>
                        <th class="px-5 py-3.5">Durasi</th>
                        <th class="px-5 py-3.5">Uraian Tugas</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($overtimes as $ot)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-4">
                                <span class="font-bold text-slate-900 block">{{ $ot->employee->user->name }}</span>
                                <span class="text-xs text-slate-400">{{ $ot->employee->department?->name ?? 'Dept' }}</span>
                            </td>
                            <td class="px-5 py-4 font-mono font-semibold text-slate-800">
                                {{ $ot->date->format('d M Y') }}
                            </td>
                            <td class="px-5 py-4 font-mono text-slate-700">
                                {{ substr($ot->start_time, 0, 5) }} - {{ substr($ot->end_time, 0, 5) }}
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
                                        PENDING
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                @if($ot->status === 'PENDING')
                                    <div class="inline-flex items-center gap-2">
                                        <form method="POST" action="{{ route('overtime-approvals.approve', $ot) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-xs transition">
                                                Setujui
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('overtime-approvals.reject', $ot) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs shadow-xs transition">
                                                Tolak
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 italic">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-slate-400">
                                Tidak ada permohonan lembur yang perlu diverifikasi.
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
