@extends('layouts.admin')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.participants.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-brand-600 transition-colors">
            <x-heroicon-o-arrow-left class="h-4 w-4" />
            Kembali ke daftar peserta
        </a>
    </div>

    <h1 class="mb-8 font-display text-3xl font-bold uppercase italic text-slate-800">Tambah Peserta</h1>

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.participants.store') }}" method="POST"
          class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm"
          data-loading-title="Menyimpan peserta"
          data-loading-message="Data peserta baru sedang disimpan..."
          x-data="participantForm()">
        @csrf

        <div class="grid gap-5 md:grid-cols-2">
            {{-- Event --}}
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">Event <span class="text-red-500">*</span></label>
                <select name="event_id" x-model="selectedEventId" @change="onEventChange()"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors" required>
                    <option value="">-- Pilih Event --</option>
                    @foreach ($events as $event)
                        <option value="{{ $event->id }}" @selected(old('event_id') == $event->id)>
                            {{ $event->name }} ({{ $event->date->format('d M Y') }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Nama Lengkap --}}
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors" required>
            </div>

            {{-- Tanggal Lahir --}}
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">Tanggal Lahir <span class="text-red-500">*</span></label>
                <input type="date" name="birth_date" value="{{ old('birth_date') }}"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors" required>
            </div>

            {{-- Jenis Kelamin --}}
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">Jenis Kelamin <span class="text-red-500">*</span></label>
                <select name="gender"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors" required>
                    <option value="">-- Pilih --</option>
                    <option value="male" @selected(old('gender') === 'male')>Laki-laki</option>
                    <option value="female" @selected(old('gender') === 'female')>Perempuan</option>
                </select>
            </div>

            {{-- NIK --}}
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">NIK <span class="text-red-500">*</span></label>
                <input type="text" name="nik" value="{{ old('nik') }}" maxlength="16"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors"
                       placeholder="16 digit NIK" required>
            </div>

            {{-- No. HP --}}
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">No. HP <span class="text-red-500">*</span></label>
                <input type="text" name="phone" value="{{ old('phone') }}"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors" required>
            </div>

            {{-- Email --}}
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors" required>
            </div>

            {{-- Alamat --}}
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">Alamat <span class="text-red-500">*</span></label>
                <textarea name="address" rows="3"
                          class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors" required>{{ old('address') }}</textarea>
            </div>

            {{-- Kategori Jarak --}}
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">Kategori Jarak <span class="text-red-500">*</span></label>
                <select name="distance_category" x-model="selectedCategory"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400"
                        :disabled="!selectedEventId" required>
                    <option value="">-- Pilih Event Terlebih Dahulu --</option>
                    <template x-for="cat in categories" :key="cat.name">
                        <option :value="cat.name" x-text="cat.label" :disabled="cat.is_full" :selected="cat.name === selectedCategory"></option>
                    </template>
                </select>
                <p x-show="selectedCategory && quotaInfo" x-text="quotaInfo" class="mt-1 text-xs text-slate-500"></p>
            </div>

            {{-- Ukuran Jersey --}}
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">Ukuran Jersey <span class="text-red-500">*</span></label>
                <select name="jersey_size"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors" required>
                    <option value="">-- Pilih --</option>
                    @foreach (['2XS', 'XS', 'S', 'M', 'L', 'XL', 'XXL'] as $size)
                        <option value="{{ $size }}" @selected(old('jersey_size') === $size)>{{ $size }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Kontak Darurat Section --}}
            <div class="md:col-span-2 pt-4 border-t border-slate-200">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Kontak Darurat</h3>
            </div>

            {{-- Nama Kontak Darurat --}}
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">Nama Kontak Darurat <span class="text-red-500">*</span></label>
                <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors" required>
            </div>

            {{-- Nomor Kontak Darurat --}}
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">Nomor Kontak Darurat <span class="text-red-500">*</span></label>
                <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors" required>
            </div>

            {{-- Hubungan --}}
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700">Hubungan <span class="text-red-500">*</span></label>
                <select name="emergency_contact_relationship"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors" required>
                    <option value="">-- Pilih --</option>
                    @foreach ([
                        'father' => 'Ayah',
                        'mother' => 'Ibu',
                        'husband' => 'Suami',
                        'wife' => 'Istri',
                        'child' => 'Anak',
                        'other_family' => 'Keluarga Lain',
                    ] as $value => $label)
                        <option value="{{ $value }}" @selected(old('emergency_contact_relationship') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-6">
            <button type="submit" data-loading-label="Menyimpan..."
                    class="rounded-xl bg-brand-600 px-6 py-3 text-sm font-bold text-white shadow-sm transition-all hover:bg-brand-700 active:scale-95">
                Simpan Peserta
            </button>
        </div>
    </form>

    <script>
        function participantForm() {
            const eventsData = @json($eventsCategories);

            return {
                selectedEventId: '{{ old("event_id", "") }}',
                selectedCategory: '{{ old("distance_category", "") }}',
                categories: [],

                init() {
                    if (this.selectedEventId) {
                        this.loadCategories();
                    }
                },

                onEventChange() {
                    this.selectedCategory = '';
                    this.loadCategories();
                },

                loadCategories() {
                    const cats = eventsData[this.selectedEventId] || [];
                    this.categories = cats.map(cat => ({
                        ...cat,
                        label: cat.name + (cat.is_full ? ' (PENUH)' :
                               cat.remaining !== null ? ` (Sisa: ${cat.remaining})` : ''),
                    }));
                },

                get quotaInfo() {
                    const cat = this.categories.find(c => c.name === this.selectedCategory);
                    if (!cat) return '';
                    if (cat.remaining === null) return 'Kuota: Tidak terbatas';
                    return `Sisa kuota: ${cat.remaining}`;
                }
            };
        }
    </script>
@endsection
