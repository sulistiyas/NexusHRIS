@extends('layouts.app')

@section('title', 'Formulir Pengajuan Cuti - NexusHRIS')
@section('page_title', 'Ajukan Cuti')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Formulir Pengajuan Cuti</h2>
            <p class="text-xs sm:text-sm text-slate-500">Pilih jenis cuti, rentang tanggal kerja, dan sertakan dokumen pendukung bila ada.</p>
        </div>
        <a href="{{ route('ess.leaves.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-600 font-semibold text-xs sm:text-sm hover:bg-slate-100 transition">
            Kembali
        </a>
    </div>

    @if($errors->any())
        <x-alert type="error">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <x-card>
        <form method="POST" action="{{ route('ess.leaves.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Jenis Cuti -->
            <div>
                <label for="leave_type_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Jenis Cuti <span class="text-rose-500">*</span>
                </label>
                <select name="leave_type_id" id="leave_type_id" required class="w-full px-4 py-3 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition">
                    <option value="">-- Pilih Jenis Cuti --</option>
                    @foreach($leaveTypes as $type)
                        <option value="{{ $type->id }}" {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }} ({{ $type->is_deduct_annual ? 'Memotong Saldo Tahunan' : 'Izin Khusus' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Rentang Tanggal -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="start_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Tanggal Mulai <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required class="w-full px-4 py-3 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-mono focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition">
                </div>
                <div>
                    <label for="end_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Tanggal Selesai <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date', date('Y-m-d')) }}" required class="w-full px-4 py-3 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-mono focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition">
                </div>
            </div>

            <!-- Alasan Cuti -->
            <div>
                <label for="reason" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Alasan / Keterangan Permohonan <span class="text-rose-500">*</span>
                </label>
                <textarea name="reason" id="reason" rows="3" placeholder="Tuliskan alasan pengajuan cuti secara jelas..." required class="w-full px-4 py-3 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition">{{ old('reason') }}</textarea>
            </div>

            <!-- Lampiran Berkas / Surat Dokter -->
            <div>
                <label for="attachment" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Berkas Lampiran / Surat Dokter (PDF, JPG, PNG - Maks 2MB)
                </label>
                <input type="file" name="attachment" id="attachment" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 transition">
                <p class="text-xs text-slate-400 mt-1">Wajib dilampirkan jika mengajukan Izin Sakit atau Cuti Melahirkan.</p>
            </div>

            <!-- Tombol Simpan -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('ess.leaves.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 font-semibold text-xs sm:text-sm hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-sky-600/20 transition">
                    Kirim Permohonan Cuti
                </button>
            </div>

        </form>
    </x-card>

</div>
@endsection
