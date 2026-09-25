<header class="bg-[#0B1E36] text-white border-b border-sky-950 shadow-sm sticky top-0 z-30">
    <div class="px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        
        <!-- Sisi Kiri: Tombol Hamburger Mobile & Breadcrumb/Title -->
        <div class="flex items-center gap-3">
            <button id="sidebar-toggle" type="button" class="lg:hidden p-2 text-slate-300 hover:text-white rounded-xl hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
            <h1 class="font-bold text-base sm:text-lg text-white">@yield('page_title', 'NexusHRIS Portal')</h1>
        </div>

        <!-- Sisi Kanan: Status Role & Tombol Keluar -->
        <div class="flex items-center gap-3 sm:gap-5">
            <div class="hidden sm:block text-right">
                <p class="text-sm font-semibold text-white leading-tight">{{ auth()->user()->name }}</p>
                
                @role('super_admin')
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-orange-400 uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-orange-400"></span> Super Admin
                    </span>
                @endrole
                @role('hr_admin')
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-sky-400 uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-sky-400"></span> HR Admin
                    </span>
                @endrole
                @role('manager')
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-400 uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Manager
                    </span>
                @endrole
                @role('employee')
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-300 uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Employee
                    </span>
                @endrole
            </div>

            <!-- Tombol Logout -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button 
                    type="submit" 
                    class="text-xs sm:text-sm font-medium text-rose-300 hover:text-white px-3.5 py-2 rounded-xl border border-rose-400/30 hover:bg-rose-600/30 transition duration-150"
                >
                    Keluar
                </button>
            </form>
        </div>

    </div>
</header>
