{{-- Top Navbar Mobile & Desktop --}}
<header class="sticky top-0 z-20 flex h-16 shrink-0 items-center justify-between border-b border-slate-200 bg-white/80 px-4 shadow-sm backdrop-blur-md sm:px-6 lg:px-8">
    {{-- Left Side: Hamburger (Mobile) & Breadcrumbs --}}
    <div class="flex items-center gap-4">
        <button @click="sidebarOpen = true" class="text-slate-500 hover:text-slate-800 focus:outline-none lg:hidden">
            <x-heroicon-o-bars-3 class="h-6 w-6" />
        </button>
        
        <nav class="hidden sm:flex" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 text-sm text-slate-500">
                <li>
                    <span class="font-medium text-slate-400">Admin</span>
                </li>
                <li>
                    <x-heroicon-s-chevron-right class="h-4 w-4 text-slate-300" />
                </li>
                <li>
                    <span class="font-bold text-slate-800">{{ $title ?? 'Dashboard' }}</span>
                </li>
            </ol>
        </nav>
    </div>

    {{-- Right Side: Notifications & User Profile Dropdown --}}
    <div class="flex items-center gap-3 sm:gap-4">
        
        {{-- Notification Bell Dropdown --}}
        <div x-data="{ notifOpen: false }" class="relative">
            <button @click="notifOpen = !notifOpen" @click.outside="notifOpen = false" class="relative rounded-full p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600 focus:outline-none focus:ring-2 focus:ring-brand-500">
                <span class="sr-only">Lihat notifikasi</span>
                <x-heroicon-o-bell class="h-6 w-6" />
                
                @if(Auth::user()->unreadNotifications->count() > 0)
                    <span class="absolute right-1.5 top-1.5 flex h-2.5 w-2.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-red-500"></span>
                    </span>
                @endif
            </button>

            {{-- Notification Panel --}}
            <div x-cloak x-show="notifOpen" 
                x-transition:enter="transition ease-out duration-100" 
                x-transition:enter-start="opacity-0 scale-95 transform" 
                x-transition:enter-end="opacity-100 scale-100 transform" 
                x-transition:leave="transition ease-in duration-75" 
                x-transition:leave-start="opacity-100 scale-100 transform" 
                x-transition:leave-end="opacity-0 scale-95 transform" 
                class="absolute right-0 mt-2 w-80 origin-top-right overflow-hidden rounded-xl border border-slate-100 bg-white shadow-xl ring-1 ring-black/5 focus:outline-none sm:w-96">
                
                <div class="flex items-center border-b border-slate-100 bg-slate-50 px-4 py-3">
                    <h3 class="text-sm font-bold text-slate-800">Notifikasi</h3>
                    @if(Auth::user()->unreadNotifications->count() > 0)
                        <span class="ml-2 inline-flex items-center justify-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-bold text-red-600">
                            {{ Auth::user()->unreadNotifications->count() }} Baru
                        </span>
                    @endif
                </div>

                <div class="max-h-96 overflow-y-auto">
                    @forelse(Auth::user()->unreadNotifications as $notification)
                        <a href="{{ route('admin.notifications.read', $notification->id) }}" wire:navigate class="block border-b border-slate-50 px-4 py-4 transition-colors hover:bg-slate-50">
                            <div class="flex items-start gap-3">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-100 text-brand-600">
                                    <x-heroicon-s-user-plus class="h-4 w-4" />
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-slate-800">
                                        <span class="font-bold">{{ $notification->data['participant_name'] ?? 'Peserta' }}</span> mendaftar di event <span class="font-bold">{{ $notification->data['event_name'] ?? '' }}</span>.
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $notification->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="px-4 py-8 text-center sm:py-12">
                            <x-heroicon-o-bell-slash class="mx-auto h-8 w-8 text-slate-300" />
                            <p class="mt-2 text-sm font-medium text-slate-500">Belum ada notifikasi baru</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- User Dropdown --}}
        <div x-data="{ profileOpen: false }" class="relative">
            <button @click="profileOpen = !profileOpen" @click.outside="profileOpen = false" class="flex items-center gap-2 rounded-full border border-slate-200 bg-white p-1 pr-3 shadow-sm transition-all hover:border-brand-500 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-800 text-xs font-bold text-white">
                    {{ substr(Auth::user()->name ?? 'A', 0, 1) }}
                </div>
                <span class="hidden text-sm font-bold text-slate-700 sm:block">{{ explode(' ', Auth::user()->name ?? 'Admin')[0] }}</span>
                <x-heroicon-s-chevron-down class="h-4 w-4 text-slate-400" />
            </button>

            {{-- Dropdown Menu --}}
            <div x-cloak x-show="profileOpen" 
                x-transition:enter="transition ease-out duration-100" 
                x-transition:enter-start="opacity-0 scale-95 transform" 
                x-transition:enter-end="opacity-100 scale-100 transform" 
                x-transition:leave="transition ease-in duration-75" 
                x-transition:leave-start="opacity-100 scale-100 transform" 
                x-transition:leave-end="opacity-0 scale-95 transform" 
                class="absolute right-0 mt-2 w-48 origin-top-right overflow-hidden rounded-xl border border-slate-100 bg-white shadow-xl ring-1 ring-black/5 focus:outline-none">
                <div class="border-b border-slate-100 px-4 py-3">
                    <p class="truncate text-sm font-bold text-slate-800">{{ Auth::user()->name ?? 'Administrator' }}</p>
                    <p class="truncate text-xs font-medium text-slate-500">{{ Auth::user()->email ?? 'admin@samawa.run' }}</p>
                </div>
                <div class="py-1">
                    <a href="{{ route('admin.profile.edit') }}" wire:navigate wire:current="bg-slate-50 text-brand-600" class="flex items-center px-4 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50 hover:text-brand-600">
                        <x-heroicon-o-user class="mr-3 h-4 w-4" />
                        Edit Profil
                    </a>
                </div>
                <div class="border-t border-slate-100 py-1">
                    <form method="POST" action="{{ route('admin.logout') }}" data-loading-title="Keluar dari dashboard" data-loading-message="Sesi admin sedang diakhiri...">
                        @csrf
                        <button type="submit" data-loading-label="Keluar..." class="flex w-full items-center px-4 py-2 text-left text-sm font-bold text-red-600 transition-colors hover:bg-red-50 hover:text-red-800">
                            <x-heroicon-o-arrow-right-on-rectangle class="mr-3 h-4 w-4" />
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
