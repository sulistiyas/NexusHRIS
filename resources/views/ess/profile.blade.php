@extends('layouts.app')

@section('title', 'Profil Saya (ESS) - NexusHRIS')
@section('page_title', 'Profil Saya')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    @if(session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif
    @if(isset($errors) && $errors->any())
        <x-alert type="error">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <!-- Kartu Header Profil Pengguna -->
    <div class="bg-gradient-to-r from-[#081627] to-[#0f2c4d] p-6 sm:p-8 rounded-2xl text-white shadow-xl flex flex-col sm:flex-row sm:items-center gap-6 border border-sky-950">
        <div class="relative group">
            @if($employee?->avatar_url)
                <img src="{{ asset($employee->avatar_url) }}" alt="Avatar" class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover shadow-lg ring-4 ring-white/10">
            @else
                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-sky-600 text-white font-black text-3xl flex items-center justify-center shadow-lg ring-4 ring-white/10">
                    {{ substr($user->name, 0, 2) }}
                </div>
            @endif
        </div>

        <div class="flex-1">
            <div class="flex flex-wrap items-center gap-3">
                <h2 class="text-xl sm:text-2xl font-black">{{ $user->name }}</h2>
                @if($employee)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-sky-500/20 text-sky-300 border border-sky-400/30">
                        {{ $employee->employee_code }}
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-orange-500/20 text-orange-300 border border-orange-400/30 uppercase tracking-wide">
                        {{ $user->roles->first()?->name ?? 'User' }}
                    </span>
                @endif
            </div>

            @if($employee)
                <p class="text-xs sm:text-sm text-slate-300 mt-1">{{ $employee->designation?->title }} &bull; {{ $employee->department?->name }}</p>
                <p class="text-xs text-slate-400 mt-1">Cabang: {{ $employee->branch?->name }} | Bergabung: {{ $employee->join_date ? $employee->join_date->format('d M Y') : '-' }}</p>
            @else
                <p class="text-xs sm:text-sm text-slate-300 mt-1">Akun Administrator Sistem</p>
                <p class="text-xs text-slate-400 mt-1">{{ $user->email }} &bull; Terdaftar sejak {{ $user->created_at->format('d M Y') }}</p>
            @endif
        </div>

        @if($employee)
            <!-- Form Ubah Foto Profil -->
            <form method="POST" action="{{ route('ess.profile.avatar') }}" enctype="multipart/form-data" class="flex flex-col gap-2">
                @csrf
                <label class="px-3 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-semibold cursor-pointer text-center transition">
                    <span>📷 Ganti Foto</span>
                    <input type="file" name="avatar" accept="image/*" class="hidden" onchange="this.form.submit()">
                </label>
            </form>
        @endif
    </div>

    @if($employee)
        <!-- Grid Data Profil & Kontak Darurat (Untuk Akun Karyawan) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

            <!-- Form Update Kontak Pribadi -->
            <x-card class="space-y-4">
                <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">Kontak & Data Pribadi</h3>
                <form method="POST" action="{{ route('ess.profile.update') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor WhatsApp / HP</label>
                        <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tempat Lahir</label>
                        <input type="text" name="birth_place" value="{{ old('birth_place', $employee->birth_place) }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email (Akun)</label>
                        <input type="email" value="{{ $user->email }}" disabled class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-100 text-slate-500 text-xs sm:text-sm cursor-not-allowed">
                    </div>
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs shadow-md transition">
                        Simpan Perubahan
                    </button>
                </form>
            </x-card>

            <!-- Kontak Darurat -->
            <x-card class="space-y-4">
                <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">Kontak Darurat</h3>
                
                <!-- List Kontak Darurat -->
                <div class="space-y-2">
                    @forelse($employee->emergencyContacts as $contact)
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between text-xs">
                            <div>
                                <span class="font-bold text-slate-900">{{ $contact->name }}</span>
                                <span class="text-slate-400 block">{{ $contact->relationship }} &bull; {{ $contact->phone }}</span>
                            </div>
                            <form method="POST" action="{{ route('ess.emergency-contacts.destroy', $contact->id) }}" onsubmit="return confirm('Hapus kontak darurat ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-500 hover:text-rose-700 font-semibold">Hapus</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400">Belum ada kontak darurat yang dicatat.</p>
                    @endforelse
                </div>

                <!-- Form Tambah Kontak Darurat -->
                <form method="POST" action="{{ route('ess.emergency-contacts.store') }}" class="border-t border-slate-100 pt-3 space-y-2 text-xs">
                    @csrf
                    <span class="font-bold text-slate-700 block">+ Tambah Kontak Baru</span>
                    <input type="text" name="name" placeholder="Nama Lengkap" required class="w-full px-3 py-2 rounded-lg border border-slate-300">
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" name="relationship" placeholder="Hubungan (Istri/Ayah/dll)" required class="px-3 py-2 rounded-lg border border-slate-300">
                        <input type="text" name="phone" placeholder="No. Telepon" required class="px-3 py-2 rounded-lg border border-slate-300">
                    </div>
                    <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg font-semibold transition">
                        Simpan Kontak
                    </button>
                </form>
            </x-card>

        </div>

        <!-- Berkas Dokumen Saya -->
        <x-card class="space-y-3">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">Berkas Digital Terarsip</h3>
            <div class="divide-y divide-slate-100 text-xs">
                @forelse($employee->documents as $doc)
                    <div class="py-2.5 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="font-mono font-bold text-sky-700">{{ $doc->document_type }}</span>
                            <span class="text-slate-600">{{ $doc->file_name }}</span>
                        </div>
                        <a href="{{ route('documents.download', $doc) }}" class="text-sky-600 hover:text-sky-700 font-semibold">
                            ⬇️ Unduh Berkas
                        </a>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-2">Belum ada berkas digital.</p>
                @endforelse
            </div>
        </x-card>
    @else
        <!-- Informasi Akun Pengguna Non-Karyawan (Admin/Sistem) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <x-card class="space-y-4">
                <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">Informasi Akun</h3>
                <div class="space-y-3 text-xs sm:text-sm">
                    <div>
                        <span class="block text-xs font-bold text-slate-500">Nama Pengguna</span>
                        <span class="text-slate-800 font-medium">{{ $user->name }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-500">Alamat Email</span>
                        <span class="text-slate-800 font-medium">{{ $user->email }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-500">Peran Sistem</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-100 text-sky-800 capitalize">
                            {{ str_replace('_', ' ', $user->roles->first()?->name ?? 'User') }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-500">Status Akun</span>
                        <span class="inline-flex items-center gap-1.5 text-emerald-600 font-semibold text-xs">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Aktif
                        </span>
                    </div>
                </div>
            </x-card>

            <x-card class="space-y-4 bg-slate-50 border-dashed border-slate-200">
                <div class="flex items-center gap-3 text-sky-950 font-bold text-sm border-b border-slate-200 pb-2">
                    <span class="text-lg">ℹ️</span>
                    <span>Informasi Layanan ESS</span>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Halaman ini merupakan portal <strong>Employee Self-Service (ESS)</strong>. Akun Anda saat ini bertindak sebagai administrator sistem dan tidak terikat dengan rekaman profil karyawan.
                </p>
                <div class="p-3.5 bg-white rounded-xl border border-slate-200 text-xs text-slate-500 space-y-1.5">
                    <div class="font-bold text-slate-700">Fitur ESS yang aktif jika terhubung ke profil karyawan:</div>
                    <ul class="list-disc list-inside space-y-1 text-slate-600">
                        <li>Pengelolaan data pribadi & kontak darurat</li>
                        <li>Arsip dan unduh berkas digital resmi</li>
                        <li>Presensi mandiri & pengajuan cuti/izin (Sprint 3)</li>
                        <li>Akses slip gaji digital mandiri (Sprint 4)</li>
                    </ul>
                </div>
            </x-card>
        </div>
    @endif

</div>
@endsection
