@extends('layouts.app')

@section('title', 'Audit Trail Logs - NexusHRIS')
@section('page_title', 'Rekam Jejak Keamanan')

@section('content')
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        
        <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-slate-900">Audit Trail (Log Aktivitas)</h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Memantau seluruh interaksi pengguna, login sesi web, dan akses mobile API.</p>
            </div>
            <div class="text-xs font-semibold px-3 py-1.5 rounded-xl bg-slate-100 text-slate-600 self-start sm:self-auto">
                Total: {{ $logs->total() }} Catatan
            </div>
        </div>

        <!-- Tabel Responsif -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-4 sm:px-6">Waktu Kejadian</th>
                        <th class="py-3.5 px-4">Pengguna</th>
                        <th class="py-3.5 px-4">Aktivitas (Event)</th>
                        <th class="py-3.5 px-4">Alamat IP</th>
                        <th class="py-3.5 px-4 sm:px-6">User Agent</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 sm:px-6 font-mono text-xs text-slate-500 whitespace-nowrap">
                                {{ $log->created_at->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-900 whitespace-nowrap">
                                {{ $log->user->name ?? 'Sistem/Guest' }}
                                <span class="block text-[11px] text-slate-400 font-normal">{{ $log->user->email ?? '-' }}</span>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if ($log->event === 'web_login' || $log->event === 'api_login')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        {{ $log->event }}
                                    </span>
                                @elseif ($log->event === 'web_logout' || $log->event === 'api_logout')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        {{ $log->event }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-sky-50 text-sky-700">
                                        {{ $log->event }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-mono text-xs text-slate-600 whitespace-nowrap">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </td>
                            <td class="py-3.5 px-4 sm:px-6 text-xs text-slate-400 max-w-xs truncate" title="{{ $log->user_agent }}">
                                {{ $log->user_agent ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 text-sm">
                                Belum ada rekam jejak aktivitas yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($logs->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        @endif

    </div>
@endsection
