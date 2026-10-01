@extends('layouts.app')

@section('title', 'Verifikasi Klaim ' . $reimbursement->claim_number . ' - NexusHRIS')
@section('page_title', 'Verifikasi Reimbursement')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Navigation Back -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('reimbursement-approvals.index') }}" class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
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
                        <x-badge color="sky">Disetujui (Siap Dicairkan)</x-badge>
                    @elseif($reimbursement->status === 'DISBURSED')
                        <x-badge color="emerald">Sudah Dicairkan</x-badge>
                    @elseif($reimbursement->status === 'REJECTED')
                        <x-badge color="rose">Ditolak</x-badge>
                    @endif
                </div>
                <p class="text-xs text-slate-500">Diajukan pada {{ $reimbursement->created_at?->translatedFormat('d F Y, H:i') }} WIB</p>
            </div>
        </div>

        <!-- Tombol Aksi Langsung -->
        <div class="flex items-center gap-2">
            @if($reimbursement->status === 'PENDING')
                <form method="POST" action="{{ route('reimbursement-approvals.approve', $reimbursement) }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-emerald-600/20 transition">
                        Setujui Klaim
                    </button>
                </form>
                <form method="POST" action="{{ route('reimbursement-approvals.reject', $reimbursement) }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-rose-600/20 transition">
                        Tolak Klaim
                    </button>
                </form>
            @elseif($reimbursement->status === 'APPROVED')
                @role('super_admin|hr_admin')
                    <form method="POST" action="{{ route('reimbursement-approvals.disburse', $reimbursement) }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-blue-600/20 transition">
                            Cairkan Dana (Disburse)
                        </button>
                    </form>
                @endrole
            @endif
        </div>
    </div>

    @if(session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif
    @if(session('error'))
        <x-alert type="error">{{ session('error') }}</x-alert>
    @endif

    <!-- Kartu Profil Pemohon & Rincian Klaim -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <div class="md:col-span-2 space-y-6">
            <x-card title="Rincian Pengeluaran">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm">
                    <div>
                        <dt class="text-slate-400 font-medium">Kategori Biaya</dt>
                        <dd class="text-slate-800 font-bold mt-0.5">{{ $reimbursement->category?->name ?? 'Umum' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400 font-medium">Tanggal Transaksi Nota</dt>
                        <dd class="text-slate-800 font-semibold mt-0.5">{{ $reimbursement->claim_date?->translatedFormat('d F Y') }}</dd>
                    </div>
                    <div class="sm:col-span-2 bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <dt class="text-slate-500 font-medium">Total Nominal Klaim</dt>
                        <dd class="text-2xl font-extrabold font-mono text-slate-900 mt-1">
                            Rp {{ number_format((float) $reimbursement->total_amount, 0, ',', '.') }}
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-slate-400 font-medium">Keterangan / Keperluan Klaim</dt>
                        <dd class="text-slate-700 mt-1 bg-white p-3 rounded-xl border border-slate-200">
                            {{ $reimbursement->description ?: 'Tidak ada keterangan tambahan.' }}
                        </dd>
                    </div>
                </dl>
            </x-card>

            <!-- Lampiran Berkas Bukti -->
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
                                        <p class="text-[11px] text-slate-400">Bukti struk pembayaran resmi</p>
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
                    <p class="text-xs text-slate-400 italic">Tidak ada berkas bukti struk kuitansi yang dilampirkan.</p>
                @endif
            </x-card>
        </div>

        <!-- Sidebar Informasi Pemohon & Verifikasi -->
        <div class="space-y-6">
            <x-card title="Informasi Pemohon">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-11 h-11 rounded-xl bg-slate-900 text-white font-bold flex items-center justify-center text-sm shrink-0">
                        {{ substr($reimbursement->employee?->full_name ?? 'NA', 0, 2) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-slate-900 truncate">{{ $reimbursement->employee?->full_name }}</p>
                        <p class="text-xs text-slate-400 font-mono">{{ $reimbursement->employee?->employee_code }}</p>
                    </div>
                </div>
                <div class="pt-3 space-y-2 text-xs">
                    <div>
                        <span class="text-slate-400">Departemen:</span>
                        <span class="font-semibold text-slate-700 ml-1">{{ $reimbursement->employee?->department?->name ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400">Jabatan:</span>
                        <span class="font-semibold text-slate-700 ml-1">{{ $reimbursement->employee?->designation?->name ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400">Cabang:</span>
                        <span class="font-semibold text-slate-700 ml-1">{{ $reimbursement->employee?->branch?->name ?? '-' }}</span>
                    </div>
                </div>
            </x-card>

            <x-card title="Riwayat Verifikasi">
                <dl class="space-y-3 text-xs">
                    <div>
                        <dt class="text-slate-400">Diverifikasi Oleh:</dt>
                        <dd class="font-semibold text-slate-800 mt-0.5">
                            {{ $reimbursement->approvedBy?->full_name ?? ($reimbursement->status === 'REJECTED' ? 'Ditolak' : 'Belum Diverifikasi') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-400">Pencairan Dana (Disbursement):</dt>
                        <dd class="font-semibold text-slate-800 mt-0.5">
                            {{ $reimbursement->disbursed_at?->translatedFormat('d M Y, H:i') ?? ($reimbursement->status === 'APPROVED' ? 'Menunggu Transfer Finance' : '-') }}
                        </dd>
                    </div>
                </dl>
            </x-card>
        </div>

    </div>

</div>
@endsection
