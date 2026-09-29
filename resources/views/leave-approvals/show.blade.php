@extends('layouts.app')

@section('title', 'Tinjau Permohonan Cuti - NexusHRIS')
@section('page_title', 'Tinjau Cuti')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Keputusan Persetujuan Cuti</h2>
            <p class="text-xs sm:text-sm text-slate-500">Tinjau kelengkapan berkas dan tentukan persetujuan atau penolakan.</p>
        </div>
        <a href="{{ route('leave-approvals.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-600 font-semibold text-xs sm:text-sm hover:bg-slate-100 transition">
            Kembali
        </a>
    </div>

    @if(session('error'))
        <x-alert type="error">{{ session('error') }}</x-alert>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Rincian Pemohon -->
        <div class="lg:col-span-7 space-y-6">
            <x-card>
                <div class="space-y-4">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                        <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-700 font-extrabold flex items-center justify-center text-sm">
                            {{ substr($leaveRequest->employee->user->name, 0, 2) }}
                        </div>
                        <div>
                            <h3 class="font-extrabold text-slate-900 text-sm">{{ $leaveRequest->employee->user->name }}</h3>
                            <span class="text-xs text-slate-400">{{ $leaveRequest->employee->department?->name ?? 'Departemen' }} (NIP: {{ $leaveRequest->employee->employee_code }})</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 border-b border-slate-100 pb-3 text-xs">
                        <div>
                            <span class="text-slate-400 block uppercase font-bold">Jenis Cuti</span>
                            <span class="font-bold text-slate-800 mt-1 block">{{ $leaveRequest->leaveType->name }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block uppercase font-bold">Total Hari</span>
                            <span class="font-bold text-sky-700 mt-1 block">{{ $leaveRequest->total_days }} Hari Kerja</span>
                        </div>
                    </div>

                    <div class="border-b border-slate-100 pb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-1">Periode Cuti</span>
                        <span class="text-xs sm:text-sm font-semibold text-slate-800">
                            {{ $leaveRequest->start_date->format('d F Y') }} s.d {{ $leaveRequest->end_date->format('d F Y') }}
                        </span>
                    </div>

                    <div class="border-b border-slate-100 pb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-1">Alasan Pengajuan</span>
                        <p class="text-xs sm:text-sm text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-100">{{ $leaveRequest->reason }}</p>
                    </div>

                    @if($leaveRequest->attachment_path)
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-1">Lampiran Berkas / Bukti Dokter</span>
                            <a href="{{ asset('storage/' . $leaveRequest->attachment_path) }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-sky-50 text-sky-700 font-semibold text-xs border border-sky-200 hover:bg-sky-100 transition">
                                <span>📎</span> Buka Lampiran Berkas
                            </a>
                        </div>
                    @endif
                </div>
            </x-card>
        </div>

        <!-- Formulir Tindakan (Approve / Reject) -->
        <div class="lg:col-span-5 space-y-6">
            <x-card>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 pb-2 border-b border-slate-100">
                    Tindakan Persetujuan
                </h3>

                @if($leaveRequest->status === 'PENDING')
                    <div class="space-y-4">
                        <div>
                            <label for="approval_remarks" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                Catatan / Komentar (Opsional)
                            </label>
                            <textarea id="approval_remarks" rows="3" placeholder="Tuliskan catatan alasan jika menolak..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10"></textarea>
                        </div>

                        <div class="flex items-center gap-3 pt-2">
                            <!-- Tombol Setujui -->
                            <form method="POST" action="{{ route('leave-approvals.approve', $leaveRequest) }}" class="flex-1" onsubmit="this.remarks.value = document.getElementById('approval_remarks').value;">
                                @csrf
                                <input type="hidden" name="remarks">
                                <button type="submit" class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm shadow-md shadow-emerald-600/20 transition">
                                    ✓ Setujui
                                </button>
                            </form>

                            <!-- Tombol Tolak -->
                            <form method="POST" action="{{ route('leave-approvals.reject', $leaveRequest) }}" class="flex-1" onsubmit="this.remarks.value = document.getElementById('approval_remarks').value;">
                                @csrf
                                <input type="hidden" name="remarks">
                                <button type="submit" class="w-full py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs sm:text-sm shadow-md shadow-rose-600/20 transition">
                                    ✕ Tolak
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="p-4 rounded-xl {{ $leaveRequest->status === 'APPROVED' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-rose-50 text-rose-800 border-rose-200' }} border text-center text-xs">
                        Permohonan ini telah berstatus <span class="font-bold">{{ $leaveRequest->status }}</span>.
                    </div>
                @endif
            </x-card>
        </div>

    </div>

</div>
@endsection
