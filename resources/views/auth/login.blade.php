@extends('layouts.guest')

@section('title', 'Masuk Sistem - NexusHRIS')

@section('content')
    <!-- Container Kartu Login -->
    <div class="bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl shadow-sky-950/50 border border-white/20 p-6 sm:p-8 text-slate-800">
        
        <!-- Branding Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-sky-700 via-sky-600 to-blue-800 text-white font-black text-2xl mb-3 shadow-lg shadow-sky-700/30 ring-4 ring-orange-500/20">
                N
            </div>
            <div class="flex items-center justify-center gap-1.5">
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Nexus<span class="text-sky-600">HRIS</span></h1>
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
            </div>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 font-medium">Enterprise HR & Workforce Management</p>
        </div>

        <!-- Pesan Status / Sukses -->
        @if (session('status'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm flex items-start gap-2.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 mt-1.5 shrink-0"></span>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <!-- Notifikasi Error Validasi -->
        @if ($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm">
                <div class="flex items-center gap-2 font-semibold text-rose-900 mb-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                    <span>Terjadi Kesalahan:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 text-xs text-rose-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Formulir Login -->
        <form method="POST" action="{{ route('login') }}" class="space-y-4 sm:space-y-5">
            @csrf

            <!-- Input Email -->
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Alamat Email Kerja
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    required 
                    autofocus
                    placeholder="contoh@nexus.test"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 text-slate-900 text-sm bg-slate-50 focus:bg-white focus:outline-none focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition duration-200 placeholder:text-slate-400"
                >
            </div>

            <!-- Input Password -->
            <div>
                <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Kata Sandi
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    placeholder="••••••••"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 text-slate-900 text-sm bg-slate-50 focus:bg-white focus:outline-none focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition duration-200 placeholder:text-slate-400"
                >
            </div>

            <!-- Remember Me Checkbox -->
            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input 
                        type="checkbox" 
                        id="remember" 
                        name="remember" 
                        class="w-4 h-4 rounded text-sky-600 border-slate-300 focus:ring-sky-500 focus:ring-offset-0"
                    >
                    <span class="text-xs text-slate-600 font-medium">Ingat perangkat ini</span>
                </label>
            </div>

            <!-- Tombol Submit (Biru Laut dengan aksen hover Oranye) -->
            <button 
                type="submit" 
                class="w-full py-3 px-4 bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-700 hover:to-blue-800 active:scale-[0.99] text-white font-semibold rounded-xl text-sm transition-all duration-200 shadow-lg shadow-sky-600/30 focus:outline-none focus:ring-4 focus:ring-sky-500/20"
            >
                Masuk ke Portal
            </button>
        </form>

        <!-- Akun Pengujian / Demo Helpers -->
        <div class="mt-8 pt-6 border-t border-slate-100">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Akun Pengujian (Demo)</span>
                <span class="text-[11px] font-semibold text-orange-600 bg-orange-50 border border-orange-200/60 px-2 py-0.5 rounded-full">
                    Password: password
                </span>
            </div>

            <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-150">
                    <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold uppercase bg-orange-100 text-orange-800 mb-1">Super Admin</span>
                    <p class="font-mono text-slate-700 truncate text-[11px]">admin@nexus.test</p>
                </div>
                <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-150">
                    <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold uppercase bg-sky-100 text-sky-800 mb-1">HR Admin</span>
                    <p class="font-mono text-slate-700 truncate text-[11px]">hr@nexus.test</p>
                </div>
                <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-150">
                    <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800 mb-1">Manager</span>
                    <p class="font-mono text-slate-700 truncate text-[11px]">manager@nexus.test</p>
                </div>
                <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-150">
                    <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-200 text-slate-700 mb-1">Employee</span>
                    <p class="font-mono text-slate-700 truncate text-[11px]">employee@nexus.test</p>
                </div>
            </div>
        </div>

    </div>
@endsection
