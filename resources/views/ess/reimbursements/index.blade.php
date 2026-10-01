@extends('layouts.app')

@section('title', 'Klaim Biaya Operasional (Reimbursement) - NexusHRIS')
@section('page_title', 'Reimbursement Mandiri')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Klaim Biaya Operasional (Reimbursement)</h2>
            <p class="text-xs sm:text-sm text-slate-500">Ajukan penggantian pengeluaran dinas dengan bukti struk kuitansi transparan.</p>
        </div>
        <a href="{{ route('ess.reimbursements.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-sky-600/20 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Ajukan Klaim Baru</span>
        </a>
    </div>

    @if(session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif
    @if(session('error'))
        <x-alert type="error">{{ session('error') }}</x-alert>
    @endif

    <!-- Kartu Informasi & Panduan -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-gradient-to-tr from-sky-900 to-[#0B1E36] rounded-2xl p-5 text-white shadow-md">
            <div class="text-xs text-sky-200 font-bold uppercase tracking-wider">Total Klaim Anda</div>
            <div class="text-2xl sm:text-3xl font-extrabold font-mono mt-1 text-white">
                {{ $reimbursements->total() }} <span class="text-sm font-normal text-sky-200">Pengajuan</span>
            </div>
            <p class="text-[11px] text-sky-200/70 mt-1">Riwayat seluruh permohonan klaim reimbursement Anda.</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs">
            <div class="text-xs text-slate-500 font-bold uppercase tracking-wider">Format Bukti Lampiran</div>
            <p class="text-xs text-slate-600 mt-1">
                Lampirkan foto struk asli atau file digital (JPG, PNG, PDF maks. 5 MB) yang menampilkan tanggal dan nominal jelas.
            </p>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs">
            <div class="text-xs text-slate-500 font-bold uppercase tracking-wider">Alur Verifikasi</div>
            <p class="text-xs text-slate-600 mt-1">
                Pengajuan diperiksa oleh atasan langsung (Manager) sebelum diverifikasi dan dicairkan (Disbursed) oleh Tim Finance.
            </p>
        </div>
    </div>

    <!-- Tabel Riwayat Pengajuan Klaim -->
    <x-card class="p-0 overflow-hidden" title="Riwayat Pengajuan Reimbursement">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 font-semibold uppercase text-[11px] tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">No. Referensi</th>
                        <th class="px-5 py-3.5">Tanggal Klaim</th>
                        <th class="px-5 py-3.5">Kategori Biaya</th>
                        <th class="px-5 py-3.5">Nominal (IDR)</th>
                        <th class="px-5 py-3.5">Bukti Struk</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($reimbursements as $item)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-3.5 font-mono font-bold text-slate-900">
                                {{ $item->claim_number }}
                            </td>
                            <td class="px-5 py-3.5">
                                {{ $item->claim_date?->translatedFormat('d M Y') ?? '-' }}
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-medium text-slate-800">{{ $item->category?->name ?? 'Umum' }}</span>
                            </td>
                            <td class="px-5 py-3.5 font-mono font-bold text-slate-900">
                                Rp {{ number_format((float) $item->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5">
                                @if($item->attachments->isNotEmpty())
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-sky-50 text-sky-700 text-xs font-medium">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                        </svg>
                                        {{ $item->attachments->count() }} Berkas
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs italic">Tanpa berkas</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if($item->status === 'PENDING')
                                    <x-badge color="amber">Menunggu Persetujuan</x-badge>
                                @elseif($item->status === 'APPROVED')
                                    <x-badge color="sky">Disetujui (Menunggu Finance)</x-badge>
                                @elseif($item->status === 'DISBURSED')
                                    <x-badge color="emerald">Sudah Dicairkan</x-badge>
                                @elseif($item->status === 'REJECTED')
                                    <x-badge color="rose">Ditolak</x-badge>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('ess.reimbursements.show', $item) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                                    <span>Detail</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-slate-400">
                                Belum ada riwayat pengajuan klaim reimbursement.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reimbursements->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $reimbursements->links() }}
            </div>
        @endif
    </x-card>

</div>
@endsection
