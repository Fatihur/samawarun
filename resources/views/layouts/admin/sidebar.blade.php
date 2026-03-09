{{-- Sidebar --}}
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-30 w-64 shrink-0 transform bg-slate-900 px-4 py-6 text-slate-300 transition-transform duration-300 lg:static lg:translate-x-0 flex flex-col border-r border-slate-800 shadow-xl lg:shadow-none">
    <div class="mb-8 px-2 flex items-center justify-between">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 group">
            <div class="flex h-8 w-8 items-center justify-center rounded-md bg-gradient-to-br from-brand-500 to-amber-500 text-white shadow-md">
                <x-heroicon-o-bolt class="h-5 w-5" />
            </div>
            <span class="font-display text-xl font-bold tracking-wider text-white uppercase italic">Samawa<span class="text-brand-500">Admin</span></span>
        </a>
        <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white">
            <x-heroicon-o-x-mark class="h-6 w-6" />
        </button>
    </div>

    <nav class="flex-1 space-y-1.5 overflow-y-auto font-medium text-sm pb-8">
        {{-- Main Menu --}}
        <div class="pt-2 pb-1">
            <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Main Menu</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2.5 transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-brand-500/10 text-brand-400' : 'hover:bg-slate-800 hover:text-white' }}">
            <x-heroicon-o-squares-2x2 class="h-5 w-5 {{ request()->routeIs('admin.dashboard') ? 'text-brand-500' : 'text-slate-400 group-hover:text-white' }}" />
            Dashboard
        </a>
        
        {{-- Manajemen Event --}}
        <div class="pt-4 pb-1">
            <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Manajemen Event</p>
        </div>
        <a href="{{ route('admin.events.index') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2.5 transition-colors {{ request()->routeIs('admin.events.*') ? 'bg-brand-500/10 text-brand-400' : 'hover:bg-slate-800 hover:text-white' }}">
            <x-heroicon-o-calendar-days class="h-5 w-5 {{ request()->routeIs('admin.events.*') ? 'text-brand-500' : 'text-slate-400 group-hover:text-white' }}" />
            Kelola Event
        </a>
        <a href="{{ route('admin.participants.index') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2.5 transition-colors {{ request()->routeIs('admin.participants.*') ? 'bg-brand-500/10 text-brand-400' : 'hover:bg-slate-800 hover:text-white' }}">
            <x-heroicon-o-user-group class="h-5 w-5 {{ request()->routeIs('admin.participants.*') ? 'text-brand-500' : 'text-slate-400 group-hover:text-white' }}" />
            Data Peserta
        </a>
        <a href="{{ route('admin.distance-categories.index') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2.5 transition-colors {{ request()->routeIs('admin.distance-categories.*') ? 'bg-brand-500/10 text-brand-400' : 'hover:bg-slate-800 hover:text-white' }}">
            <x-heroicon-o-flag class="h-5 w-5 {{ request()->routeIs('admin.distance-categories.*') ? 'text-brand-500' : 'text-slate-400 group-hover:text-white' }}" />
            Kategori Jarak
        </a>
        <a href="{{ route('admin.bib-settings.index') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2.5 transition-colors {{ request()->routeIs('admin.bib-settings.*') ? 'bg-brand-500/10 text-brand-400' : 'hover:bg-slate-800 hover:text-white' }}">
            <x-heroicon-o-tag class="h-5 w-5 {{ request()->routeIs('admin.bib-settings.*') ? 'text-brand-500' : 'text-slate-400 group-hover:text-white' }}" />
            Pengaturan BIB
        </a>

        {{-- Hasil & Laporan --}}
        <div class="pt-4 pb-1">
            <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Hasil & Laporan</p>
        </div>
        <a href="{{ route('admin.race-timing.index') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2.5 transition-colors {{ request()->routeIs('admin.race-timing.*') ? 'bg-brand-500/10 text-brand-400' : 'hover:bg-slate-800 hover:text-white' }}">
            <x-heroicon-o-clock class="h-5 w-5 {{ request()->routeIs('admin.race-timing.*') ? 'text-brand-500' : 'text-slate-400 group-hover:text-white' }}" />
            Catat Waktu Race
        </a>
        <a href="{{ route('admin.race-reports.index') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2.5 transition-colors {{ request()->routeIs('admin.race-reports.*') ? 'bg-brand-500/10 text-brand-400' : 'hover:bg-slate-800 hover:text-white' }}">
            <x-heroicon-o-document-chart-bar class="h-5 w-5 {{ request()->routeIs('admin.race-reports.*') ? 'text-brand-500' : 'text-slate-400 group-hover:text-white' }}" />
            Laporan Hasil
        </a>
        <a href="{{ route('admin.certificates.index') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2.5 transition-colors {{ request()->routeIs('admin.certificates.*') || request()->routeIs('admin.participants.certificate') ? 'bg-brand-500/10 text-brand-400' : 'hover:bg-slate-800 hover:text-white' }}">
            <x-heroicon-o-document-check class="h-5 w-5 {{ request()->routeIs('admin.certificates.*') || request()->routeIs('admin.participants.certificate') ? 'text-brand-500' : 'text-slate-400 group-hover:text-white' }}" />
            Sertifikat Finisher
        </a>

        {{-- Pengaturan Halaman --}}
        <div class="pt-4 pb-1">
            <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Halaman Publik</p>
        </div>
        <a href="{{ route('admin.contacts.index') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2.5 transition-colors {{ request()->routeIs('admin.contacts.*') ? 'bg-brand-500/10 text-brand-400' : 'hover:bg-slate-800 hover:text-white' }}">
            <x-heroicon-o-phone class="h-5 w-5 {{ request()->routeIs('admin.contacts.*') ? 'text-brand-500' : 'text-slate-400 group-hover:text-white' }}" />
            Kontak & Header
        </a>
        <a href="{{ route('admin.gallery.index') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2.5 transition-colors {{ request()->routeIs('admin.gallery.*') ? 'bg-brand-500/10 text-brand-400' : 'hover:bg-slate-800 hover:text-white' }}">
            <x-heroicon-o-photo class="h-5 w-5 {{ request()->routeIs('admin.gallery.*') ? 'text-brand-500' : 'text-slate-400 group-hover:text-white' }}" />
            Galeri / Dokumentasi
        </a>

    </nav>

    
</aside>
