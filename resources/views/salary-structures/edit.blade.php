@extends('layouts.app')

@section('title', 'Atur Struktur Gaji Karyawan - NexusHRIS')
@section('page_title', 'Atur Struktur Gaji')

@section('content')
<div class="space-y-6">

    <!-- Header Navigation Back -->
    <div class="flex items-center gap-3">
        <a href="{{ route('salary-structures.index') }}" class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Atur Struktur Gaji Karyawan</h2>
            <p class="text-xs sm:text-sm text-slate-500">Konfigurasi komponen pendapatan dan verifikasi pemotongan iuran resmi.</p>
        </div>
    </div>

    <!-- Info Singkat Karyawan -->
    <div class="bg-gradient-to-r from-sky-950 to-[#0B1E36] rounded-2xl p-5 text-white shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-sky-600/30 text-sky-300 font-extrabold flex items-center justify-center text-lg border border-sky-500/20">
                    {{ substr($employee->user->name, 0, 2) }}
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-bold">{{ $employee->user->name }}</h3>
                    <p class="text-xs text-sky-200/80 font-mono">{{ $employee->employee_code }} &bull; {{ $employee->designation->name ?? '-' }} ({{ $employee->department->name ?? '-' }})</p>
                </div>
            </div>
            <div class="text-left sm:text-right border-t sm:border-t-0 border-sky-800/40 pt-2 sm:pt-0">
                <p class="text-xs text-slate-300">Cabang: <span class="font-semibold text-white">{{ $employee->branch->name ?? '-' }}</span></p>
                <p class="text-xs text-slate-300">Bank: <span class="font-semibold text-white">{{ $employee->bank_name ?? 'BCA' }} - {{ $employee->bank_account_no ?? 'Belum ada' }}</span></p>
            </div>
        </div>
    </div>

    @if($errors->any())
        <x-alert type="error">
            <ul class="list-disc list-inside text-xs space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <form method="POST" action="{{ route('salary-structures.update', $employee->id) }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Formulir Input Komponen Pendapatan -->
            <div class="lg:col-span-2 space-y-6">
                <x-card title="Komponen Pendapatan Rutin" subtitle="Masukkan besaran upah pokok dan tunjangan bulanan.">
                    <div class="space-y-4">
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Gaji Pokok (Basic Salary) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-3 text-xs font-bold text-slate-400">Rp</span>
                                <input 
                                    type="number" 
                                    name="basic_salary" 
                                    step="0.01" 
                                    min="0" 
                                    required 
                                    value="{{ old('basic_salary', $salaryStructure->basic_salary ?? 0) }}" 
                                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition"
                                >
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">Dasar perhitungan utama lembur (PP 35/2021) dan iuran BPJS.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Tunjangan Tetap (Jabatan / Keahlian)
                            </label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-3 text-xs font-bold text-slate-400">Rp</span>
                                <input 
                                    type="number" 
                                    name="fixed_allowance" 
                                    step="0.01" 
                                    min="0" 
                                    value="{{ old('fixed_allowance', $salaryStructure->fixed_allowance ?? 0) }}" 
                                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition"
                                >
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">Tunjangan yang dibayarkan rutin tanpa dipengaruhi kehadiran.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Tunjangan Transportasi
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3.5 top-3 text-xs font-bold text-slate-400">Rp</span>
                                    <input 
                                        type="number" 
                                        name="transport_allowance" 
                                        step="0.01" 
                                        min="0" 
                                        value="{{ old('transport_allowance', $salaryStructure->transport_allowance ?? 0) }}" 
                                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition"
                                    >
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Tunjangan Makan
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3.5 top-3 text-xs font-bold text-slate-400">Rp</span>
                                    <input 
                                        type="number" 
                                        name="meal_allowance" 
                                        step="0.01" 
                                        min="0" 
                                        value="{{ old('meal_allowance', $salaryStructure->meal_allowance ?? 0) }}" 
                                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition"
                                    >
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Status PTKP Pajak (PPh 21 TER 2024)
                            </label>
                            <select name="ptkp_status" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10">
                                <option value="TK/0">TK/0 - Tidak Kawin, 0 Tanggungan (Kategori TER A)</option>
                                <option value="TK/1">TK/1 - Tidak Kawin, 1 Tanggungan (Kategori TER A)</option>
                                <option value="K/0">K/0 - Kawin, 0 Tanggungan (Kategori TER A)</option>
                                <option value="TK/2">TK/2 - Tidak Kawin, 2 Tanggungan (Kategori TER B)</option>
                                <option value="TK/3">TK/3 - Tidak Kawin, 3 Tanggungan (Kategori TER B)</option>
                                <option value="K/1">K/1 - Kawin, 1 Tanggungan (Kategori TER B)</option>
                                <option value="K/2">K/2 - Kawin, 2 Tanggungan (Kategori TER B)</option>
                                <option value="K/3">K/3 - Kawin, 3 Tanggungan (Kategori TER C)</option>
                            </select>
                            <p class="text-[11px] text-slate-400 mt-1">Mengacu pada PMK 168/2023 & PP 58/2023 untuk penentuan persentase tarif pajak bulanan.</p>
                        </div>

                    </div>
                </x-card>

                <!-- Tombol Aksi Simpan -->
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('salary-structures.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 font-semibold text-xs sm:text-sm transition">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-sky-600/20 transition">
                        Simpan Struktur Gaji
                    </button>
                </div>
            </div>

            <!-- Ringkasan Kalkulasi & Regulasi Samping -->
            <div class="space-y-6">
                <x-card title="Estimasi Penggajian" subtitle="Simulasi otomatis sistem berdasarkan data saat ini.">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center text-xs pb-3 border-b border-slate-100">
                            <span class="text-slate-500">Pendapatan Kotor (Gross):</span>
                            <span class="font-mono font-bold text-slate-900">Rp {{ number_format($gross, 0, ',', '.') }}</span>
                        </div>

                        <!-- Rincian Pemotongan Regulasi -->
                        <div class="bg-slate-50 rounded-xl p-3 space-y-2 border border-slate-200/60 text-xs">
                            <div class="font-bold text-slate-700 text-[11px] uppercase tracking-wider">Potongan Karyawan (Deductions):</div>
                            <div class="flex justify-between text-slate-600">
                                <span>BPJS Kesehatan (1%):</span>
                                <span class="font-mono text-rose-600">- Rp {{ number_format($bpjs['employee']['bpjs_kes'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>BPJS TK JHT (2%):</span>
                                <span class="font-mono text-rose-600">- Rp {{ number_format($bpjs['employee']['jht'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>BPJS TK JP (1%):</span>
                                <span class="font-mono text-rose-600">- Rp {{ number_format($bpjs['employee']['jp'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>PPh 21 TER ({{ $tax['rate'] * 100 }}%):</span>
                                <span class="font-mono text-rose-600">- Rp {{ number_format($tax['tax_amount'], 0, ',', '.') }}</span>
                            </div>
                            <div class="pt-2 border-t border-slate-200 flex justify-between font-bold text-rose-700">
                                <span>Total Potongan:</span>
                                <span class="font-mono">- Rp {{ number_format($deductions, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <!-- Take Home Pay Card -->
                        <div class="bg-sky-50 border border-sky-200/80 rounded-xl p-4">
                            <div class="text-[11px] font-bold text-sky-800 uppercase tracking-wider">Estimasi Gaji Bersih (THP):</div>
                            <div class="text-xl sm:text-2xl font-extrabold text-sky-950 font-mono mt-1">
                                Rp {{ number_format($estimatedThp, 0, ',', '.') }}
                            </div>
                            <p class="text-[10px] text-sky-600 mt-1">*Nilai final bulanan akan disesuaikan dengan rekonsiliasi kehadiran, lembur, dan kasbon.</p>
                        </div>

                        <!-- Iuran Tanggungan Perusahaan -->
                        <div class="bg-amber-50/60 border border-amber-200/60 rounded-xl p-3 text-xs space-y-1 text-slate-600">
                            <div class="font-bold text-amber-800 text-[11px] uppercase tracking-wider">Tanggungan Perusahaan:</div>
                            <div class="flex justify-between">
                                <span>BPJS Kesehatan (4%):</span>
                                <span class="font-mono font-medium">Rp {{ number_format($bpjs['employer']['bpjs_kes'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>BPJS TK (JHT, JP, JKK, JKM):</span>
                                <span class="font-mono font-medium">Rp {{ number_format($bpjs['employer']['total_bpjs_tk'], 0, ',', '.') }}</span>
                            </div>
                        </div>

                    </div>
                </x-card>
            </div>

        </div>
    </form>

</div>
@endsection
