@extends('layouts.app')

@section('title', 'Detail Permohonan Cuti - NexusHRIS')
@section('page_title', 'Detail Cuti')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Rincian Permohonan Cuti</h2>
            <p class="text-xs sm:text-sm text-slate-500">Lihat status verifikasi dan linimasa persetujuan bertingkat.</p>
        </div>
        <a href="{{ route('ess.leaves.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-600 font-semibold text-xs sm:text-sm hover:bg-slate-100 transition">
            Kembali
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Info Detail Permohonan -->
        <div class="lg:col-span-2 space-y-6">
            <x-card>
                <div class="space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Jenis Cuti</span>
                        <span class="font-extrabold text-slate-800 text-sm">{{ $leaveRequest->leaveType->name }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 border-b border-slate-100 pb-3 text-xs">
                        <div>
                            <span class="text-slate-400 block uppercase font-bold">Tanggal Mulai</span>
                            <span class="font-bold text-slate-800 mt-1 block">{{ $leaveRequest->start_date->format('d F Y') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block uppercase font-bold">Tanggal Selesai</span>
                            <span class="font-bold text-slate-800 mt-1 block">{{ $leaveRequest->end_date->format('d F Y') }}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Durasi</span>
                        <span class="font-extrabold text-sky-700 text-sm">{{ $leaveRequest->total_days }} Hari Kerja</span>
                    </div>

                    <div class="border-b border-slate-100 pb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-1">Alasan Permohonan</span>
                        <p class="text-xs sm:text-sm text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-100">{{ $leaveRequest->reason }}</p>
                    </div>

                    @if($leaveRequest->attachment_path)
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-1">Berkas Lampiran</span>
                            <a href="{{ asset('storage/' . $leaveRequest->attachment_path) }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-sky-50 text-sky-700 font-semibold text-xs border border-sky-200 hover:bg-sky-100 transition">
                                <span>📎</span> Unduh / Lihat Lampiran Dokumen
                            </a>
                        </div>
                    @endif
                </div>
            </x-card>
        </div>

        <!-- Linimasa Persetujuan Bertingkat -->
        <div class="space-y-6">
            <x-card>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4 pb-2 border-b border-slate-100">
                    Alur Persetujuan (Workflow)
                </h3>

                <div class="space-y-6">
                    @forelse($leaveRequest->approvals as $approval)
                        <div class="relative pl-6 border-l-2 {{ $approval->status === 'APPROVED' ? 'border-emerald-500' : ($approval->status === 'REJECTED' ? 'border-rose-500' : 'border-slate-200') }}">
                            <div class="absolute -left-2 top-0 w-4 h-4 rounded-full {{ $approval->status === 'APPROVED' ? 'bg-emerald-500' : ($approval->status === 'REJECTED' ? 'bg-rose-500' : 'bg-slate-300') }}"></div>
                            
                            <span class="text-[11px] font-bold text-slate-400 block uppercase">
                                Level {{ $approval->approval_level }}: {{ $approval->approval_level === 1 ? 'Line Manager' : 'HR Admin' }}
                            </span>
                            <span class="font-bold text-xs text-slate-800 block mt-0.5">
                                {{ $approval->approver->user->name ?? 'Belum Ditugaskan' }}
                            </span>

                            <div class="mt-2">
                                @if($approval->status === 'APPROVED')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Disetujui ({{ $approval->acted_at?->format('d/m/Y H:i') }})
                                    </span>
                                @elseif($approval->status === 'REJECTED')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Ditolak ({{ $approval->acted_at?->format('d/m/Y H:i') }})
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Menunggu Tindakan
                                    </span>
                                @endif
                            </div>

                            @if($approval->remarks)
                                <p class="text-xs text-slate-500 italic mt-1.5 bg-slate-50 p-2 rounded-lg border border-slate-100">
                                    "{{ $approval->remarks }}"
                                </p>
                            @endif
                        </div>
                    @empty
                        <span class="text-xs text-slate-400">Belum ada tahapan persetujuan.</span>
                    @endforelse
                </div>
            </x-card>
        </div>

    </div>

</div>
@endsection
