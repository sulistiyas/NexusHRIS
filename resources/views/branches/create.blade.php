@extends('layouts.app')

@section('title', 'Tambah Kantor Cabang - NexusHRIS')
@section('page_title', 'Tambah Cabang')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Formulir Tambah Cabang</h2>
            <p class="text-xs sm:text-sm text-slate-500">Tentukan nama, alamat, zona waktu, serta geofencing radius absensi kantor.</p>
        </div>
        <a href="{{ route('branches.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-600 font-semibold text-xs sm:text-sm hover:bg-slate-100 transition">
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
        <form method="POST" action="{{ route('branches.store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Kode Cabang -->
                <div>
                    <label for="code" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Kode Cabang <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="code" 
                        id="code" 
                        value="{{ old('code') }}" 
                        placeholder="Contoh: HQ-JKT atau BR-BDG" 
                        required 
                        class="w-full px-4 py-3 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 font-mono transition"
                    >
                </div>

                <!-- Nama Cabang -->
                <div>
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nama Cabang <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="name" 
                        id="name" 
                        value="{{ old('name') }}" 
                        placeholder="Contoh: Kantor Pusat Jakarta" 
                        required 
                        class="w-full px-4 py-3 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition"
                    >
                </div>
            </div>

            <!-- Alamat Lengkap -->
            <div>
                <label for="address" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Alamat Lengkap Kantor
                </label>
                <textarea 
                    name="address" 
                    id="address" 
                    rows="3" 
                    placeholder="Jl. Jendral Sudirman Kav. 25, Jakarta Selatan..." 
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition"
                >{{ old('address') }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Radius Geofence (Meter) -->
                <div>
                    <label for="radius_meters" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Radius Absensi (Meter) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input 
                            type="number" 
                            name="radius_meters" 
                            id="radius_meters" 
                            value="{{ old('radius_meters', 50) }}" 
                            min="5" 
                            max="5000" 
                            required 
                            class="w-full px-4 py-3 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition"
                        >
                        <span class="absolute right-4 top-3 text-xs text-slate-400 font-semibold">Meter</span>
                    </div>
                    <span class="text-[11px] text-slate-400 mt-1 block">Toleransi jarak karyawan dari titik koordinat saat presensi masuk.</span>
                </div>

                <!-- Zona Waktu -->
                <div>
                    <label for="timezone" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Zona Waktu <span class="text-rose-500">*</span>
                    </label>
                    <select 
                        name="timezone" 
                        id="timezone" 
                        required 
                        class="w-full px-4 py-3 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition bg-white"
                    >
                        <option value="Asia/Jakarta" @selected(old('timezone', 'Asia/Jakarta') === 'Asia/Jakarta')>WIB - Asia/Jakarta (UTC+7)</option>
                        <option value="Asia/Makassar" @selected(old('timezone') === 'Asia/Makassar')>WITA - Asia/Makassar (UTC+8)</option>
                        <option value="Asia/Jayapura" @selected(old('timezone') === 'Asia/Jayapura')>WIT - Asia/Jayapura (UTC+9)</option>
                    </select>
                </div>
            </div>

            <!-- Interaktif Peta Geofencing Leaflet -->
            <div class="border-t border-slate-200 pt-5 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Titik Koordinat & Geofencing GPS</h3>
                        <p class="text-xs text-slate-500">Klik peta atau geser pin marker untuk menentukan koordinat kantor.</p>
                    </div>
                    <button 
                        type="button" 
                        id="btn-get-location" 
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition"
                    >
                        <span>🎯</span>
                        <span>Gunakan Lokasi Saat Ini</span>
                    </button>
                </div>

                <!-- Container Map Leaflet -->
                <div 
                    id="branch-map" 
                    data-interactive="true"
                    data-lat="{{ old('latitude', -6.2088) }}" 
                    data-lng="{{ old('longitude', 106.8456) }}" 
                    data-radius="{{ old('radius_meters', 50) }}"
                    class="w-full h-72 sm:h-80 rounded-2xl border border-slate-300 shadow-inner z-10"
                ></div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="latitude" class="block text-xs font-semibold text-slate-600 mb-1">Latitude</label>
                        <input 
                            type="text" 
                            name="latitude" 
                            id="latitude" 
                            value="{{ old('latitude', -6.2088) }}" 
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-mono bg-slate-50 focus:bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-500/10 transition"
                        >
                    </div>
                    <div>
                        <label for="longitude" class="block text-xs font-semibold text-slate-600 mb-1">Longitude</label>
                        <input 
                            type="text" 
                            name="longitude" 
                            id="longitude" 
                            value="{{ old('longitude', 106.8456) }}" 
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-mono bg-slate-50 focus:bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-500/10 transition"
                        >
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi Simpan -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <a href="{{ route('branches.index') }}" class="px-5 py-3 sm:py-2.5 rounded-xl border border-slate-300 text-slate-600 text-xs sm:text-sm font-semibold hover:bg-slate-100 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-3 sm:py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-sky-600/20 transition">
                    Simpan Cabang
                </button>
            </div>

        </form>
    </x-card>

</div>
@endsection
