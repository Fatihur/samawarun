@extends('layouts.admin')

@section('content')
    <div class="mb-8">
        <h1 class="font-display text-3xl font-bold uppercase italic text-slate-800">Edit Profil</h1>
        <p class="mt-1 text-sm text-slate-500">Perbarui informasi dasar dan kata sandi akun keamanan Anda.</p>
    </div>

    <div class="grid lg:grid-cols-2 gap-8 items-start">
        {{-- Personal Info Form --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100 pb-4 mb-6">Informasi Akun</h2>
            
            <form action="{{ route('admin.profile.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    {{-- Name --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 focus:border-brand-500 focus:ring-brand-500 transition-colors" required />
                        @error('name')
                            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Alamat Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 focus:border-brand-500 focus:ring-brand-500 transition-colors" required />
                        @error('email')
                            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-8">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100 pb-4 mb-6">Keamanan</h2>
                    
                    <div class="space-y-4">
                        {{-- New Password --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Kata Sandi Baru <span class="font-normal text-slate-400 text-xs ml-1">(Kosongkan jika tidak ingin diubah)</span></label>
                            <input type="password" name="password" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 focus:border-brand-500 focus:ring-brand-500 transition-colors" />
                            @error('password')
                                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Confirm Password --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Konfirmasi Kata Sandi Baru</label>
                            <input type="password" name="password_confirmation" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 focus:border-brand-500 focus:ring-brand-500 transition-colors" />
                        </div>
                    </div>
                </div>

                <div class="mt-8 rounded-xl bg-slate-50 p-4 border border-slate-100 flex items-center justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-brand-700 active:scale-95">
                        <x-heroicon-s-check-circle class="h-5 w-5" />
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
