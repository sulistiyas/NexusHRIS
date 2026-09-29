@extends('layouts.app')

@section('title', 'Tambah Shift Kerja - NexusHRIS')
@section('page_title', 'Tambah Shift')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Formulir Tambah Shift</h2>
            <p class="text-xs sm:text-sm text-slate-500">Tentukan jam masuk, jam pulang, toleransi keterlambatan, dan status shift malam.</p>
        </div>
        <a href="{{ route('shifts.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-600 font-semibold text-xs sm:text-sm hover:bg-slate-100 transition">
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
        <form method="POST" action="{{ route('shifts.store') }}" class="space-y-6">
            @csrf

            <!-- Nama Shift -->
            <div>
                <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Nama Shift <span class="text-rose-500">*</span>
                </label>
                <input 
                    type="text" 
                    name="name" 
                    id="name" 
                    value="{{ old('name') }}" 
                    placeholder="Contoh: Shift Pagi Reguler, Shift Siang, Shift Malam" 
                    required 
                    class="w-full px-4 py-3 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition"
                >
            </div>

            <!-- Jam Masuk & Jam Pulang -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="start_time" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Jam Masuk (Format HH:mm) <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="time" 
                        name="start_time" 
                        id="start_time" 
                        value="{{ old('start_time', '08:00') }}" 
                        required 
                        class="w-full px-4 py-3 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-mono focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition"
                    >
                </div>
                <div>
                    <label for="end_time" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Jam Pulang (Format HH:mm) <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="time" 
                        name="end_time" 
                        id="end_time" 
                        value="{{ old('end_time', '17:00') }}" 
                        required 
                        class="w-full px-4 py-3 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-mono focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition"
                    >
                </div>
            </div>

            <!-- Toleransi Keterlambatan -->
            <div>
                <label for="grace_period_minutes" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Toleransi Keterlambatan (Menit) <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input 
                        type="number" 
                        name="grace_period_minutes" 
                        id="grace_period_minutes" 
                        value="{{ old('grace_period_minutes', 15) }}" 
                        min="0" 
                        max="120" 
                        required 
                        class="w-full px-4 py-3 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition"
                    >
                    <span class="absolute right-4 top-3 sm:top-2.5 text-xs text-slate-400 font-medium">Menit</span>
                </div>
                <p class="mt-1.5 text-xs text-slate-400">Pegawai yang absen masuk setelah jam masuk + toleransi akan tercatat berstatus <span class="font-semibold text-rose-500">LATE</span>.</p>
            </div>

            <!-- Checkbox Shift Malam -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input 
                        type="checkbox" 
                        name="is_night_shift" 
                        id="is_night_shift" 
                        value="1" 
                        {{ old('is_night_shift') ? 'checked' : '' }}
                        class="mt-1 w-4 h-4 rounded text-sky-600 focus:ring-sky-500 border-slate-300"
                    >
                    <div>
                        <span class="text-xs sm:text-sm font-bold text-slate-800">Shift Lintas Malam (Overnight Shift)</span>
                        <p class="text-xs text-slate-500">Centang opsi ini jika jam kerja berakhir di keesokan harinya (misal: 22:00 s.d 06:00).</p>
                    </div>
                </label>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('shifts.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 font-semibold text-xs sm:text-sm hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-sky-600/20 transition">
                    Simpan Shift
                </button>
            </div>

        </form>
    </x-card>

</div>
@endsection
