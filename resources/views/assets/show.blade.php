@extends('layouts.app')

@section('title', 'Aset ' . $asset->asset_tag . ' - ' . $asset->name . ' - NexusHRIS')
@section('page_title', 'Rincian Aset')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header Navigation Back & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('assets.index') }}" class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">{{ $asset->name }}</h2>
                    <span class="px-2.5 py-0.5 rounded-lg bg-sky-100 text-sky-800 font-mono text-xs font-bold">{{ $asset->asset_tag }}</span>
                </div>
                <p class="text-xs text-slate-500">Kategori: {{ $asset->category?->name ?? 'Umum' }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('assets.edit', $asset) }}" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold text-xs sm:text-sm transition">
                Ubah Spesifikasi
            </a>
        </div>
    </div>

    @if(session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif
    @if(session('error'))
        <x-alert type="error">{{ session('error') }}</x-alert>
    @endif

    @php
        $activeAssignment = $asset->assignments->firstWhere('returned_date', null);
    @endphp

    <!-- Grid Detail Aset & Status Peminjaman -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Kartu Spesifikasi Aset -->
        <div class="md:col-span-2 space-y-6">
            <x-card title="Spesifikasi & Informasi Aset">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm">
                    <div>
                        <dt class="text-slate-400 font-medium">Tag / Barcode Aset</dt>
                        <dd class="text-slate-900 font-mono font-bold mt-0.5">{{ $asset->asset_tag }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400 font-medium">Nomor Seri (Serial Number)</dt>
                        <dd class="text-slate-900 font-mono font-semibold mt-0.5">{{ $asset->serial_number ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400 font-medium">Kategori</dt>
                        <dd class="text-slate-800 font-semibold mt-0.5">{{ $asset->category?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400 font-medium">Kondisi Fisik Saat Ini</dt>
                        <dd class="mt-0.5">
                            @if($asset->condition === 'EXCELLENT')
                                <span class="text-emerald-700 font-bold">Sangat Baik (EXCELLENT)</span>
                            @elseif($asset->condition === 'GOOD')
                                <span class="text-sky-700 font-bold">Baik / Layak Pakai (GOOD)</span>
                            @elseif($asset->condition === 'DAMAGED')
                                <span class="text-amber-700 font-bold">Rusak (DAMAGED)</span>
                            @else
                                <span class="text-rose-700 font-bold">Hilang (LOST)</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-400 font-medium">Tanggal Pengadaan</dt>
                        <dd class="text-slate-800 font-medium mt-0.5">{{ $asset->purchase_date?->translatedFormat('d F Y') ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400 font-medium">Nilai Pembelian (IDR)</dt>
                        <dd class="text-slate-900 font-mono font-bold mt-0.5">
                            {{ $asset->purchase_cost ? 'Rp ' . number_format((float) $asset->purchase_cost, 0, ',', '.') : '-' }}
                        </dd>
                    </div>
                </dl>
            </x-card>

            <!-- Formulir Tindakan Serah Terima / Pengembalian -->
            @if($asset->status === 'AVAILABLE')
                <x-card title="Serah Terima Aset ke Karyawan (Peminjaman)">
                    <p class="text-xs text-slate-500 mb-4">Aset saat ini berstatus <strong>Tersedia</strong> di gudang. Masukkan data penyerahan untuk meminjamkan aset ini ke karyawan.</p>
                    <form method="POST" action="{{ route('assets.assign', $asset) }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Pilih Karyawan Penerima <span class="text-rose-500">*</span>
                                </label>
                                <select name="employee_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 bg-white">
                                    <option value="">-- Pilih Karyawan Aktif --</option>
                                    @foreach($activeEmployees as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->employee_code }} - {{ $emp->full_name }} ({{ $emp->department?->name }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Tanggal Penyerahan <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" name="assigned_date" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Catatan Serah Terima / Kelengkapan Aksesoris
                            </label>
                            <textarea name="notes" rows="2" placeholder="Contoh: Unit laptop mulus, include charger 67W ori, tas laptop, dan mouse wireless..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500"></textarea>
                        </div>

                        <div class="text-right">
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-sky-600/20 transition">
                                Simpan Serah Terima Aset
                            </button>
                        </div>
                    </form>
                </x-card>
            @elseif($asset->status === 'ASSIGNED' && $activeAssignment)
                <x-card title="Pencatatan Pengembalian Aset (Return)">
                    <p class="text-xs text-slate-500 mb-4">Aset sedang dipegang oleh <strong>{{ $activeAssignment->employee?->full_name }}</strong> sejak {{ $activeAssignment->assigned_date?->format('d/m/Y') }}.</p>
                    <form method="POST" action="{{ route('asset-assignments.return', $activeAssignment) }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Tanggal Pengembalian Fisik <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" name="returned_date" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Kondisi Fisik Saat Dikembalikan <span class="text-rose-500">*</span>
                                </label>
                                <select name="return_condition" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 bg-white">
                                    <option value="EXCELLENT">Sangat Baik (EXCELLENT)</option>
                                    <option value="GOOD" selected>Baik / Lengkap (GOOD)</option>
                                    <option value="DAMAGED">Rusak / Perlu Servis (DAMAGED)</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Catatan Pengembalian & Kondisi Akhir
                            </label>
                            <textarea name="notes" rows="2" placeholder="Catatan kondisi perangkat saat diterima kembali oleh bagian inventaris/IT..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500"></textarea>
                        </div>

                        <div class="text-right">
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-emerald-600/20 transition">
                                Catat Pengembalian & Perbarui Status
                            </button>
                        </div>
                    </form>
                </x-card>
            @endif
        </div>

        <!-- Sidebar Status Aset Saat Ini -->
        <div class="space-y-6">
            <x-card title="Status Inventaris">
                <div class="space-y-4">
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-500">Status Operasional:</span>
                        @if($asset->status === 'AVAILABLE')
                            <x-badge color="emerald">Tersedia</x-badge>
                        @elseif($asset->status === 'ASSIGNED')
                            <x-badge color="sky">Dipinjamkan</x-badge>
                        @elseif($asset->status === 'UNDER_MAINTENANCE')
                            <x-badge color="amber">Dalam Servis</x-badge>
                        @elseif($asset->status === 'DISPOSED')
                            <x-badge color="rose">Dihapus</x-badge>
                        @endif
                    </div>

                    @if($activeAssignment && $activeAssignment->employee)
                        <div class="border-t border-slate-100 pt-3">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Pemegang Saat Ini</span>
                            <div class="flex items-center gap-3 mt-2">
                                <div class="w-10 h-10 rounded-xl bg-sky-900 text-white font-bold flex items-center justify-center text-xs">
                                    {{ substr($activeAssignment->employee->full_name, 0, 2) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-900 truncate">{{ $activeAssignment->employee->full_name }}</p>
                                    <p class="text-[11px] text-slate-400 font-mono">{{ $activeAssignment->employee->employee_code }}</p>
                                </div>
                            </div>
                            <div class="mt-2 text-xs text-slate-600 space-y-1">
                                <p><strong>Diserahkan:</strong> {{ $activeAssignment->assigned_date?->translatedFormat('d M Y') }}</p>
                                @if($activeAssignment->notes)
                                    <p class="text-slate-500 italic bg-white p-2 rounded-lg border border-slate-100 mt-1">"{{ $activeAssignment->notes }}"</p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </x-card>
        </div>

    </div>

    <!-- Tabel Riwayat Serah Terima Aset (Log Audit) -->
    <x-card class="p-0 overflow-hidden" title="Riwayat Serah Terima & Pengembalian Aset">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 font-semibold uppercase text-[11px] tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Karyawan Pemegang</th>
                        <th class="px-5 py-3.5">Tanggal Penyerahan</th>
                        <th class="px-5 py-3.5">Tanggal Pengembalian</th>
                        <th class="px-5 py-3.5">Kondisi Pengembalian</th>
                        <th class="px-5 py-3.5">Catatan Log</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($asset->assignments->sortByDesc('id') as $assignment)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-900">{{ $assignment->employee?->full_name ?? '-' }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $assignment->employee?->employee_code }} • {{ $assignment->employee?->department?->name }}</div>
                            </td>
                            <td class="px-5 py-3.5 font-medium text-slate-800">
                                {{ $assignment->assigned_date?->translatedFormat('d M Y') ?? '-' }}
                            </td>
                            <td class="px-5 py-3.5">
                                @if($assignment->returned_date)
                                    <span class="text-emerald-700 font-semibold">{{ $assignment->returned_date->translatedFormat('d M Y') }}</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-sky-100 text-sky-700 text-xs font-bold">Sedang Digunakan</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if($assignment->return_condition)
                                    <span class="font-medium text-slate-800">{{ $assignment->return_condition }}</span>
                                @else
                                    <span class="text-slate-400 italic">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-slate-600">
                                {{ $assignment->notes ?: '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-400">
                                Belum ada riwayat peminjaman untuk aset ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

</div>
@endsection
