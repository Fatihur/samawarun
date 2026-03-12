<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full overflow-hidden m-0 p-0">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'Admin Samawa Run' }}</title>
        @livewireStyles
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/admin.css', 'resources/js/admin.js'])
        @endif
        {{-- TinyMCE is now self-hosted via npm, loaded through admin.js --}}
    </head>
    <body class="h-full m-0 p-0 overflow-hidden bg-slate-50 font-sans text-slate-800 antialiased selection:bg-brand-500 selection:text-white" x-data="{ sidebarOpen: false }">
        <div class="flex h-screen overflow-hidden bg-slate-50">
            
            {{-- Mobile Sidebar Overlay --}}
            <div x-cloak x-show="sidebarOpen" class="fixed inset-0 z-20 bg-slate-900/50 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false" x-transition.opacity></div>

            {{-- Sidebar --}}
            @include('layouts.admin.sidebar')

            {{-- Main Content Window --}}
            <div class="flex flex-1 flex-col overflow-hidden relative min-w-0 h-full">
                {{-- Top Navbar Mobile & Desktop --}}
                @include('layouts.admin.navbar')

                <main class="flex-1 overflow-y-auto overflow-x-hidden p-4 sm:p-6 lg:p-8 relative h-full">
                    <div class="mx-auto w-full max-w-7xl">
                        @if (session('success'))
                            <div class="mb-6 flex items-start sm:items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 sm:py-4 text-emerald-800 shadow-sm" role="alert">
                                <x-heroicon-s-check-circle class="h-6 w-6 shrink-0 text-emerald-500" />
                                <div class="text-sm font-medium">{{ session('success') }}</div>
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="mb-6 flex items-start sm:items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 sm:py-4 text-red-800 shadow-sm" role="alert">
                                <x-heroicon-s-exclamation-circle class="h-6 w-6 shrink-0 text-red-500" />
                                <div class="text-sm font-medium">{{ session('error') }}</div>
                            </div>
                        @endif
                        
                        @yield('content')
                    </div>
                </main>
            </div>
        </div>
        @livewireScriptConfig
    </body>
</html>
