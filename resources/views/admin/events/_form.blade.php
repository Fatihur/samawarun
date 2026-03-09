<div class="grid gap-5 md:grid-cols-2">
    <input type="hidden" name="event_code" value="{{ $event->event_code ?? '' }}">
    <div>
        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Nama Event</label>
        <input type="text" name="name" value="{{ old('name', $event->name ?? '') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors" required>
    </div>
    <div class="md:col-span-2">
        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Poster</label>
        <input type="file" name="poster" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-bold file:text-brand-600 focus:outline-none" accept="image/*">
    </div>
    <div class="md:col-span-2">
        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Deskripsi</label>
        <textarea name="description" rows="4" class="richtext w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors" required>{{ old('description', $event->description ?? '') }}</textarea>
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Tanggal Event</label>
        <input type="date" name="date" value="{{ old('date', optional($event->date ?? null)->format('Y-m-d')) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors" required>
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Jam Mulai Race</label>
        <input type="time" name="start_time" value="{{ old('start_time', optional($event->start_time ?? null)->format('H:i')) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors">
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Deadline Pendaftaran</label>
        <input type="datetime-local" name="registration_deadline" value="{{ old('registration_deadline', $event->registration_deadline_for_form ?? '') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors">
        <p class="mt-1 text-xs text-slate-500">Kosongkan jika pendaftaran selalu dibuka selama event aktif.</p>
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Lokasi</label>
        <input type="text" name="location" value="{{ old('location', $event->location ?? '') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors" required>
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Biaya</label>
        <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $event->price ?? '') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors" required>
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Kontak Panitia</label>
        <input type="text" name="contact" value="{{ old('contact', $event->contact ?? '') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors">
    </div>
    <div class="md:col-span-2">
        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Nomor Rekening</label>
        <input type="text" name="bank_account" value="{{ old('bank_account', $event->bank_account ?? '') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors">
    </div>
    <div class="md:col-span-2">
        <label class="inline-flex items-center gap-2.5 text-sm font-semibold text-slate-700 cursor-pointer">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $event->is_active ?? true)) class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
            Event aktif
        </label>
    </div>
    
    <div class="md:col-span-2 pt-4 border-t border-slate-200">
        <label class="mb-3 block text-sm font-semibold text-slate-700">Kategori Jarak yang Tersedia</label>
        @if(isset($distanceCategories) && $distanceCategories->count() > 0)
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                @php
                    $selectedCategories = old('distance_categories', $event->distanceCategories ? $event->distanceCategories->pluck('id')->toArray() : []);
                @endphp
                @foreach($distanceCategories as $category)
                    <label class="inline-flex items-center gap-2.5 text-sm md:text-base font-semibold text-slate-700 cursor-pointer p-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-white hover:border-brand-300 transition-all">
                        <input type="checkbox" name="distance_categories[]" value="{{ $category->id }}" @checked(in_array($category->id, $selectedCategories)) class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        {{ $category->name }}
                    </label>
                @endforeach
            </div>
            @error('distance_categories')
                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
            @enderror
        @else
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-center text-sm text-slate-500">
                Belum ada Kategori Jarak yang aktif. <a href="{{ route('admin.distance-categories.index') }}" class="text-brand-600 font-bold hover:underline">Kelola Kategori File</a>
            </div>
        @endif
    </div>
</div>
