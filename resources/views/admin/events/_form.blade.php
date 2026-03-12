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
        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Harga Dasar Event</label>
        <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $event->price ?? '') }}" class="w-full rounded-xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm text-slate-500 placeholder-slate-400 focus:outline-none" readonly>
        <p class="mt-1 text-xs text-slate-500">Terisi otomatis dari harga kategori termurah yang dipilih.</p>
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
            <div class="space-y-3">
                @php
                    $selectedCategories = old('distance_categories', $event->distanceCategories ? $event->distanceCategories->pluck('id')->toArray() : []);
                    $existingCategoryPrices = $event->distanceCategories
                        ? $event->distanceCategories->mapWithKeys(fn ($category) => [$category->id => $category->pivot?->price ?? $event->price])->toArray()
                        : [];
                @endphp
                @foreach($distanceCategories as $category)
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 transition-all hover:border-brand-300 hover:bg-white">
                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <label class="inline-flex items-center gap-2.5 text-sm md:text-base font-semibold text-slate-700 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="distance_categories[]"
                                    value="{{ $category->id }}"
                                    @checked(in_array($category->id, $selectedCategories))
                                    class="category-price-toggle h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                                    data-target="category-price-{{ $category->id }}"
                                >
                                <span>{{ $category->name }}</span>
                            </label>

                            <div class="w-full md:w-64">
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Harga Kategori</label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-sm text-slate-400">Rp</span>
                                    <input
                                        id="category-price-{{ $category->id }}"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        name="category_prices[{{ $category->id }}]"
                                        value="{{ old('category_prices.'.$category->id, $existingCategoryPrices[$category->id] ?? '') }}"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 pl-10 text-sm text-slate-800 placeholder-slate-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400"
                                        @disabled(! in_array($category->id, $selectedCategories))
                                        placeholder="Contoh: 150000"
                                    >
                                </div>
                                @error('category_prices.'.$category->id)
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @error('distance_categories')
                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
            @enderror
            @error('category_prices')
                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
            @enderror
        @else
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-center text-sm text-slate-500">
                Belum ada Kategori Jarak yang aktif. <a href="{{ route('admin.distance-categories.index') }}" class="text-brand-600 font-bold hover:underline">Kelola Kategori File</a>
            </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const categoryToggles = document.querySelectorAll('.category-price-toggle');
        const basePriceInput = document.querySelector('input[name="price"]');

        function syncCategoryPriceState(toggle) {
            const targetId = toggle.dataset.target;
            const input = document.getElementById(targetId);

            if (!input) {
                return;
            }

            input.disabled = !toggle.checked;

            if (!toggle.checked) {
                input.value = '';
            }
        }

        function syncBasePrice() {
            if (!basePriceInput) {
                return;
            }

            const enabledPrices = Array.from(document.querySelectorAll('.category-price-toggle:checked'))
                .map((toggle) => document.getElementById(toggle.dataset.target))
                .filter((input) => input && input.value !== '')
                .map((input) => Number(input.value))
                .filter((value) => !Number.isNaN(value));

            if (enabledPrices.length === 0) {
                basePriceInput.value = '';
                return;
            }

            basePriceInput.value = Math.min(...enabledPrices);
        }

        categoryToggles.forEach((toggle) => {
            syncCategoryPriceState(toggle);

            toggle.addEventListener('change', () => {
                syncCategoryPriceState(toggle);
                syncBasePrice();
            });

            const input = document.getElementById(toggle.dataset.target);

            if (input) {
                input.addEventListener('input', syncBasePrice);
            }
        });

        syncBasePrice();
    });
</script>
