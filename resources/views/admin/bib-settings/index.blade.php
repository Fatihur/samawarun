@extends('layouts.admin')

@section('content')
    <h1 class="mb-2 font-display text-3xl font-bold uppercase italic text-slate-800">Pengaturan Nomor Dada</h1>
    <p class="mb-8 text-sm text-slate-500">Atur format penomoran dan desain template cetak nomor dada peserta.</p>

    {{-- Tab Navigation --}}
    <div class="mb-8 flex flex-wrap items-center gap-2 rounded-2xl border border-slate-200 bg-white p-2 shadow-sm">
        <a href="{{ route('admin.bib-settings.index', ['tab' => 'format']) }}" class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold transition-colors {{ $activeTab === 'format' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
            <x-heroicon-o-hashtag class="h-4 w-4" />
            Format Nomor
        </a>
        <a href="{{ route('admin.bib-settings.index', ['tab' => 'template']) }}" class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold transition-colors {{ $activeTab === 'template' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
            <x-heroicon-o-paint-brush class="h-4 w-4" />
            Desain Template
        </a>
        <a href="{{ route('admin.bib-settings.index', ['tab' => 'kiosk']) }}" class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold transition-colors {{ $activeTab === 'kiosk' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
            <x-heroicon-o-computer-desktop class="h-4 w-4" />
            Kiosk Scan
        </a>
    </div>

    @if ($activeTab === 'format')
        @php
            $bibPreviewConfig = [
                'padding' => $setting->number_padding ?? 3,
                'prefixes' => $setting->category_prefixes ?? [],
                'starts' => $setting->category_start_numbers ?? [],
                'defaults' => $distanceCategories->mapWithKeys(fn ($category) => [
                    $category->id => [
                        'start' => 1,
                        'prefix' => substr($category->name, 0, 1),
                    ],
                ])->toArray(),
            ];
        @endphp

        <div
            x-data='bibFormatPreview(@json($bibPreviewConfig))'
            class="grid gap-8 lg:grid-cols-3"
        >
            {{-- Form --}}
            <form action="{{ route('admin.bib-settings.update') }}" method="POST" class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm" data-loading-title="Menyimpan format nomor" data-loading-message="Pengaturan format nomor dada sedang diperbarui...">
                @csrf
                @method('PUT')
                <input type="hidden" name="section" value="format">

                @if($distanceCategories->isEmpty())
                    <div class="mb-8 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                        Belum ada Kategori Jarak yang aktif. Silakan tambahkan dan aktifkan Kategori Jarak terlebih dahulu di menu <a href="{{ route('admin.distance-categories.index') }}" class="font-bold underline hover:text-amber-900">Kategori Jarak</a>.
                    </div>
                @else
                    @foreach($distanceCategories as $category)
                    <div class="mb-8 border-l-2 border-brand-500 pl-4">
                        <h3 class="mb-4 flex items-center gap-2 text-base font-bold text-slate-800 uppercase tracking-widest">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 text-xs font-black text-slate-700">{{ substr($category->name, 0, 3) }}</span>
                            {{ $category->name }}
                        </h3>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-slate-700">Prefix</label>
                                <input type="text" name="category_prefixes[{{ $category->id }}]" x-model="prefixes[{{ $category->id }}]" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors" placeholder="Contoh: {{ substr($category->name, 0, 1) }}">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-slate-700">Nomor Awal</label>
                                <input type="number" name="category_start_numbers[{{ $category->id }}]" x-model="starts[{{ $category->id }}]" min="1" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors">
                            </div>
                        </div>
                    </div>
                    @endforeach
                @endif

                {{-- Padding --}}
                <div class="mb-8 rounded-xl border border-slate-200 bg-slate-50 p-5">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Panjang Angka (Padding)</label>
                    <input type="number" name="number_padding" x-model="padding" value="{{ old('number_padding', $setting->number_padding) }}" min="1" max="6" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors">
                    <p class="mt-2 text-xs text-slate-400">Menentukan jumlah digit angka. Contoh: padding 3 → 001, 045, 120.</p>
                </div>

                <button type="submit" data-loading-label="Menyimpan..." class="rounded-xl bg-brand-600 px-6 py-3 text-sm font-bold text-white shadow-sm transition-all hover:bg-brand-700 active:scale-95" {{ $distanceCategories->isEmpty() ? 'disabled' : '' }}>
                    Simpan Format Nomor
                </button>
            </form>

            {{-- Live Preview Sidebar --}}
            <div class="lg:col-span-1">
                <div class="sticky top-28 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="mb-4 flex items-center gap-2 text-sm font-bold uppercase tracking-wider text-slate-500">
                        <x-heroicon-o-eye class="h-4 w-4" />
                        Preview Langsung
                    </h3>

                    <div class="space-y-4">
                        @forelse($distanceCategories as $category)
                        <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-4 text-center">
                            <p class="text-xs font-bold text-slate-600 mb-2">{{ $category->name }} — Peserta ke-1</p>
                            <p class="text-3xl font-black text-slate-800 font-mono tracking-widest" x-text="getPreview({{ $category->id }})"></p>
                        </div>
                        @empty
                        <div class="rounded-xl border border-dashed border-slate-200 p-4 text-center text-sm text-slate-400">
                            Tidak ada kategori untuk dipreview.
                        </div>
                        @endforelse
                    </div>

                    <div class="mt-5 rounded-xl bg-slate-50 p-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Logika Penomoran</h4>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Format: <strong class="text-slate-700">PREFIX</strong> + <strong class="text-slate-700">NOMOR</strong><br>
                            Nomor = Nomor Awal + jumlah peserta verified di kategori yang sama (per event).<br>
                            Contoh: jika prefix = "5", start = 1, padding = 3, peserta ke-1 = <span class="font-mono font-bold text-slate-700">5001</span>, peserta ke-2 = <span class="font-mono font-bold text-slate-700">5002</span>.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function bibFormatPreview(config) {
                return {
                    padding: config.padding || 3,
                    prefixes: config.prefixes || {},
                    starts: config.starts || {},
                    defaults: config.defaults || {},
                    
                    init() {
                        Object.entries(this.defaults).forEach(([categoryId, defaults]) => {
                            if (this.starts[categoryId] === undefined) {
                                this.starts[categoryId] = defaults.start;
                            }

                            if (this.prefixes[categoryId] === undefined) {
                                this.prefixes[categoryId] = defaults.prefix;
                            }
                        });
                    },

                    getPreview(categoryId) {
                        const prefix = this.prefixes[categoryId] || '';
                        const start = parseInt(this.starts[categoryId]) || 1;
                        let numStr = start.toString();
                        while(numStr.length < this.padding) {
                            numStr = '0' + numStr;
                        }
                        return prefix + numStr;
                    }
                }
            }
        </script>

    @elseif ($activeTab === 'template')
        {{-- Template Tab --}}
        <form action="{{ route('admin.bib-settings.update') }}" method="POST" enctype="multipart/form-data" class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm" data-loading-title="Menyimpan desain template" data-loading-message="Template nomor dada sedang diperbarui...">
            @csrf
            @method('PUT')
            <input type="hidden" name="section" value="template">

            <div class="mb-6 rounded-xl border border-indigo-100 bg-indigo-50 px-4 py-3 text-sm text-indigo-700">
                <strong>Info:</strong> Ukuran nomor dada menggunakan kertas A5 <strong>landscape</strong>. Perubahan desain akan langsung berlaku pada semua PDF yang dicetak selanjutnya.
            </div>

            {{-- Section: Informasi Dasar --}}
            <div class="mb-8">
                <h3 class="mb-4 text-base font-bold text-slate-800 flex items-center gap-2">
                    <span class="h-5 w-1 rounded-full bg-brand-500"></span>
                    Informasi Dasar
                </h3>
                <div class="grid gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Judul Template</label>
                        <input type="text" name="template_title" value="{{ old('template_title', $setting->template_title) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors">
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Teks Footer</label>
                        <textarea name="footer_text" rows="2" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors">{{ old('footer_text', $setting->footer_text) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Section: Background --}}
            <div class="mb-8">
                <h3 class="mb-4 text-base font-bold text-slate-800 flex items-center gap-2">
                    <span class="h-5 w-1 rounded-full bg-brand-500"></span>
                    Background Gambar
                </h3>

                {{-- Layout Guide Download --}}
                <div class="mb-4 flex items-center gap-4 rounded-xl border border-blue-100 bg-blue-50 p-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-100">
                        <x-heroicon-o-map class="h-5 w-5 text-blue-600" />
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-blue-900">Panduan Tata Letak</p>
                        <p class="text-xs text-blue-700">Download file tata letak terbaru untuk acuan posisi elemen desain background.</p>
                    </div>
                    <a href="{{ asset('tata-letak.jpg') }}" download="tata-letak.jpg" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-700 transition-all active:scale-95 shrink-0">
                        <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
                        Download
                    </a>
                </div>

                <div>
                    <input type="file" name="background_image" accept=".jpg,.jpeg,.png" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-bold file:text-brand-600 focus:outline-none">
                    <p class="mt-1.5 text-xs text-slate-400">JPG/PNG, maks 4MB. Gambar akan ditampilkan sebagai background penuh (100% opacity). Gunakan panduan tata letak untuk mengatur posisi desain.</p>
                    @if ($setting->background_image_path)
                        <div class="mt-3 flex items-center gap-3 rounded-lg border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                            <x-heroicon-o-check-circle class="h-5 w-5 shrink-0" />
                            <span>Background aktif: <strong>{{ basename($setting->background_image_path) }}</strong></span>
                        </div>
                        <label class="mt-2 inline-flex items-center gap-2 text-sm font-semibold text-red-600 cursor-pointer">
                            <input type="checkbox" name="remove_background_image" value="1" class="rounded border-slate-300 text-red-600">
                            Hapus background saat simpan
                        </label>
                    @endif
                </div>
            </div>

            {{-- Section: Skema Warna --}}
            <div class="mb-8">
                <h3 class="mb-4 text-base font-bold text-slate-800 flex items-center gap-2">
                    <span class="h-5 w-1 rounded-full bg-brand-500"></span>
                    Skema Warna
                </h3>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Warna Utama (Border & Pill)</label>
                        <select name="primary_color" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors">
                            @foreach (['#0f172a' => 'Slate', '#1d4ed8' => 'Blue', '#166534' => 'Green', '#7c2d12' => 'Orange', '#6d28d9' => 'Violet'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('primary_color', $setting->primary_color) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Warna Garis Aksen</label>
                        <select name="accent_color" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors">
                            @foreach (['#cbd5e1' => 'Slate Soft', '#bfdbfe' => 'Blue Soft', '#bbf7d0' => 'Green Soft', '#fed7aa' => 'Orange Soft', '#ddd6fe' => 'Violet Soft'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('accent_color', $setting->accent_color) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Warna Teks Utama</label>
                        <select name="text_color" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors">
                            @foreach (['#0f172a' => 'Slate', '#1e3a8a' => 'Blue', '#14532d' => 'Green', '#7c2d12' => 'Orange', '#4c1d95' => 'Violet'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('text_color', $setting->text_color) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Warna Teks Meta</label>
                        <select name="meta_text_color" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors">
                            @foreach (['#334155' => 'Slate', '#1e40af' => 'Blue', '#166534' => 'Green', '#9a3412' => 'Orange', '#6d28d9' => 'Violet'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('meta_text_color', $setting->meta_text_color) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Section: Ukuran Font --}}
            <div class="mb-8">
                <h3 class="mb-4 text-base font-bold text-slate-800 flex items-center gap-2">
                    <span class="h-5 w-1 rounded-full bg-brand-500"></span>
                    Ukuran Font
                </h3>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Ukuran Nomor Dada</label>
                        <select name="bib_font_size" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors">
                            @foreach ([84, 96, 108, 120, 132, 144] as $size)
                                <option value="{{ $size }}" @selected((int) old('bib_font_size', $setting->bib_font_size) === $size)>{{ $size }} px</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Ukuran Nama Peserta</label>
                        <select name="name_font_size" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors">
                            @foreach ([18, 22, 26, 30, 34] as $size)
                                <option value="{{ $size }}" @selected((int) old('name_font_size', $setting->name_font_size) === $size)>{{ $size }} px</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Section: Opsi Tampilan --}}
            <div class="mb-8">
                <h3 class="mb-4 text-base font-bold text-slate-800 flex items-center gap-2">
                    <span class="h-5 w-1 rounded-full bg-brand-500"></span>
                    Opsi Tampilan
                </h3>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 cursor-pointer hover:bg-slate-100 transition-colors">
                        <input type="checkbox" name="show_event_date" value="1" @checked(old('show_event_date', $setting->show_event_date)) class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        Tampilkan Tanggal Event
                    </label>
                    <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 cursor-pointer hover:bg-slate-100 transition-colors">
                        <input type="checkbox" name="show_event_location" value="1" @checked(old('show_event_location', $setting->show_event_location)) class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        Tampilkan Lokasi Event
                    </label>
                </div>
            </div>

            <button type="submit" data-loading-label="Menyimpan..." class="rounded-xl bg-brand-600 px-6 py-3 text-sm font-bold text-white shadow-sm transition-all hover:bg-brand-700 active:scale-95">
                Simpan Desain Template
            </button>
        </form>

    @elseif ($activeTab === 'kiosk')
        {{-- Kiosk Tab --}}
        <form action="{{ route('admin.bib-settings.update') }}" method="POST" enctype="multipart/form-data" class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm" data-loading-title="Menyimpan pengaturan kiosk" data-loading-message="Pengaturan kiosk sedang diperbarui...">
            @csrf
            @method('PUT')
            <input type="hidden" name="section" value="kiosk">

            <div class="mb-6 rounded-xl border border-amber-100 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                <strong>Info:</strong> Logo akan ditampilkan di halaman kiosk scan BIB. Logo header muncul di bagian atas, logo footer dengan teks sponsor di bagian bawah.
            </div>

            {{-- Section: Sponsor Text --}}
            <div class="mb-8">
                <h3 class="mb-4 text-base font-bold text-slate-800 flex items-center gap-2">
                    <span class="h-5 w-1 rounded-full bg-brand-500"></span>
                    Teks Sponsor
                </h3>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Teks di Atas Logo Footer</label>
                    <input type="text" name="kiosk_sponsor_text" value="{{ old('kiosk_sponsor_text', $setting->kiosk_sponsor_text ?: 'Sponsored by') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors" placeholder="Contoh: Sponsored by, Didukung oleh, Partner:">
                    <p class="mt-1.5 text-xs text-slate-400">Teks yang muncul di atas logo-logo footer.</p>
                </div>
            </div>

            {{-- Section: Header Logos --}}
            <div class="mb-8">
                <h3 class="mb-4 text-base font-bold text-slate-800 flex items-center gap-2">
                    <span class="h-5 w-1 rounded-full bg-brand-500"></span>
                    Logo Header
                </h3>

                {{-- Existing Header Logos --}}
                @if($setting->kiosk_header_logos && count($setting->kiosk_header_logos) > 0)
                <div class="mb-4 grid gap-3 sm:grid-cols-3">
                    @foreach($setting->kiosk_header_logos as $index => $logo)
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <img src="{{ Storage::url($logo) }}" alt="Header Logo {{ $index + 1 }}" class="h-16 w-full object-contain mb-2">
                        <label class="inline-flex items-center gap-2 text-xs font-semibold text-red-600 cursor-pointer">
                            <input type="checkbox" name="remove_header_logos[]" value="{{ $index }}" class="rounded border-slate-300 text-red-600">
                            Hapus logo ini
                        </label>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Upload New Header Logos --}}
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Tambah Logo Header</label>
                    <input type="file" name="header_logos[]" accept=".jpg,.jpeg,.png,.svg" multiple class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-bold file:text-brand-600 focus:outline-none">
                    <p class="mt-1.5 text-xs text-slate-400">Bisa pilih multiple file. JPG/PNG/SVG, maks 2MB per file. Logo akan ditampilkan di bagian atas halaman kiosk.</p>
                </div>
            </div>

            {{-- Section: Footer Logos --}}
            <div class="mb-8">
                <h3 class="mb-4 text-base font-bold text-slate-800 flex items-center gap-2">
                    <span class="h-5 w-1 rounded-full bg-brand-500"></span>
                    Logo Footer (Sponsor)
                </h3>

                {{-- Existing Footer Logos --}}
                @if($setting->kiosk_footer_logos && count($setting->kiosk_footer_logos) > 0)
                <div class="mb-4 grid gap-3 sm:grid-cols-4">
                    @foreach($setting->kiosk_footer_logos as $index => $logo)
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <img src="{{ Storage::url($logo) }}" alt="Footer Logo {{ $index + 1 }}" class="h-12 w-full object-contain mb-2">
                        <label class="inline-flex items-center gap-2 text-xs font-semibold text-red-600 cursor-pointer">
                            <input type="checkbox" name="remove_footer_logos[]" value="{{ $index }}" class="rounded border-slate-300 text-red-600">
                            Hapus logo ini
                        </label>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Upload New Footer Logos --}}
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Tambah Logo Footer</label>
                    <input type="file" name="footer_logos[]" accept=".jpg,.jpeg,.png,.svg" multiple class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-bold file:text-brand-600 focus:outline-none">
                    <p class="mt-1.5 text-xs text-slate-400">Bisa pilih multiple file. JPG/PNG/SVG, maks 2MB per file. Logo akan ditampilkan di bagian bawah halaman kiosk dengan teks sponsor di atasnya.</p>
                </div>
            </div>

            <button type="submit" data-loading-label="Menyimpan..." class="rounded-xl bg-brand-600 px-6 py-3 text-sm font-bold text-white shadow-sm transition-all hover:bg-brand-700 active:scale-95">
                Simpan Pengaturan Kiosk
            </button>
        </form>
    @endif
@endsection
