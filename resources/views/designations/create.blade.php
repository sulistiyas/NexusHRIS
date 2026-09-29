@extends('layouts.app')

@section('title', 'Tambah Jabatan - NexusHRIS')
@section('page_title', 'Tambah Jabatan')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Formulir Tambah Jabatan</h2>
            <p class="text-xs sm:text-sm text-slate-500">Tentukan departemen naungan dan tingkatan level jabatan.</p>
        </div>
        <a href="{{ route('designations.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-600 font-semibold text-xs sm:text-sm hover:bg-slate-100 transition">
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
        <form method="POST" action="{{ route('designations.store') }}" class="space-y-5">
            @csrf

            <!-- Pilihan Departemen -->
            <div>
                <label for="department_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Departemen <span class="text-rose-500">*</span>
                </label>
                <select 
                    name="department_id" 
                    id="department_id" 
                    required 
                    class="w-full px-4 py-3 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition bg-white"
                >
                    <option value="">Pilih Departemen</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>
                            {{ $dept->name }} (Cabang: {{ $dept->branch->name }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Nama Jabatan -->
            <div>
                <label for="title" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Nama Jabatan <span class="text-rose-500">*</span>
                </label>
                <input 
                    type="text" 
                    name="title" 
                    id="title" 
                    value="{{ old('title') }}" 
                    placeholder="Contoh: Senior Backend Engineer atau HR Officer" 
                    required 
                    class="w-full px-4 py-3 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition"
                >
            </div>

            <!-- Grade / Tingkatan Level -->
            <div>
                <label for="grade_level" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Tingkatan Level / Golongan
                </label>
                <input 
                    type="text" 
                    name="grade_level" 
                    id="grade_level" 
                    value="{{ old('grade_level') }}" 
                    placeholder="Contoh: Level 1 - Staff, Level 3 - Supervisor, Level 4 - Manager" 
                    class="w-full px-4 py-3 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition"
                >
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <a href="{{ route('designations.index') }}" class="px-5 py-3 sm:py-2.5 rounded-xl border border-slate-300 text-slate-600 text-xs sm:text-sm font-semibold hover:bg-slate-100 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-3 sm:py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-sky-600/20 transition">
                    Simpan Jabatan
                </button>
            </div>

        </form>
    </x-card>

</div>
@endsection
