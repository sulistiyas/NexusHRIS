@extends('layouts.app')

@section('title', 'Tambah Karyawan Baru - NexusHRIS')
@section('page_title', 'Tambah Karyawan')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Formulir Onboarding Karyawan Baru</h2>
            <p class="text-xs sm:text-sm text-slate-500">Sistem otomatis men-generate NIP dan akun pengguna terintegrasi.</p>
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

    <form method="POST" action="{{ route('employees.store') }}" class="space-y-6">
        @csrf

        <!-- 1. Akun Pengguna & Hak Akses -->
        <x-card class="space-y-5">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-200 pb-2.5 flex items-center gap-2">
                <span>🔐</span> 1. Akun Pengguna (User Account)
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nama Lengkap <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition">
                </div>
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Alamat Email (Login) <span class="text-rose-500">*</span>
                    </label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition">
                </div>
                <div>
                    <label for="role" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Peran Akses (Role) <span class="text-rose-500">*</span>
                    </label>
                    <select name="role" id="role" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10">
                        <option value="employee" @selected(old('role', 'employee') === 'employee')>Employee (Staff Reguler)</option>
                        <option value="manager" @selected(old('role') === 'manager')>Manager (Approval Tim)</option>
                        <option value="hr_admin" @selected(old('role') === 'hr_admin')>HR Admin (Pengelola HR)</option>
                        <option value="super_admin" @selected(old('role') === 'super_admin')>Super Admin (Full Akses)</option>
                    </select>
                </div>
                <div>
                    <label for="temporary_password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Password Sementara (Opsional)
                    </label>
                    <input type="text" name="temporary_password" id="temporary_password" value="{{ old('temporary_password', 'password') }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-mono focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition">
                    <span class="text-[11px] text-slate-400 mt-1 block">Default: password (dapat diganti karyawan saat pertama login).</span>
                </div>
            </div>
        </x-card>

        <!-- 2. Data Organisasi & Penempatan -->
        <x-card class="space-y-5">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-200 pb-2.5 flex items-center gap-2">
                <span>🏢</span> 2. Struktur Organisasi & Penempatan
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="employee_code" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        NIP (Nomor Induk Pegawai)
                    </label>
                    <input type="text" name="employee_code" id="employee_code" value="{{ old('employee_code', $suggestedCode) }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-mono focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition">
                    <span class="text-[11px] text-slate-400 mt-1 block">Otomatis dihitung dari urutan NIP bulan berjalan.</span>
                </div>
                <div>
                    <label for="employee-branch-select" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Kantor Cabang <span class="text-rose-500">*</span>
                    </label>
                    <select name="branch_id" id="employee-branch-select" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10">
                        <option value="">Pilih Cabang</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" @selected(old('branch_id') == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="employee-department-select" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Departemen <span class="text-rose-500">*</span>
                    </label>
                    <select name="department_id" id="employee-department-select" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10">
                        <option value="">Pilih Cabang terlebih dahulu</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}" @selected(old('department_id') == $d->id)>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="employee-designation-select" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Jabatan <span class="text-rose-500">*</span>
                    </label>
                    <select name="designation_id" id="employee-designation-select" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10">
                        <option value="">Pilih Departemen terlebih dahulu</option>
                        @foreach($designations as $des)
                            <option value="{{ $des->id }}" @selected(old('designation_id') == $des->id)>{{ $des->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="manager_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Atasan Langsung (Manager)
                    </label>
                    <select name="manager_id" id="manager_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10">
                        <option value="">Tidak Memiliki Atasan Langsung</option>
                        @foreach($managers as $m)
                            <option value="{{ $m->id }}" @selected(old('manager_id') == $m->id)>
                                {{ $m->user->name }} ({{ $m->employee_code }} - {{ $m->designation->title ?? '' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="employment_status" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Status Ketenagakerjaan <span class="text-rose-500">*</span>
                    </label>
                    <select name="employment_status" id="employment_status" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10">
                        <option value="PKWTT" @selected(old('employment_status') === 'PKWTT')>Tetap (PKWTT)</option>
                        <option value="PKWT" @selected(old('employment_status', 'PKWT') === 'PKWT')>Kontrak (PKWT)</option>
                        <option value="PROBATION" @selected(old('employment_status') === 'PROBATION')>Probation (Percobaan)</option>
                        <option value="INTERN" @selected(old('employment_status') === 'INTERN')>Magang (Internship)</option>
                    </select>
                </div>
                <div>
                    <label for="join_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Tanggal Masuk / Bergabung <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="join_date" id="join_date" value="{{ old('join_date', now()->format('Y-m-d')) }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition">
                </div>
                <div>
                    <label for="contract_end_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Tanggal Berakhir Kontrak (Opsional)
                    </label>
                    <input type="date" name="contract_end_date" id="contract_end_date" value="{{ old('contract_end_date') }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition">
                </div>
            </div>
        </x-card>

        <!-- 3. Biodata Identitas Pribadi -->
        <x-card class="space-y-5">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-200 pb-2.5 flex items-center gap-2">
                <span>📋</span> 3. Identitas & Biodata Pribadi
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="nik_ktp" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        NIK KTP (16 Digit) <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="nik_ktp" id="nik_ktp" maxlength="16" value="{{ old('nik_ktp') }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-mono focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition">
                </div>
                <div>
                    <label for="gender" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Jenis Kelamin
                    </label>
                    <select name="gender" id="gender" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10">
                        <option value="">Pilih Jenis Kelamin</option>
                        <option value="MALE" @selected(old('gender') === 'MALE')>Laki-Laki (Male)</option>
                        <option value="FEMALE" @selected(old('gender') === 'FEMALE')>Perempuan (Female)</option>
                    </select>
                </div>
                <div>
                    <label for="birth_place" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Tempat Lahir
                    </label>
                    <input type="text" name="birth_place" id="birth_place" value="{{ old('birth_place') }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition">
                </div>
                <div>
                    <label for="birth_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Tanggal Lahir
                    </label>
                    <input type="date" name="birth_date" id="birth_date" value="{{ old('birth_date') }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition">
                </div>
                <div>
                    <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nomor Handphone / WhatsApp
                    </label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}" placeholder="0812xxxxxxxx" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition">
                </div>
                <div>
                    <label for="npwp" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        NPWP
                    </label>
                    <input type="text" name="npwp" id="npwp" value="{{ old('npwp') }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-mono focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition">
                </div>
            </div>
        </x-card>

        <!-- 4. Data Rekening Penggajian -->
        <x-card class="space-y-5">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-200 pb-2.5 flex items-center gap-2">
                <span>💳</span> 4. Rekening Bank Payroll
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="bank_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nama Bank
                    </label>
                    <input type="text" name="bank_name" id="bank_name" value="{{ old('bank_name') }}" placeholder="BCA / Mandiri / BNI" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition">
                </div>
                <div>
                    <label for="bank_account_no" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nomor Rekening
                    </label>
                    <input type="text" name="bank_account_no" id="bank_account_no" value="{{ old('bank_account_no') }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-mono focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition">
                </div>
                <div>
                    <label for="bank_account_holder" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nama Pemilik Rekening
                    </label>
                    <input type="text" name="bank_account_holder" id="bank_account_holder" value="{{ old('bank_account_holder') }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition">
                </div>
            </div>
        </x-card>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('employees.index') }}" class="px-5 py-3 sm:py-2.5 rounded-xl border border-slate-300 text-slate-600 text-xs sm:text-sm font-semibold hover:bg-slate-100 transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-3 sm:py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-sky-600/20 transition">
                Simpan & Daftarkan Karyawan
            </button>
        </div>

    </form>

</div>
@endsection
