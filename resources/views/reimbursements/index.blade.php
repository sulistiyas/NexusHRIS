@extends('layouts.app')

@section('title', 'Persetujuan & Verifikasi Klaim Biaya - NexusHRIS')
@section('page_title', 'Persetujuan Reimbursement')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Verifikasi & Pencairan Reimbursement</h2>
            <p class="text-xs sm:text-sm text-slate-500">Persetujuan berjenjang klaim pengeluaran operasional karyawan dan pencairan dana Finance.</p>
        </div>
    </div>

    @if(session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif
    @if(session('error'))
        <x-alert type="error">{{ session('error') }}</x-alert>
    @endif

    <!-- Kartu Statistik Klaim -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 sm:gap-4">
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Klaim</span>
            <p class="text-2xl font-extrabold font-mono text-slate-900 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-amber-50/70 border border-amber-200 rounded-2xl p-4 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700">Menunggu</span>
            <p class="text-2xl font-extrabold font-mono text-amber-900 mt-1">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-sky-50/70 border border-sky-200 rounded-2xl p-4 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-sky-700">Disetujui</span>
            <p class="text-2xl font-extrabold font-mono text-sky-900 mt-1">{{ $stats['approved'] }}</p>
        </div>
        <div class="bg-emerald-50/70 border border-emerald-200 rounded-2xl p-4 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Dicairkan</span>
            <p class="text-2xl font-extrabold font-mono text-emerald-900 mt-1">{{ $stats['disbursed'] }}</p>
        </div>
        <div class="bg-rose-50/70 border border-rose-200 rounded-2xl p-4 shadow-xs col-span-2 sm:col-span-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-rose-700">Ditolak</span>
            <p class="text-2xl font-extrabold font-mono text-rose-900 mt-1">{{ $stats['rejected'] }}</p>
        </div>
    </div>

    <!-- Filter & Pencarian -->
    <x-card class="p-4">
        <form method="GET" action="{{ route('reimbursement-approvals.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search }}" 
                    placeholder="Cari nomor klaim, NIP, atau nama karyawan..." 
                    class="w-full pl-4 pr-10 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition"
                >
            </div>

            <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0">
                <select name="status" class="px-3 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-medium text-slate-700 focus:border-sky-500 bg-white">
                    <option value="ALL" {{ $status === 'ALL' ? 'selected' : '' }}>Semua Status</option>
                    <option value="PENDING" {{ $status === 'PENDING' ? 'selected' : '' }}>Menunggu Persetujuan</option>
                    <option value="APPROVED" {{ $status === 'APPROVED' ? 'selected' : '' }}>Disetujui (Siap Dicairkan)</option>
                    <option value="DISBURSED" {{ $status === 'DISBURSED' ? 'selected' : '' }}>Sudah Dicairkan</option>
                    <option value="REJECTED" {{ $status === 'REJECTED' ? 'selected' : '' }}>Ditolak</option>
                </select>

                <button type="submit" class="px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs sm:text-sm shadow-sm transition whitespace-nowrap">
                    Terapkan Filter
                </button>

                @if($search || $status !== 'ALL')
                    <a href="{{ route('reimbursement-approvals.index') }}" class="px-3 py-2.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-xs sm:text-sm font-medium transition whitespace-nowrap">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </x-card>

    <!-- Tabel Pengajuan Klaim -->
    <x-card class="p-0 overflow-hidden" title="Daftar Pengajuan Klaim Biaya">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 font-semibold uppercase text-[11px] tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Karyawan</th>
                        <th class="px-5 py-3.5">No. Referensi</th>
                        <th class="px-5 py-3.5">Tgl Transaksi</th>
                        <th class="px-5 py-3.5">Kategori</th>
                        <th class="px-5 py-3.5">Nominal (IDR)</th>
                        <th class="px-5 py-3.5">Bukti</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi Verifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($reimbursements as $item)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-900">{{ $item->employee?->full_name }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $item->employee?->employee_code }} • {{ $item->employee?->department?->name }}</div>
                            </td>
                            <td class="px-5 py-3.5 font-mono font-bold text-slate-800">
                                {{ $item->claim_number }}
                            </td>
                            <td class="px-5 py-3.5">
                                {{ $item->claim_date?->translatedFormat('d M Y') }}
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-medium text-slate-800">{{ $item->category?->name ?? 'Umum' }}</span>
                            </td>
                            <td class="px-5 py-3.5 font-mono font-bold text-slate-900">
                                Rp {{ number_format((float) $item->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5">
                                @if($item->attachments->isNotEmpty())
                                    <a 
                                        href="{{ route('reimbursements.attachments.download', $item->attachments->first()) }}" 
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-700 text-xs font-medium transition"
                                        title="Unduh berkas {{ $item->attachments->first()->file_name }}"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                        </svg>
                                        <span>Unduh ({{ $item->attachments->count() }})</span>
                                    </a>
                                @else
                                    <span class="text-slate-400 text-xs italic">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if($item->status === 'PENDING')
                                    <x-badge color="amber">Menunggu</x-badge>
                                @elseif($item->status === 'APPROVED')
                                    <x-badge color="sky">Disetujui</x-badge>
                                @elseif($item->status === 'DISBURSED')
                                    <x-badge color="emerald">Dicairkan</x-badge>
                                @elseif($item->status === 'REJECTED')
                                    <x-badge color="rose">Ditolak</x-badge>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    <a href="{{ route('reimbursement-approvals.show', $item) }}" class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition" title="Lihat Rincian">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>

                                    @if($item->status === 'PENDING')
                                        <form method="POST" action="{{ route('reimbursement-approvals.approve', $item) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-xs transition" title="Setujui Klaim">
                                                Setujui
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('reimbursement-approvals.reject', $item) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs shadow-xs transition" title="Tolak Klaim">
                                                Tolak
                                            </button>
                                        </form>
                                    @elseif($item->status === 'APPROVED')
                                        @role('super_admin|hr_admin')
                                            <form method="POST" action="{{ route('reimbursement-approvals.disburse', $item) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-xs transition" title="Cairkan Dana">
                                                    Cairkan Dana
                                                </button>
                                            </form>
                                        @endrole
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-8 text-center text-slate-400">
                                Tidak ada data klaim reimbursement yang cocok dengan filter.
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
