<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Login Admin - Samawa Run</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="flex min-h-screen items-center justify-center bg-background-dark p-4 text-white">
        <div class="w-full max-w-md overflow-hidden rounded-2xl border border-white/10 bg-secondary-dark p-8 shadow-2xl">
            <div class="mb-6 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary text-background-dark">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold font-display uppercase italic">Login Admin</h1>
                    <p class="text-sm text-gray-400">Masuk ke dashboard Samawa Run</p>
                </div>
            </div>

            @if ($errors->any())
                <div class="mb-4 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-400">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('admin.login.store') }}" method="POST" class="space-y-5" data-loading-title="Masuk ke dashboard" data-loading-message="Sedang memverifikasi akun admin...">
                @csrf
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-300">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-xl border border-white/10 bg-background-dark px-4 py-3 text-sm text-white placeholder-gray-500 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" placeholder="admin@samawarun.com" required>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-300">Password</label>
                    <input type="password" name="password" class="w-full rounded-xl border border-white/10 bg-background-dark px-4 py-3 text-sm text-white placeholder-gray-500 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" placeholder="••••••••" required>
                </div>
                <label class="flex items-center gap-2 text-sm text-gray-400">
                    <input type="checkbox" name="remember" value="1" class="rounded border-white/20 bg-background-dark text-primary focus:ring-primary">
                    Ingat saya
                </label>
                <button type="submit" data-loading-label="Memproses..." class="w-full rounded-xl bg-primary py-3 text-sm font-bold text-background-dark shadow-[0_0_20px_rgba(48,232,122,0.2)] transition-all hover:bg-primary-hover active:scale-[0.98]">Masuk</button>
            </form>
        </div>
    </body>
</html>
