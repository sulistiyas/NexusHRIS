@extends('layouts.app')

@section('title', 'Detail Klaim ' . $reimbursement->claim_number . ' - NexusHRIS')
@section('page_title', 'Detail Reimbursement')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Navigation Back -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('ess.reimbursements.index') }}" class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">{{ $reimbursement->claim_number }}</h2>
                    @if($reimbursement->status === 'PENDING')
                        <x-badge color="amber">Menunggu Persetujuan</x-badge>
                    @elseif($reimbursement->status === 'APPROVED')
                        <x-badge color="sky">Disetujui (Menunggu Pencairan Finance)</x-badge>
                    @elseif($reimbursement->status === 'DISBURSED')
                        <x-badge color="emerald">Sudah Dicairkan</x-badge>
                    @elseif($reimbursement->status === 'REJECTED')
                        <x-badge color="rose">Ditolak</x-badge>
                    @endif
                </div>
                <p class="text-xs text-slate-500">Diajukan pada {{ $reimbursement->created_at?->translatedFormat('d F Y, H:i') }} WIB</p>
            </div>
        </div>
    </div>

    <!-- Rincian Informasi Klaim -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <div class="md:col-span-2 space-y-6">
            <x-card title="Rincian Pengeluaran">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm">
                    <div>
                        <dt class="text-slate-400 font-medium">Kategori Biaya</dt>
                        <dd class="text-slate-800 font-bold mt-0.5">{{ $reimbursement->category?->name ?? 'Umum' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400 font-medium">Tanggal Transaksi</dt>
                        <dd class="text-slate-800 font-semibold mt-0.5">{{ $reimbursement->claim_date?->translatedFormat('d F Y') }}</dd>
                    </div>
                    <div class="sm:col-span-2 bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <dt class="text-slate-500 font-medium">Total Nominal Klaim</dt>
                        <dd class="text-2xl font-extrabold font-mono text-slate-900 mt-1">
                            Rp {{ number_format((float) $reimbursement->total_amount, 0, ',', '.') }}
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-slate-400 font-medium">Keterangan / Keperluan</dt>
                        <dd class="text-slate-700 mt-1 bg-white p-3 rounded-xl border border-slate-200">
                            {{ $reimbursement->description ?: 'Tidak ada keterangan tambahan.' }}
                        </dd>
                    </div>
                </dl>
            </x-card>

            <!-- Berkas Struk Lampiran -->
            <x-card title="Berkas Lampiran Struk Kuitansi">
                @if($reimbursement->attachments->isNotEmpty())
                    <ul class="divide-y divide-slate-100">
                        @foreach($reimbursement->attachments as $attachment)
                            <li class="py-3 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center font-bold text-sm">
                                        📎
                                    </div>
                                    <div>
                                        <p class="text-xs sm:text-sm font-semibold text-slate-800">{{ $attachment->file_name }}</p>
                                        <p class="text-[11px] text-slate-400">Diunggah bersama pengajuan</p>
                                    </div>
                                </div>
                                <a 
                                    href="{{ route('reimbursements.attachments.download', $attachment) }}" 
                                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-sky-50 text-sky-600 hover:bg-sky-100 font-semibold text-xs transition"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                    <span>Unduh Bukti</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-xs text-slate-400 italic">Tidak ada berkas bukti kuitansi yang dilampirkan.</p>
                @endif
            </x-card>
        </div>

        <!-- Sidebar Timeline Status & Approver -->
        <div class="space-y-6">
            <x-card title="Riwayat Proses & Persetujuan">
                <ol class="relative border-l border-slate-200 ml-3 space-y-6 text-xs">
                    <li class="mb-4 ml-4">
                        <div class="absolute w-3 h-3 bg-sky-600 rounded-full -left-1.5 border border-white"></div>
                        <time class="mb-1 text-[11px] font-normal leading-none text-slate-400">Tahap 1</time>
                        <h4 class="text-xs font-semibold text-slate-900">Pengajuan Klaim</h4>
                        <p class="text-[11px] text-slate-500">Diajukan oleh {{ $reimbursement->employee?->full_name }}</p>
                    </li>
                    <li class="mb-4 ml-4">
                        <div class="absolute w-3 h-3 {{ in_array($reimbursement->status, ['APPROVED', 'DISBURSED']) ? 'bg-emerald-600' : ($reimbursement->status === 'REJECTED' ? 'bg-rose-500' : 'bg-slate-300') }} rounded-full -left-1.5 border border-white"></div>
                        <time class="mb-1 text-[11px] font-normal leading-none text-slate-400">Tahap 2</time>
                        <h4 class="text-xs font-semibold text-slate-900">Verifikasi Atasan / HR</h4>
                        <p class="text-[11px] text-slate-500">
                            @if($reimbursement->approvedBy)
                                Diverifikasi oleh {{ $reimbursement->approvedBy->full_name }}
                            @elseif($reimbursement->status === 'REJECTED')
                                Pengajuan ditolak
                            @else
                                Menunggu verifikasi
                            @endif
                        </p>
                    </li>
                    <li class="ml-4">
                        <div class="absolute w-3 h-3 {{ $reimbursement->status === 'DISBURSED' ? 'bg-emerald-600' : 'bg-slate-300' }} rounded-full -left-1.5 border border-white"></div>
                        <time class="mb-1 text-[11px] font-normal leading-none text-slate-400">Tahap 3</time>
                        <h4 class="text-xs font-semibold text-slate-900">Pencairan Dana (Finance)</h4>
                        <p class="text-[11px] text-slate-500">
                            @if($reimbursement->disbursed_at)
                                Dicairkan pada {{ $reimbursement->disbursed_at->translatedFormat('d M Y, H:i') }} WIB
                            @else
                                Menunggu transfer finance
                            @endif
                        </p>
                    </li>
                </ol>
            </x-card>
        </div>

    </div>

</div>
@endsection
