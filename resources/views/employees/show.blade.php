@extends('layouts.app')

@section('title', 'Profil Karyawan: ' . $employee->user->name . ' - NexusHRIS')
@section('page_title', 'Profil Karyawan')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header Profil Card -->
    <div class="bg-gradient-to-r from-[#081627] to-[#0f2c4d] p-6 sm:p-8 rounded-2xl text-white shadow-xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6 border border-sky-950">
        <div class="flex items-center gap-5">
            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-sky-600 text-white font-black text-2xl sm:text-3xl flex items-center justify-center shadow-lg shadow-sky-950/50 ring-4 ring-white/10">
                {{ substr($employee->user->name, 0, 2) }}
            </div>
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-xl sm:text-2xl font-black tracking-tight">{{ $employee->user->name }}</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-mono font-bold bg-sky-500/20 text-sky-300 border border-sky-400/30">
                        {{ $employee->employee_code }}
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-slate-300 mt-1">{{ $employee->designation->title }} &bull; {{ $employee->department->name }}</p>
                <div class="flex items-center gap-3 text-xs text-slate-400 mt-2">
                    <span>🏢 {{ $employee->branch->name }}</span>
                    <span>✉️ {{ $employee->user->email }}</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('employees.edit', $employee) }}" class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-semibold text-xs sm:text-sm shadow-md transition">
                ✏️ Ubah Profil
            </a>
            <a href="{{ route('employees.index') }}" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold text-xs sm:text-sm transition">
                Kembali
            </a>
        </div>
    </div>

    <!-- Grid Detail Info -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Kolom Kiri: Ringkasan Cepat -->
        <div class="space-y-6">
            <x-card class="space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100 pb-2">Status Pekerjaan</h3>
                <div>
                    <span class="text-xs text-slate-400 block">Status Ketenagakerjaan</span>
                    <span class="inline-flex mt-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-100 text-sky-800">
                        {{ $employee->employment_status }}
                    </span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block">Tanggal Bergabung</span>
                    <span class="text-sm font-semibold text-slate-900 block mt-0.5">{{ $employee->join_date->format('d M Y') }}</span>
                </div>
                @if($employee->contract_end_date)
                    <div>
                        <span class="text-xs text-slate-400 block">Akhir Kontrak</span>
                        <span class="text-sm font-semibold text-rose-600 block mt-0.5">{{ $employee->contract_end_date->format('d M Y') }}</span>
                    </div>
                @endif
                <div>
                    <span class="text-xs text-slate-400 block">Atasan Langsung (Manager)</span>
                    <span class="text-sm font-semibold text-slate-900 block mt-0.5">
                        {{ $employee->manager?->user?->name ?? 'Tidak Ada (Root/Executive)' }}
                    </span>
                </div>
            </x-card>
        </div>

        <!-- Kolom Kanan: Rincian Lengkap -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Biodata & Identitas -->
            <x-card class="space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100 pb-2">Identitas & Legalitas</h3>
                <div class="grid grid-cols-2 gap-4 text-xs sm:text-sm">
                    <div>
                        <span class="text-slate-400 block text-xs">NIK KTP</span>
                        <span class="font-mono font-bold text-slate-800">{{ $employee->nik_ktp }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-xs">NPWP</span>
                        <span class="font-mono text-slate-800">{{ $employee->npwp ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-xs">Jenis Kelamin</span>
                        <span class="font-semibold text-slate-800">{{ $employee->gender === 'MALE' ? 'Laki-Laki' : ($employee->gender === 'FEMALE' ? 'Perempuan' : '-') }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-xs">Tempat, Tanggal Lahir</span>
                        <span class="font-semibold text-slate-800">
                            {{ $employee->birth_place ?? '-' }}, {{ $employee->birth_date?->format('d M Y') ?? '-' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-xs">Nomor HP / WhatsApp</span>
                        <span class="font-semibold text-slate-800">{{ $employee->phone ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-xs">BPJS Kesehatan / TK</span>
                        <span class="text-slate-800">{{ $employee->bpjs_kes ?? '-' }} / {{ $employee->bpjs_tk ?? '-' }}</span>
                    </div>
                </div>
            </x-card>

            <!-- Rekening Penggajian -->
            <x-card class="space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100 pb-2">Rekening Penggajian (Payroll)</h3>
                <div class="grid grid-cols-3 gap-4 text-xs sm:text-sm">
                    <div>
                        <span class="text-slate-400 block text-xs">Bank</span>
                        <span class="font-bold text-slate-800">{{ $employee->bank_name ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-xs">Nomor Rekening</span>
                        <span class="font-mono font-bold text-sky-700">{{ $employee->bank_account_no ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-xs">Atas Nama</span>
                        <span class="font-semibold text-slate-800">{{ $employee->bank_account_holder ?? '-' }}</span>
                    </div>
                </div>
            </x-card>
            <!-- Berkas Digital Karyawan -->
            <x-card class="space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Berkas Dokumen Digital</h3>
                    <span class="text-xs text-slate-400">Total: {{ $employee->documents->count() }} Dokumen</span>
                </div>

                <!-- Form Upload Berkas -->
                @can('delete', App\Models\EmployeeDocument::class)
                <form method="POST" action="{{ route('employees.documents.store', $employee) }}" enctype="multipart/form-data" class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-3">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        <select name="document_type" required class="px-3 py-2 rounded-lg border border-slate-300 text-xs bg-white">
                            <option value="">Pilih Jenis Dokumen</option>
                            <option value="KTP">KTP</option>
                            <option value="NPWP">NPWP</option>
                            <option value="IJAZAH">Ijazah</option>
                            <option value="KONTRAK">Kontrak Kerja</option>
                            <option value="SERTIFIKAT">Sertifikat</option>
                            <option value="LAINNYA">Lainnya</option>
                        </select>
                        <input type="file" name="file" required class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs bg-white file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700">
                        <button type="submit" class="px-3 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs transition">
                            📤 Unggah Dokumen
                        </button>
                    </div>
                </form>
                @endcan

                <!-- Daftar Dokumen yang Terunggah -->
                <div class="divide-y divide-slate-100">
                    @forelse($employee->documents as $doc)
                        <div class="py-2.5 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-700 font-mono">
                                    {{ $doc->document_type }}
                                </span>
                                <span class="text-xs text-slate-700 font-medium truncate max-w-xs">{{ $doc->file_name }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('documents.download', $doc) }}" class="text-xs font-semibold text-sky-600 hover:text-sky-700">
                                    ⬇️ Unduh
                                </a>
                                @can('delete', $doc)
                                    <form method="POST" action="{{ route('documents.destroy', $doc) }}" class="inline" onsubmit="return confirm('Hapus dokumen ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-rose-500 hover:text-rose-700">
                                            Hapus
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-2">Belum ada berkas digital yang diunggah.</p>
                    @endforelse
                </div>
            </x-card>
        </div>

    </div>

</div>
@endsection
