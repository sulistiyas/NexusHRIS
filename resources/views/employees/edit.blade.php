@extends('layouts.app')

@section('title', 'Ubah Data Karyawan - NexusHRIS')
@section('page_title', 'Ubah Karyawan')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Perbarui Karyawan: {{ $employee->user->name }}</h2>
            <p class="text-xs sm:text-sm text-slate-500">Sesuaikan informasi personal, cabang, maupun struktur kepegawaian.</p>
        </div>
        <a href="{{ route('employees.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-600 font-semibold text-xs sm:text-sm hover:bg-slate-100 transition">
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

    <form method="POST" action="{{ route('employees.update', $employee) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <x-card class="space-y-5">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-200 pb-2.5 flex items-center gap-2">
                <span>🔐</span> 1. Akun Pengguna
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Nama Lengkap *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $employee->user->name) }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition">
                </div>
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Email Login *</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $employee->user->email) }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition">
                </div>
                <div>
                    <label for="role" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Peran (Role)</label>
                    <select name="role" id="role" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10">
                        @php $currentRole = $employee->user->roles->first()?->name ?? 'employee'; @endphp
                        <option value="employee" @selected(old('role', $currentRole) === 'employee')>Employee</option>
                        <option value="manager" @selected(old('role', $currentRole) === 'manager')>Manager</option>
                        <option value="hr_admin" @selected(old('role', $currentRole) === 'hr_admin')>HR Admin</option>
                        <option value="super_admin" @selected(old('role', $currentRole) === 'super_admin')>Super Admin</option>
                    </select>
                </div>
            </div>
        </x-card>

        <x-card class="space-y-5">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-200 pb-2.5 flex items-center gap-2">
                <span>🏢</span> 2. Struktur Organisasi & Penempatan
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="employee_code" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">NIP *</label>
                    <input type="text" name="employee_code" id="employee_code" value="{{ old('employee_code', $employee->employee_code) }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-mono focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition">
                </div>
                <div>
                    <label for="employee-branch-select" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Cabang *</label>
                    <select name="branch_id" id="employee-branch-select" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10">
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" @selected(old('branch_id', $employee->branch_id) == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="employee-department-select" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Departemen *</label>
                    <select name="department_id" id="employee-department-select" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10">
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}" @selected(old('department_id', $employee->department_id) == $d->id)>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="employee-designation-select" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Jabatan *</label>
                    <select name="designation_id" id="employee-designation-select" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10">
                        @foreach($designations as $des)
                            <option value="{{ $des->id }}" @selected(old('designation_id', $employee->designation_id) == $des->id)>{{ $des->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="manager_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Atasan Langsung</label>
                    <select name="manager_id" id="manager_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10">
                        <option value="">Tidak Ada Atasan</option>
                        @foreach($managers as $m)
                            <option value="{{ $m->id }}" @selected(old('manager_id', $employee->manager_id) == $m->id)>
                                {{ $m->user->name }} ({{ $m->employee_code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="employment_status" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Status Ketenagakerjaan *</label>
                    <select name="employment_status" id="employment_status" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10">
                        <option value="PKWTT" @selected(old('employment_status', $employee->employment_status) === 'PKWTT')>Tetap (PKWTT)</option>
                        <option value="PKWT" @selected(old('employment_status', $employee->employment_status) === 'PKWT')>Kontrak (PKWT)</option>
                        <option value="PROBATION" @selected(old('employment_status', $employee->employment_status) === 'PROBATION')>Probation</option>
                        <option value="INTERN" @selected(old('employment_status', $employee->employment_status) === 'INTERN')>Internship</option>
                    </select>
                </div>
                <div>
                    <label for="join_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Tanggal Masuk *</label>
                    <input type="date" name="join_date" id="join_date" value="{{ old('join_date', $employee->join_date->format('Y-m-d')) }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition">
                </div>
                <div>
                    <label for="contract_end_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Akhir Kontrak</label>
                    <input type="date" name="contract_end_date" id="contract_end_date" value="{{ old('contract_end_date', $employee->contract_end_date?->format('Y-m-d')) }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition">
                </div>
            </div>
        </x-card>

        <x-card class="space-y-5">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-200 pb-2.5 flex items-center gap-2">
                <span>📋</span> 3. Identitas & Biodata
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="nik_ktp" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">NIK KTP *</label>
                    <input type="text" name="nik_ktp" id="nik_ktp" maxlength="16" value="{{ old('nik_ktp', $employee->nik_ktp) }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-mono focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition">
                </div>
                <div>
                    <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Nomor Handphone</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $employee->phone) }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition">
                </div>
                <div>
                    <label for="bank_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Bank</label>
                    <input type="text" name="bank_name" id="bank_name" value="{{ old('bank_name', $employee->bank_name) }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition">
                </div>
                <div>
                    <label for="bank_account_no" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">No. Rekening</label>
                    <input type="text" name="bank_account_no" id="bank_account_no" value="{{ old('bank_account_no', $employee->bank_account_no) }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-mono focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition">
                </div>
            </div>
        </x-card>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('employees.index') }}" class="px-5 py-3 sm:py-2.5 rounded-xl border border-slate-300 text-slate-600 text-xs sm:text-sm font-semibold hover:bg-slate-100 transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-3 sm:py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-sky-600/20 transition">
                Simpan Perubahan
            </button>
        </div>

    </form>

</div>
@endsection
