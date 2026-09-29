@extends('layouts.app')

@section('title', 'Ubah Departemen - NexusHRIS')
@section('page_title', 'Ubah Departemen')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Perbarui Departemen: {{ $department->name }}</h2>
            <p class="text-xs sm:text-sm text-slate-500">Sesuaikan cabang atau atasan penanggung jawab departemen.</p>
        </div>
        <a href="{{ route('departments.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-600 font-semibold text-xs sm:text-sm hover:bg-slate-100 transition">
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
        <form method="POST" action="{{ route('departments.update', $department) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="branch_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Kantor Cabang <span class="text-rose-500">*</span>
                </label>
                <select 
                    name="branch_id" 
                    id="branch_id" 
                    required 
                    class="w-full px-4 py-3 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition bg-white"
                >
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected(old('branch_id', $department->branch_id) == $branch->id)>
                            {{ $branch->name }} ({{ $branch->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="code" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Kode Departemen <span class="text-rose-500">*</span>
                </label>
                <input 
                    type="text" 
                    name="code" 
                    id="code" 
                    value="{{ old('code', $department->code) }}" 
                    required 
                    class="w-full px-4 py-3 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-mono focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition"
                >
            </div>

            <div>
                <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Nama Departemen <span class="text-rose-500">*</span>
                </label>
                <input 
                    type="text" 
                    name="name" 
                    id="name" 
                    value="{{ old('name', $department->name) }}" 
                    required 
                    class="w-full px-4 py-3 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition"
                >
            </div>

            <div>
                <label for="manager_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Kepala / Manajer Departemen (Opsional)
                </label>
                <select 
                    name="manager_id" 
                    id="manager_id" 
                    class="w-full px-4 py-3 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition bg-white"
                >
                    <option value="">Belum Ditentukan</option>
                    @foreach($potentialManagers as $emp)
                        <option value="{{ $emp->id }}" @selected(old('manager_id', $department->manager_id) == $emp->id)>
                            {{ $emp->user->name }} ({{ $emp->employee_code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <a href="{{ route('departments.index') }}" class="px-5 py-3 sm:py-2.5 rounded-xl border border-slate-300 text-slate-600 text-xs sm:text-sm font-semibold hover:bg-slate-100 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-3 sm:py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-sky-600/20 transition">
                    Simpan Perubahan
                </button>
            </div>

        </form>
    </x-card>

</div>
@endsection
