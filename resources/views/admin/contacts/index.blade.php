@extends('layouts.admin')

@section('content')
    <h1 class="mb-8 font-display text-3xl font-bold uppercase italic text-slate-800">Kelola Kontak & Header</h1>

    <form action="{{ route('admin.contacts.update') }}" method="POST" class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm" data-loading-title="Menyimpan kontak" data-loading-message="Informasi kontak dan header sedang diperbarui...">
        @csrf
        @method('PUT')

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">Nomor HP</label>
                <input type="text" name="phone" value="{{ old('phone', $contact->phone) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">WhatsApp</label>
                <input type="text" name="whatsapp" value="{{ old('whatsapp', $contact->whatsapp) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">Email</label>
                <input type="email" name="email" value="{{ old('email', $contact->email) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">Instagram</label>
                <input type="text" name="instagram" value="{{ old('instagram', $contact->instagram) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">Facebook</label>
                <input type="text" name="facebook" value="{{ old('facebook', $contact->facebook) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">TikTok</label>
                <input type="text" name="tiktok" value="{{ old('tiktok', $contact->tiktok) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors">
            </div>
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">Alamat</label>
                <textarea name="address" rows="4" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors">{{ old('address', $contact->address) }}</textarea>
            </div>
            <div class="md:col-span-2">
                <button type="submit" data-loading-label="Menyimpan..." class="rounded-xl bg-brand-600 px-6 py-3 text-sm font-bold text-white shadow-sm transition-all hover:bg-brand-700 active:scale-95">Simpan Kontak</button>
            </div>
        </div>
    </form>
@endsection
