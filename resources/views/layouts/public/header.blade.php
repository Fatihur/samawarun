        <header x-data="{ mobileMenuOpen: false }" class="sticky top-0 z-50 w-full border-b border-white/10 bg-background-dark/80 backdrop-blur-md shadow-sm transition-all duration-300">
            <div class="mx-auto flex w-full max-w-6xl items-center justify-between px-4 py-3 md:py-4">
                {{-- Brand --}}
                <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary text-background-dark shadow-[0_0_15px_rgba(48,232,122,0.5)] transition-transform group-hover:rotate-6 group-hover:scale-110">
                        <x-heroicon-o-bolt class="h-6 w-6" />
                    </div>
                    <span class="font-display text-2xl font-bold tracking-widest text-white transition-colors group-hover:text-primary uppercase italic">Samawa Run</span>
                </a>

                {{-- Desktop Navigation --}}
                <nav class="hidden md:flex items-center gap-8 font-medium">
                    <a class="text-sm transition-colors hover:text-primary {{ request()->routeIs('home') ? 'text-primary font-bold' : 'text-gray-300' }}" href="{{ route('home') }}">Home</a>
                    <a class="text-sm transition-colors hover:text-primary {{ request()->routeIs('events.*') ? 'text-primary font-bold' : 'text-gray-300' }}" href="{{ route('events.index') }}">Events</a>
                </nav>

                {{-- Action / Auth --}}
                <div class="hidden md:flex items-center gap-4">
                    <a class="text-sm font-medium text-gray-300 hover:text-primary transition-colors" href="{{ route('admin.login') }}">Admin Panel</a>
                </div>

                {{-- Mobile Menu Button --}}
                <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="md:hidden flex h-10 w-10 items-center justify-center rounded-lg text-gray-300 hover:bg-white/10 transition-colors" aria-label="Toggle menu">
                    <x-heroicon-o-bars-3 x-show="!mobileMenuOpen" class="w-6 h-6" />
                    <x-heroicon-o-x-mark x-cloak x-show="mobileMenuOpen" class="w-6 h-6" />
                </button>
            </div>

            {{-- Mobile Navigation Dropdown --}}
            <div x-cloak x-show="mobileMenuOpen" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-2"
                 class="md:hidden border-t border-white/10 bg-secondary-dark px-4 py-4 shadow-xl flex flex-col gap-4">
                <a class="block rounded-lg px-4 py-3 text-base font-medium transition-colors hover:bg-white/5 {{ request()->routeIs('home') ? 'bg-primary/10 text-primary' : 'text-gray-300' }}" href="{{ route('home') }}">Home</a>
                <a class="block rounded-lg px-4 py-3 text-base font-medium transition-colors hover:bg-white/5 {{ request()->routeIs('events.*') ? 'bg-primary/10 text-primary' : 'text-gray-300' }}" href="{{ route('events.index') }}">Events</a>
                <hr class="border-white/10">
                <a class="block rounded-lg px-4 py-3 text-base font-medium text-gray-300 transition-colors hover:bg-white/5" href="{{ route('admin.login') }}">Admin Panel</a>
            </div>
        </header>
