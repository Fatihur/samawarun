@extends('layouts.admin')

@section('content')
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold uppercase italic text-slate-800">Sertifikat Finisher</h1>
            <p class="mt-2 max-w-3xl text-sm text-slate-500">Upload gambar background sertifikat, lalu atur posisi text secara visual dengan drag & drop.</p>
        </div>
    </div>

    {{-- Load Google Fonts for Visual Editor --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alex+Brush&family=Allura&family=Caveat:wght@400;700&family=Cookie&family=Dancing+Script:wght@400;700&family=Great+Vibes&family=Inter:ital,wght@0,400;0,700;1,400;1,700&family=Kaushan+Script&family=Lato:ital,wght@0,400;0,700;1,400;1,700&family=Montserrat:ital,wght@0,400;0,700;1,400;1,700&family=Pacifico&family=Parisienne&family=Pinyon+Script&family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&family=Poppins:ital,wght@0,400;0,700;1,400;1,700&family=Roboto:ital,wght@0,400;0,700;1,400;1,700&family=Satisfy&family=Tangerine:wght@400;700&display=swap" rel="stylesheet">

    <div class="mb-8 flex flex-wrap items-center gap-2 rounded-2xl border border-slate-200 bg-white p-2 shadow-sm">
        <a href="{{ route('admin.certificates.index', ['tab' => 'template', 'event_id' => $selectedEvent?->id]) }}" class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold transition-colors {{ $activeTab === 'template' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
            <x-heroicon-o-paint-brush class="h-4 w-4" />
            Visual Editor
        </a>
        <a href="{{ route('admin.certificates.index', ['tab' => 'generate', 'event_id' => $selectedEvent?->id]) }}" class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold transition-colors {{ $activeTab === 'generate' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
            <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
            Generate PDF
        </a>
    </div>

    @if ($activeTab === 'template')
        {{-- Event Selector --}}
        <div class="mb-6 grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:grid-cols-2">
            <form method="GET" class="flex items-end gap-3 md:col-span-2">
                <input type="hidden" name="tab" value="template">
                <div class="flex-1">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Event</label>
                    <select name="event_id" onchange="this.form.submit()" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 transition-colors focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500">
                        <option value="">Pilih Event</option>
                        @foreach ($events as $event)
                            <option value="{{ $event->id }}" @selected((string) ($selectedEvent?->id) === (string) $event->id)>
                                {{ $event->name }} - {{ $event->date?->format('d M Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        @if ($selectedEvent)
            {{-- Upload Background --}}
            <form action="{{ route('admin.certificates.background.update') }}" method="POST" enctype="multipart/form-data" class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                @csrf
                <input type="hidden" name="event_id" value="{{ $selectedEvent->id }}">
                <h2 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-500">Upload Background Sertifikat</h2>

                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Nama Template</label>
                        <input type="text" name="name" value="{{ old('name', $template?->name ?? 'Sertifikat '.$selectedEvent->name) }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Gambar Background</label>
                        <input type="file" name="background_image" accept="image/png,image/jpeg" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-bold file:text-brand-600 focus:outline-none">
                        <p class="mt-1 text-xs text-slate-400">PNG/JPG, max 10MB. Gunakan gambar landscape.</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Orientasi</label>
                        <select name="orientation" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500">
                            <option value="landscape" @selected(old('orientation', $template?->orientation ?? 'landscape') === 'landscape')>Landscape</option>
                            <option value="portrait" @selected(old('orientation', $template?->orientation) === 'portrait')>Portrait</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-3">
                    <button type="submit" class="rounded-xl bg-brand-600 px-6 py-3 text-sm font-bold text-white shadow-sm transition-all hover:bg-brand-700 active:scale-95">
                        Upload Background
                    </button>
                    @if ($template?->background_image_path)
                        <span class="text-sm text-emerald-600 font-semibold">✓ Background sudah diupload</span>
                    @endif
                </div>

                @if ($errors->any())
                    <div class="mt-4 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif
            </form>

            {{-- Visual Editor --}}
            @if ($template?->background_image_path)
                <div id="editor-container" x-data="certificateEditor()" x-init="init()" :class="isFullscreen ? 'fixed inset-0 z-50 bg-slate-50 flex flex-col h-screen overflow-hidden' : 'rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden'">
                    {{-- Toolbar --}}
                    <div class="flex flex-wrap items-center gap-3 border-b border-slate-200 bg-slate-50 px-5 py-3">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 mr-auto">Visual Editor</h2>

                        <button @click="addElement()" class="inline-flex items-center gap-1.5 rounded-lg bg-slate-800 px-3 py-2 text-xs font-bold text-white transition-colors hover:bg-slate-700 active:scale-95" title="Tambah text baru">
                            <x-heroicon-o-plus class="h-3.5 w-3.5" />
                            Tambah Text
                        </button>

                        <button @click="savePositions()" :disabled="saving" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-600 px-4 py-2 text-xs font-bold text-white transition-colors hover:bg-brand-700 active:scale-95 disabled:opacity-50">
                            <x-heroicon-o-check class="h-3.5 w-3.5" />
                            <span x-text="saving ? 'Menyimpan...' : 'Simpan Posisi'"></span>
                        </button>

                        <button @click="toggleFullscreen()" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-600 transition-colors hover:bg-slate-50">
                            <x-heroicon-o-arrows-pointing-out class="h-3.5 w-3.5" x-show="!isFullscreen" />
                            <x-heroicon-o-arrows-pointing-in class="h-3.5 w-3.5" x-show="isFullscreen" style="display: none;" />
                            <span x-text="isFullscreen ? 'Exit Fullscreen' : 'Fullscreen'"></span>
                        </button>

                        <a href="{{ route('admin.certificates.preview', ['event_id' => $selectedEvent->id]) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-600 transition-colors hover:bg-slate-50">
                            <x-heroicon-o-eye class="h-3.5 w-3.5" />
                            Preview PDF
                        </a>
                    </div>

                    <div class="flex flex-col lg:flex-row flex-1 overflow-hidden">
                        {{-- Canvas --}}
                        <div class="flex-1 p-5 overflow-auto" :class="isFullscreen ? 'bg-slate-800' : 'bg-slate-100'">
                            <div
                                id="editor-canvas"
                                class="relative mx-auto bg-white shadow-xl border border-slate-300"
                                :style="{
                                    width: canvasWidth + 'px',
                                    height: canvasHeight + 'px',
                                    backgroundImage: 'url({{ asset('storage/'.$template->background_image_path) }})',
                                    backgroundSize: 'cover',
                                    backgroundPosition: 'center',
                                }"
                                @mousedown="onCanvasMouseDown($event)"
                                @mousemove="onCanvasMouseMove($event)"
                                @mouseup="onCanvasMouseUp($event)"
                                @mouseleave="onCanvasMouseUp($event)"
                            >
                                <template x-for="(el, index) in elements" :key="index">
                                    <div
                                        class="absolute cursor-move select-none transition-shadow"
                                        :class="selectedIndex === index ? 'ring-2 ring-brand-500 ring-offset-2 shadow-lg' : 'hover:ring-2 hover:ring-slate-300'"
                                        :style="{
                                            left: (el.x / 100 * canvasWidth - el.width / 100 * canvasWidth / 2) + 'px',
                                            top: (el.y / 100 * canvasHeight) + 'px',
                                            width: (el.width / 100 * canvasWidth) + 'px',
                                            fontSize: scaledFontSize(el.fontSize) + 'px',
                                            fontFamily: el.fontFamily || 'DejaVu Sans',
                                            fontWeight: el.fontWeight,
                                            fontStyle: el.fontStyle || 'normal',
                                            textDecoration: el.textDecoration || 'none',
                                            textTransform: el.textTransform || 'none',
                                            color: el.color,
                                            textAlign: el.textAlign,
                                            lineHeight: '1.3',
                                            padding: '4px 8px',
                                            borderRadius: '4px',
                                            backgroundColor: selectedIndex === index ? 'rgba(255,255,255,0.15)' : 'transparent',
                                        }"
                                        @mousedown.stop="startDrag(index, $event)"
                                        @click.stop="selectElement(index)"
                                    >
                                        <span x-text="el.label" class="pointer-events-none"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Properties Panel --}}
                        <div class="w-full lg:w-80 border-t lg:border-t-0 lg:border-l border-slate-200 bg-white overflow-y-auto" :class="isFullscreen ? 'max-h-none flex-1 pb-10' : 'max-h-[600px]'">
                            {{-- Element List --}}
                            <div class="border-b border-slate-200 p-4">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Text Elements</h3>
                                <div class="space-y-1.5">
                                    <template x-for="(el, index) in elements" :key="'list-'+index">
                                        <div
                                            class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm cursor-pointer transition-colors"
                                            :class="selectedIndex === index ? 'bg-brand-50 text-brand-700 font-bold' : 'text-slate-600 hover:bg-slate-50'"
                                            @click="selectElement(index)"
                                        >
                                            <x-heroicon-o-bars-3-bottom-left class="h-4 w-4 shrink-0" />
                                            <span x-text="el.label" class="truncate flex-1"></span>
                                            <button @click.stop="removeElement(index)" class="text-slate-400 hover:text-red-500 transition-colors" title="Hapus">
                                                <x-heroicon-o-trash class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Properties --}}
                            <div x-show="selectedIndex !== null && elements[selectedIndex]" class="p-4 space-y-4">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Properti</h3>

                                <div>
                                    <label class="text-xs font-semibold text-slate-600 mb-1 block">Placeholder</label>
                                    <select x-model="elements[selectedIndex].placeholder" @change="updateLabel()" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-brand-500">
                                        @foreach ($supportedPlaceholders as $key => $desc)
                                            <option value="{{ $key }}">{{ '${' . $key . '}' }} — {{ $desc }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="text-xs font-semibold text-slate-600 mb-1 block">Label</label>
                                    <input type="text" x-model="elements[selectedIndex].label" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-brand-500">
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="text-xs font-semibold text-slate-600 mb-1 block">X (%)</label>
                                        <input type="number" x-model.number="elements[selectedIndex].x" min="0" max="100" step="0.5" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-brand-500">
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-slate-600 mb-1 block">Y (%)</label>
                                        <input type="number" x-model.number="elements[selectedIndex].y" min="0" max="100" step="0.5" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-brand-500">
                                    </div>
                                </div>

                                <div>
                                    <label class="text-xs font-semibold text-slate-600 mb-1 block">Lebar (%)</label>
                                    <input type="range" x-model.number="elements[selectedIndex].width" min="10" max="100" step="1" class="w-full accent-brand-600">
                                    <span class="text-xs text-slate-400" x-text="elements[selectedIndex].width + '%'"></span>
                                </div>

                                <div>
                                    <label class="text-xs font-semibold text-slate-600 mb-1 block">Font Family</label>
                                    <select x-model="elements[selectedIndex].fontFamily" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-2 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-brand-500">
                                        <optgroup label="Sans Serif">
                                            <option value="Inter">Inter</option>
                                            <option value="Roboto">Roboto</option>
                                            <option value="Poppins">Poppins</option>
                                            <option value="Montserrat">Montserrat</option>
                                            <option value="Open Sans">Open Sans</option>
                                            <option value="Lato">Lato</option>
                                        </optgroup>
                                        <optgroup label="Serif">
                                            <option value="Playfair Display">Playfair Display</option>
                                            <option value="Merriweather">Merriweather</option>
                                        </optgroup>
                                        <optgroup label="Calligraphy / Script">
                                            <option value="Dancing Script">Dancing Script</option>
                                            <option value="Great Vibes">Great Vibes</option>
                                            <option value="Pacifico">Pacifico</option>
                                            <option value="Caveat">Caveat</option>
                                            <option value="Satisfy">Satisfy</option>
                                            <option value="Cookie">Cookie</option>
                                            <option value="Kaushan Script">Kaushan Script</option>
                                            <option value="Tangerine">Tangerine</option>
                                            <option value="Allura">Allura</option>
                                            <option value="Alex Brush">Alex Brush</option>
                                            <option value="Pinyon Script">Pinyon Script</option>
                                            <option value="Parisienne">Parisienne</option>
                                        </optgroup>
                                        <optgroup label="Standar System (Fallback)">
                                            <option value="DejaVu Sans">DejaVu Sans</option>
                                            <option value="DejaVu Serif">DejaVu Serif</option>
                                            <option value="DejaVu Sans Mono">DejaVu Sans Mono</option>
                                            <option value="Helvetica">Helvetica</option>
                                            <option value="Times">Times (Serif)</option>
                                            <option value="Courier">Courier (Mono)</option>
                                        </optgroup>
                                    </select>
                                </div>

                                <div>
                                    <label class="text-xs font-semibold text-slate-600 mb-1 block">Font Size (px)</label>
                                    <input type="range" x-model.number="elements[selectedIndex].fontSize" min="8" max="120" step="1" class="w-full accent-brand-600">
                                    <span class="text-xs text-slate-400" x-text="elements[selectedIndex].fontSize + 'px'"></span>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="text-xs font-semibold text-slate-600 mb-1 block">Font Weight</label>
                                        <select x-model="elements[selectedIndex].fontWeight" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-brand-500">
                                            <option value="normal">Normal</option>
                                            <option value="bold">Bold</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-slate-600 mb-1 block">Alignment</label>
                                        <select x-model="elements[selectedIndex].textAlign" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-brand-500">
                                            <option value="left">Kiri</option>
                                            <option value="center">Tengah</option>
                                            <option value="right">Kanan</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 gap-2">
                                    <label class="flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-2 cursor-pointer transition-colors" :class="elements[selectedIndex].fontStyle === 'italic' ? 'bg-brand-50 border-brand-300' : 'bg-slate-50 hover:bg-slate-100'">
                                        <input type="checkbox" class="sr-only" :checked="elements[selectedIndex].fontStyle === 'italic'" @change="elements[selectedIndex].fontStyle = $el.checked ? 'italic' : 'normal'">
                                        <span class="text-sm italic font-semibold" :class="elements[selectedIndex].fontStyle === 'italic' ? 'text-brand-700' : 'text-slate-500'">I</span>
                                        <span class="text-xs" :class="elements[selectedIndex].fontStyle === 'italic' ? 'text-brand-600' : 'text-slate-400'">Italic</span>
                                    </label>
                                    <label class="flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-2 cursor-pointer transition-colors" :class="elements[selectedIndex].textDecoration === 'underline' ? 'bg-brand-50 border-brand-300' : 'bg-slate-50 hover:bg-slate-100'">
                                        <input type="checkbox" class="sr-only" :checked="elements[selectedIndex].textDecoration === 'underline'" @change="elements[selectedIndex].textDecoration = $el.checked ? 'underline' : 'none'">
                                        <span class="text-sm underline font-semibold" :class="elements[selectedIndex].textDecoration === 'underline' ? 'text-brand-700' : 'text-slate-500'">U</span>
                                        <span class="text-xs" :class="elements[selectedIndex].textDecoration === 'underline' ? 'text-brand-600' : 'text-slate-400'">Garis</span>
                                    </label>
                                    <div>
                                        <select x-model="elements[selectedIndex].textTransform" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-2 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-brand-500">
                                            <option value="none">Normal</option>
                                            <option value="uppercase">HURUF BESAR</option>
                                            <option value="capitalize">Kapital</option>
                                            <option value="lowercase">huruf kecil</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="text-xs font-semibold text-slate-600 mb-1 block">Warna</label>
                                    <div class="flex items-center gap-2">
                                        <input type="color" x-model="elements[selectedIndex].color" class="h-10 w-14 rounded-lg border border-slate-200 cursor-pointer">
                                        <input type="text" x-model="elements[selectedIndex].color" class="flex-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-mono focus:outline-none focus:ring-1 focus:ring-brand-500">
                                    </div>
                                </div>
                            </div>

                            <div x-show="selectedIndex === null" class="p-6 text-center text-sm text-slate-400">
                                Klik text element di canvas atau daftar di atas untuk mengedit propertinya.
                            </div>
                        </div>
                    </div>

                    {{-- Save Status --}}
                    <div x-show="saveMessage" x-transition class="border-t border-slate-200 px-5 py-3 text-sm font-semibold" :class="saveSuccess ? 'text-emerald-600 bg-emerald-50' : 'text-red-600 bg-red-50'">
                        <span x-text="saveMessage"></span>
                    </div>
                </div>

                {{-- Placeholder Reference --}}
                <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 mb-4">Placeholder Tersedia</h2>
                    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($supportedPlaceholders as $placeholder => $description)
                            <div class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                                <p class="font-mono text-sm font-bold text-slate-800">{{ '${'.$placeholder.'}' }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $description }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center text-sm text-slate-500 shadow-sm">
                    <x-heroicon-o-photo class="mx-auto h-12 w-12 text-slate-300 mb-3" />
                    <p class="font-semibold text-slate-700 mb-1">Belum ada background sertifikat</p>
                    <p>Upload gambar background terlebih dahulu menggunakan form di atas untuk mulai mengedit template sertifikat.</p>
                </div>
            @endif
        @else
            <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-10 text-center text-sm text-slate-500 shadow-sm">
                Pilih event terlebih dahulu.
            </div>
        @endif
    @else
        {{-- Generate PDF Tab - keep same as before --}}
        <div class="mb-6 rounded-xl border border-indigo-100 bg-indigo-50 px-4 py-3 text-sm text-indigo-700">
            Generate tersedia untuk peserta <strong>verified</strong> yang sudah punya <strong>waktu finish</strong>. Bulk action akan mengunduh <strong>ZIP berisi file PDF per peserta</strong>.
        </div>

        <form method="GET" class="mb-6 grid gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:grid-cols-4">
            <input type="hidden" name="tab" value="generate">
            <select name="event_id" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 transition-colors focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500">
                <option value="">Pilih Event</option>
                @foreach ($events as $event)
                    <option value="{{ $event->id }}" @selected((string) request('event_id', $selectedEvent?->id) === (string) $event->id)>
                        {{ $event->name }} - {{ $event->date?->format('d M Y') }}
                    </option>
                @endforeach
            </select>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, atau BIB" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 transition-colors focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500">
            <button type="submit" class="rounded-xl bg-slate-800 px-5 py-3 text-sm font-bold text-white transition-colors hover:bg-slate-700 active:scale-95">Filter</button>
            <a href="{{ route('admin.certificates.index', ['tab' => 'generate']) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-600 transition-colors hover:bg-slate-50">Reset</a>
        </form>

        @if (! $selectedEvent)
            <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-10 text-center text-sm text-slate-500 shadow-sm">
                Pilih event terlebih dahulu untuk melihat peserta finisher dan generate sertifikat.
            </div>
        @elseif (! $template?->background_image_path)
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-6 py-5 text-sm text-amber-800 shadow-sm">
                Template sertifikat untuk event ini belum tersedia. Upload gambar background dulu di tab <strong>Visual Editor</strong>.
            </div>
        @else
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    Template aktif: <strong>{{ $template->name }}</strong>
                </div>
                <button form="bulk-certificate-form" type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-indigo-700 active:scale-95">
                    <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
                    Bulk Download PDF
                </button>
            </div>

            <form id="bulk-certificate-form" action="{{ route('admin.certificates.bulk') }}" method="POST" class="hidden">
                @csrf
            </form>

            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <table class="datatable w-full text-left text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-4" data-orderable="false">
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" id="select-all-certificates" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    <span>Pilih</span>
                                </label>
                            </th>
                            <th class="px-5 py-4">Peserta</th>
                            <th class="px-5 py-4">BIB</th>
                            <th class="px-5 py-4">Kategori</th>
                            <th class="px-5 py-4">Waktu Finish</th>
                            <th class="px-5 py-4">Durasi</th>
                            <th class="px-5 py-4" data-orderable="false">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($participants as $participant)
                            <tr class="transition-colors hover:bg-slate-50/50">
                                <td class="px-5 py-4">
                                    <input type="checkbox" name="participant_ids[]" value="{{ $participant->id }}" form="bulk-certificate-form" class="certificate-select h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-bold text-slate-800">{{ $participant->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $participant->email }}</p>
                                </td>
                                <td class="px-5 py-4 font-mono font-bold text-slate-700">{{ $participant->bib_number ?? '-' }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700">{{ $participant->distance_category }}</span>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">{{ $participant->race_finished_at?->format('d M Y H:i:s') ?? '-' }}</td>
                                <td class="px-5 py-4 font-mono font-bold text-slate-700">{{ $participant->formatted_race_duration ?? '-' }}</td>
                                <td class="px-5 py-4">
                                    <a href="{{ route('admin.participants.certificate', $participant) }}" class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-3 py-2 text-xs font-bold text-white transition-colors hover:bg-slate-700">
                                        <x-heroicon-o-document-arrow-down class="h-4 w-4" />
                                        Download PDF
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if ($participants->isEmpty())
                    <div class="border-t border-slate-100 px-5 py-10 text-center text-sm text-slate-500">
                        Belum ada finisher yang sesuai filter untuk event ini.
                    </div>
                @endif
            </div>

            <script>
                const selectAllCertificates = document.getElementById('select-all-certificates');
                const certificateChecks = Array.from(document.querySelectorAll('.certificate-select'));

                if (selectAllCertificates) {
                    selectAllCertificates.addEventListener('change', function () {
                        certificateChecks.forEach(function (checkbox) {
                            checkbox.checked = selectAllCertificates.checked;
                        });
                    });

                    certificateChecks.forEach(function (checkbox) {
                        checkbox.addEventListener('change', function () {
                            const allChecked = certificateChecks.length > 0 && certificateChecks.every(function (item) {
                                return item.checked;
                            });

                            selectAllCertificates.checked = allChecked;
                        });
                    });
                }
            </script>
        @endif
    @endif

    @if ($activeTab === 'template' && $template?->background_image_path)
    <script>
        function certificateEditor() {
            return {
                elements: @json($template->text_elements ?? $template->getDefaultTextElements()),
                selectedIndex: null,
                draggingIndex: null,
                dragOffsetX: 0,
                dragOffsetY: 0,
                saving: false,
                saveMessage: '',
                saveSuccess: false,
                isFullscreen: false,
                canvasWidth: 800,
                canvasHeight: 566,
                orientation: '{{ $template->orientation ?? "landscape" }}',

                init() {
                    this.updateCanvasSize();
                    window.addEventListener('resize', () => this.updateCanvasSize());
                    
                    document.addEventListener('fullscreenchange', () => {
                        this.isFullscreen = !!document.fullscreenElement;
                        setTimeout(() => this.updateCanvasSize(), 150);
                    });
                },

                toggleFullscreen() {
                    const el = document.getElementById('editor-container');
                    if (!document.fullscreenElement) {
                        el.requestFullscreen().catch(err => {
                            console.error(`Gagal masuk mode fullscreen: ${err.message}`);
                        });
                    } else {
                        document.exitFullscreen();
                    }
                },

                updateCanvasSize() {
                    const container = document.getElementById('editor-canvas')?.parentElement;
                    if (!container) return;

                    const maxWidth = Math.min(container.clientWidth - 40, 900);

                    if (this.orientation === 'landscape') {
                        this.canvasWidth = maxWidth;
                        this.canvasHeight = maxWidth * (210 / 297);
                    } else {
                        this.canvasHeight = Math.min(maxWidth * (297 / 210), 700);
                        this.canvasWidth = this.canvasHeight * (210 / 297);
                    }
                },

                scaledFontSize(originalFontSize) {
                    const baseWidth = this.orientation === 'landscape' ? 297 : 210;
                    const pxPerMm = this.canvasWidth / baseWidth;
                    return Math.max(8, Math.round(originalFontSize * pxPerMm * 0.35));
                },

                selectElement(index) {
                    this.selectedIndex = index;
                },

                startDrag(index, event) {
                    this.draggingIndex = index;
                    this.selectedIndex = index;

                    const canvas = document.getElementById('editor-canvas');
                    const rect = canvas.getBoundingClientRect();
                    const el = this.elements[index];

                    const elLeftPx = (el.x / 100) * this.canvasWidth - (el.width / 100 * this.canvasWidth / 2);
                    const elTopPx = (el.y / 100) * this.canvasHeight;

                    this.dragOffsetX = (event.clientX - rect.left) - elLeftPx;
                    this.dragOffsetY = (event.clientY - rect.top) - elTopPx;
                },

                onCanvasMouseDown(event) {
                    // Deselect when clicking canvas background
                    if (event.target.id === 'editor-canvas') {
                        this.selectedIndex = null;
                    }
                },

                onCanvasMouseMove(event) {
                    if (this.draggingIndex === null) return;

                    const canvas = document.getElementById('editor-canvas');
                    const rect = canvas.getBoundingClientRect();
                    const el = this.elements[this.draggingIndex];

                    const mouseX = event.clientX - rect.left;
                    const mouseY = event.clientY - rect.top;

                    const elLeftPx = mouseX - this.dragOffsetX;
                    const elTopPx = mouseY - this.dragOffsetY;

                    const widthPx = (el.width / 100) * this.canvasWidth;
                    const centerXPx = elLeftPx + widthPx / 2;

                    el.x = Math.max(0, Math.min(100, (centerXPx / this.canvasWidth) * 100));
                    el.y = Math.max(0, Math.min(100, (elTopPx / this.canvasHeight) * 100));

                    el.x = Math.round(el.x * 10) / 10;
                    el.y = Math.round(el.y * 10) / 10;
                },

                onCanvasMouseUp(event) {
                    this.draggingIndex = null;
                },

                addElement() {
                    this.elements.push({
                        placeholder: 'participant_name',
                        label: 'Text Baru',
                        x: 50,
                        y: 50,
                        fontSize: 16,
                        fontFamily: 'DejaVu Sans',
                        fontWeight: 'normal',
                        fontStyle: 'normal',
                        textDecoration: 'none',
                        textTransform: 'none',
                        color: '#000000',
                        textAlign: 'center',
                        width: 50,
                    });
                    this.selectedIndex = this.elements.length - 1;
                },

                removeElement(index) {
                    if (!confirm('Hapus text element ini?')) return;
                    this.elements.splice(index, 1);
                    if (this.selectedIndex === index) {
                        this.selectedIndex = null;
                    } else if (this.selectedIndex > index) {
                        this.selectedIndex--;
                    }
                },

                updateLabel() {
                    const el = this.elements[this.selectedIndex];
                    if (!el) return;
                    const labels = @json(collect($supportedPlaceholders)->map(fn($desc, $key) => $desc)->toArray());
                    el.label = labels[el.placeholder] || el.placeholder;
                },

                async savePositions() {
                    this.saving = true;
                    this.saveMessage = '';

                    try {
                        const response = await fetch('{{ route("admin.certificates.elements.save") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                event_id: {{ $selectedEvent->id }},
                                text_elements: this.elements,
                            }),
                        });

                        const data = await response.json();

                        if (response.ok) {
                            this.saveSuccess = true;
                            this.saveMessage = data.message || 'Posisi berhasil disimpan!';
                        } else {
                            this.saveSuccess = false;
                            const errors = data.errors ? Object.values(data.errors).flat().join(', ') : (data.message || 'Gagal menyimpan.');
                            this.saveMessage = errors;
                        }
                    } catch (e) {
                        this.saveSuccess = false;
                        this.saveMessage = 'Gagal menyimpan. Coba lagi.';
                    }

                    this.saving = false;
                    setTimeout(() => { this.saveMessage = ''; }, 4000);
                },
            };
        }
    </script>
    @endif
@endsection
