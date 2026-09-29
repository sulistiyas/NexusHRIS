@extends('layouts.app')

@section('title', 'Presensi Kehadiran Harian - NexusHRIS')
@section('page_title', 'Presensi Kehadiran')

@section('content')
<div class="space-y-6">

    <!-- Header & Shift Info -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-gradient-to-r from-sky-950 via-[#0B1E36] to-sky-900 text-white p-6 rounded-2xl shadow-xl shadow-sky-950/20">
        <div>
            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-sky-500/20 text-sky-300 border border-sky-400/30">
                {{ now()->translatedFormat('l, d F Y') }}
            </span>
            <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight mt-1">Presensi Cerdas Karyawan</h2>
            <p class="text-xs sm:text-sm text-slate-300 mt-0.5">
                Cabang: <span class="font-semibold text-white">{{ $employee->branch?->name ?? 'Pusat' }}</span> | 
                Shift: <span class="font-semibold text-sky-400">{{ $shiftToday?->name ?? 'Shift Normal' }}</span> 
                ({{ substr($shiftToday?->start_time ?? '08:00', 0, 5) }} - {{ substr($shiftToday?->end_time ?? '17:00', 0, 5) }})
            </p>
        </div>
        <div class="flex items-center gap-3">
            <div class="px-4 py-2 rounded-xl bg-white/10 backdrop-blur-md border border-white/10 text-center">
                <span class="block text-[10px] uppercase font-bold text-slate-300">Toleransi</span>
                <span class="text-sm font-extrabold text-amber-400">{{ $shiftToday?->grace_period_minutes ?? 15 }} Menit</span>
            </div>
            <div class="px-4 py-2 rounded-xl bg-white/10 backdrop-blur-md border border-white/10 text-center">
                <span class="block text-[10px] uppercase font-bold text-slate-300">Status Hari Ini</span>
                <span class="text-sm font-extrabold {{ $attendanceToday ? ($attendanceToday->status === 'LATE' ? 'text-rose-400' : 'text-emerald-400') : 'text-slate-300' }}">
                    {{ $attendanceToday ? $attendanceToday->status : 'BELUM ABSEN' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Alert Notifikasi -->
    @if(session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif
    @if(session('error'))
        <x-alert type="error">{{ session('error') }}</x-alert>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Kolom Kiri: Kamera & Aksi Absensi (Clock In / Clock Out) -->
        <div class="lg:col-span-7 space-y-6">
            <x-card id="attendance-camera-app" 
                data-branch-lat="{{ $employee->branch?->latitude ?? '' }}" 
                data-branch-lon="{{ $employee->branch?->longitude ?? '' }}" 
                data-branch-radius="{{ $employee->branch?->radius_meters ?? 100 }}"
            >
                <div class="space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800">Kamera Selfie & Geofencing</h3>
                        <span id="geo-status-badge" class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                            Memeriksa GPS...
                        </span>
                    </div>

                    <p id="geo-distance-text" class="text-xs text-slate-500 font-medium"></p>

                    <!-- Viewport Kamera Video & Canvas Snapshot -->
                    <div class="relative w-full aspect-4/3 bg-slate-900 rounded-2xl overflow-hidden flex items-center justify-center border-2 border-slate-200 shadow-inner">
                        <div id="camera-placeholder" class="text-center p-4">
                            <span class="text-3xl block mb-2">📸</span>
                            <span class="text-xs text-slate-400">Menghubungkan ke kamera depan...</span>
                        </div>
                        <video id="webcam-video" autoplay playsinline class="w-full h-full object-cover hidden"></video>
                        <canvas id="webcam-canvas" class="hidden"></canvas>
                        <img id="selfie-preview" alt="Selfie Absensi" class="w-full h-full object-cover hidden">
                    </div>

                    <!-- Tombol Ambil Snapshot -->
                    <div class="flex items-center justify-center gap-3">
                        <button type="button" id="btn-capture-selfie" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs sm:text-sm shadow-md transition">
                            📷 Ambil Foto Selfie
                        </button>
                        <button type="button" id="btn-retake-selfie" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold text-xs sm:text-sm hover:bg-slate-50 hidden transition">
                            🔄 Foto Ulang
                        </button>
                    </div>

                    <!-- Form Aksi Clock-In / Clock-Out -->
                    @if(! $attendanceToday || ! $attendanceToday->clock_in)
                        <!-- FORM CLOCK-IN -->
                        <form method="POST" action="{{ route('ess.attendance.clock-in') }}" class="space-y-4 pt-4 border-t border-slate-100">
                            @csrf
                            <input type="hidden" name="latitude" id="latitude-input">
                            <input type="hidden" name="longitude" id="longitude-input">
                            <input type="hidden" name="selfie" id="selfie-input">

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Tipe Kerja</label>
                                    <select name="work_type" id="work_type_select" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10">
                                        <option value="WFO">WFO (Work From Office)</option>
                                        <option value="WFH">WFH (Work From Home)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Catatan (Opsional)</label>
                                    <input type="text" name="notes" placeholder="Contoh: Sedang bertugas dinas" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10">
                                </div>
                            </div>

                            <button type="submit" id="btn-submit-attendance" disabled class="w-full py-3.5 rounded-xl bg-sky-600 hover:bg-sky-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-extrabold text-sm shadow-lg shadow-sky-600/30 transition">
                                🟢 Masuk Kerja Sekarang (Clock-In)
                            </button>
                        </form>
                    @elseif(! $attendanceToday->clock_out)
                        <!-- FORM CLOCK-OUT -->
                        <form method="POST" action="{{ route('ess.attendance.clock-out') }}" class="space-y-4 pt-4 border-t border-slate-100">
                            @csrf
                            <input type="hidden" name="latitude" id="latitude-input">
                            <input type="hidden" name="longitude" id="longitude-input">
                            <input type="hidden" name="selfie" id="selfie-input">

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Catatan Pulang (Opsional)</label>
                                <input type="text" name="notes" placeholder="Contoh: Pekerjaan selesai tepat waktu" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10">
                            </div>

                            <button type="submit" id="btn-submit-attendance" disabled class="w-full py-3.5 rounded-xl bg-orange-500 hover:bg-orange-600 disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-extrabold text-sm shadow-lg shadow-orange-500/30 transition">
                                🔴 Pulang Kerja Sekarang (Clock-Out)
                            </button>
                        </form>
                    @else
                        <!-- SUDAH SELESAI ABSEN -->
                        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-center">
                            <span class="text-2xl block mb-1">🎉</span>
                            <h4 class="font-bold text-emerald-800 text-sm">Presensi Hari Ini Lengkap</h4>
                            <p class="text-xs text-emerald-600 mt-0.5">Anda sudah melakukan Clock-In dan Clock-Out.</p>
                        </div>
                    @endif

                </div>
            </x-card>
        </div>

        <!-- Kolom Kanan: Rincian Hari Ini & Riwayat Terakhir -->
        <div class="lg:col-span-5 space-y-6">

            <!-- Card Status Hari Ini -->
            <x-card>
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800 mb-4 pb-2 border-b border-slate-100">
                    Catatan Kehadiran Hari Ini
                </h3>

                <div class="grid grid-cols-2 gap-4">
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-center">
                        <span class="block text-[11px] font-bold text-slate-500 uppercase">Jam Masuk</span>
                        <span class="text-base sm:text-lg font-extrabold font-mono text-sky-700 mt-1 block">
                            {{ $attendanceToday?->clock_in ? substr($attendanceToday->clock_in, 0, 5) : '--:--' }}
                        </span>
                        @if($attendanceToday && $attendanceToday->late_minutes > 0)
                            <span class="text-[10px] font-semibold text-rose-500">Telat {{ $attendanceToday->late_minutes }}m</span>
                        @endif
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-center">
                        <span class="block text-[11px] font-bold text-slate-500 uppercase">Jam Pulang</span>
                        <span class="text-base sm:text-lg font-extrabold font-mono text-slate-700 mt-1 block">
                            {{ $attendanceToday?->clock_out ? substr($attendanceToday->clock_out, 0, 5) : '--:--' }}
                        </span>
                        @if($attendanceToday && $attendanceToday->early_leave_minutes > 0)
                            <span class="text-[10px] font-semibold text-amber-500">Cepat {{ $attendanceToday->early_leave_minutes }}m</span>
                        @endif
                    </div>
                </div>

                @if($attendanceToday && $attendanceToday->in_selfie_path)
                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <span class="block text-[11px] font-bold text-slate-500 uppercase mb-2">Foto Selfie Masuk:</span>
                        <img src="{{ asset('storage/' . $attendanceToday->in_selfie_path) }}" alt="Selfie Masuk" class="w-24 h-24 object-cover rounded-xl border border-slate-200">
                    </div>
                @endif
            </x-card>

            <!-- Card Riwayat Terakhir -->
            <x-card class="overflow-hidden p-0">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800">Riwayat Terakhir</h3>
                </div>
                <div class="divide-y divide-slate-100 max-h-80 overflow-y-auto">
                    @forelse($recentAttendances as $item)
                        <div class="px-5 py-3 flex items-center justify-between text-xs hover:bg-slate-50 transition">
                            <div>
                                <span class="font-bold text-slate-900 block">{{ $item->date->format('d M Y') }}</span>
                                <span class="text-slate-500 font-mono">
                                    {{ substr($item->clock_in ?? '--:--', 0, 5) }} - {{ substr($item->clock_out ?? '--:--', 0, 5) }}
                                </span>
                            </div>
                            <div>
                                @if($item->status === 'PRESENT')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        TEPAT WAKTU
                                    </span>
                                @elseif($item->status === 'LATE')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        TELAT {{ $item->late_minutes }}M
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                        {{ $item->status }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-6 text-center text-xs text-slate-400">
                            Belum ada riwayat absensi.
                        </div>
                    @endforelse
                </div>
            </x-card>

        </div>

    </div>

</div>
@endsection
