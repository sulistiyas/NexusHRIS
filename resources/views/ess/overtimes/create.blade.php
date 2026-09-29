@extends('layouts.app')

@section('title', 'Formulir Pengajuan Lembur - NexusHRIS')
@section('page_title', 'Ajukan Lembur')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Formulir Pengajuan Lembur</h2>
            <p class="text-xs sm:text-sm text-slate-500">Tentukan tanggal, jam mulai, jam selesai, dan uraian tugas lembur.</p>
        </div>
        <a href="{{ route('ess.overtimes.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-600 font-semibold text-xs sm:text-sm hover:bg-slate-100 transition">
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
        <form method="POST" action="{{ route('ess.overtimes.store') }}" class="space-y-6">
            @csrf

            <!-- Tanggal Lembur -->
            <div>
                <label for="date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Tanggal Pelaksanaan <span class="text-rose-500">*</span>
                </label>
                <input type="date" name="date" id="date" value="{{ old('date', date('Y-m-d')) }}" required class="w-full px-4 py-3 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-mono focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition">
            </div>

            <!-- Jam Mulai & Jam Selesai -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="start_time" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Jam Mulai (HH:mm) <span class="text-rose-500">*</span>
                    </label>
                    <input type="time" name="start_time" id="start_time" value="{{ old('start_time', '17:00') }}" required class="w-full px-4 py-3 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-mono focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition">
                </div>
                <div>
                    <label for="end_time" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Jam Selesai (HH:mm) <span class="text-rose-500">*</span>
                    </label>
                    <input type="time" name="end_time" id="end_time" value="{{ old('end_time', '20:00') }}" required class="w-full px-4 py-3 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-mono focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition">
                </div>
            </div>

            <!-- Uraian Pekerjaan / Alasan -->
            <div>
                <label for="reason" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Uraian Pekerjaan / Alasan Lembur <span class="text-rose-500">*</span>
                </label>
                <textarea name="reason" id="reason" rows="3" placeholder="Sebutkan pekerjaan yang dikerjakan secara spesifik..." required class="w-full px-4 py-3 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition">{{ old('reason') }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('ess.overtimes.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 font-semibold text-xs sm:text-sm hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-sky-600/20 transition">
                    Kirim Pengajuan Lembur
                </button>
            </div>

        </form>
    </x-card>

</div>
@endsection
