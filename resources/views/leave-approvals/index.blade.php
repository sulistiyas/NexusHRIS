@extends('layouts.app')

@section('title', 'Persetujuan Cuti Anggota - NexusHRIS')
@section('page_title', 'Persetujuan Cuti')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Verifikasi & Persetujuan Cuti</h2>
            <p class="text-xs sm:text-sm text-slate-500">Tinjau permohonan cuti anggota tim dan berikan keputusan persetujuan.</p>
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
                        <th class="px-5 py-3.5">Jenis Cuti</th>
                        <th class="px-5 py-3.5">Tanggal Pelaksanaan</th>
                        <th class="px-5 py-3.5">Durasi</th>
                        <th class="px-5 py-3.5">Status Pengajuan</th>
                        <th class="px-5 py-3.5 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($leaveRequests as $req)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-4">
                                <span class="font-bold text-slate-900 block">{{ $req->employee->user->name }}</span>
                                <span class="text-xs text-slate-400">{{ $req->employee->department?->name ?? 'Dept' }}</span>
                            </td>
                            <td class="px-5 py-4 font-semibold text-slate-800">
                                {{ $req->leaveType->name }}
                            </td>
                            <td class="px-5 py-4 font-mono text-slate-700">
                                {{ $req->start_date->format('d M') }} - {{ $req->end_date->format('d M Y') }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-bold text-sky-700">{{ $req->total_days }} Hari</span>
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
                                <a href="{{ route('leave-approvals.show', $req) }}" class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs shadow-xs transition">
                                    Tinjau Permohonan
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                Tidak ada permohonan cuti yang perlu ditinjau saat ini.
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
