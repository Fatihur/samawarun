@extends('layouts.public')

@section('content')
    @php
        $categoryPrices = $event->distanceCategories
            ->mapWithKeys(fn ($category) => [
                strtoupper($category->name) => (int) round((float) ($category->pivot?->price ?? $event->price ?? 0)),
            ])
            ->toArray();
        $categoryInfo = $categoryInfo ?? [];
    @endphp

    <section class="px-6 py-16 lg:px-40 bg-background-dark" x-data='registrationForm(@json($categoryPrices), @json(strtoupper((string) old("distance_category"))))'>
        <div class="mx-auto max-w-2xl">
            {{-- Back Link --}}
            <a href="{{ route('events.show', $event) }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-bold text-gray-400 hover:text-primary transition-colors mb-8">
                <x-heroicon-o-arrow-left class="h-4 w-4" />
                Kembali ke detail event
            </a>

            {{-- Header --}}
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white font-display uppercase italic">Pendaftaran</h1>
                <p class="mt-2 text-gray-400">{{ $event->name }}</p>
                @if($event->registration_deadline)
                    <p class="mt-2 text-sm text-amber-300">Deadline pendaftaran: {{ $event->registration_deadline->translatedFormat('d F Y, H:i') }}</p>
                @endif
            </div>

            {{-- Step Indicator --}}
            <div class="mb-10 flex items-center justify-between">
                <template x-for="(label, index) in steps" :key="index">
                    <div class="flex items-center" :class="index < steps.length - 1 ? 'flex-1' : ''">
                        <div class="flex flex-col items-center">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold transition-all duration-300"
                                 :class="currentStep > index ? 'bg-primary text-background-dark' : (currentStep === index ? 'bg-primary text-background-dark shadow-[0_0_15px_rgba(48,232,122,0.4)]' : 'bg-white/10 text-gray-500')">
                                <template x-if="currentStep > index">
                                    <x-heroicon-o-check class="h-5 w-5" />
                                </template>
                                <template x-if="currentStep <= index">
                                    <span x-text="index + 1"></span>
                                </template>
                            </div>
                            <span class="mt-2 text-[10px] font-bold uppercase tracking-wider hidden sm:block"
                                  :class="currentStep >= index ? 'text-primary' : 'text-gray-600'"
                                  x-text="label"></span>
                        </div>
                        <template x-if="index < steps.length - 1">
                            <div class="mx-3 h-px flex-1 transition-colors duration-300"
                                 :class="currentStep > index ? 'bg-primary' : 'bg-white/10'"></div>
                        </template>
                    </div>
                </template>
            </div>

            {{-- Error Messages --}}
            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-red-500/30 bg-red-500/10 px-5 py-4 text-sm text-red-400">
                    <p class="font-bold mb-2">Terdapat kesalahan:</p>
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Form --}}
            <form action="{{ route('registrations.store', $event) }}" method="POST" enctype="multipart/form-data" novalidate data-loading-title="Mengirim pendaftaran" data-loading-message="Data peserta dan berkas sedang dikirim, mohon tunggu...">
                @csrf

                {{-- Step 1: Detail Event --}}
                <div x-show="currentStep === 0" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="rounded-2xl border border-white/10 bg-secondary-dark p-6 sm:p-8">
                        <h2 class="text-xl font-bold text-white mb-1 font-display">Detail Perlombaan</h2>
                        <p class="text-sm text-gray-500 mb-6">Pilih kategori jarak dan ukuran jersey terlebih dahulu</p>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-300">Kategori Jarak <span class="text-red-400">*</span></label>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                @foreach ($event->distanceCategories as $category)
                                @php
                                    $categoryName = strtoupper($category->name);
                                    $info = $categoryInfo[$categoryName] ?? null;
                                    $isFull = $info['is_full'] ?? false;
                                    $remaining = $info['remaining'] ?? null;
                                    $quota = $info['quota'] ?? null;
                                    $registeredCount = $info['registered_count'] ?? 0;
                                @endphp
                                <label class="cursor-pointer {{ $isFull ? 'opacity-50' : '' }}">
                                    <input type="radio" name="distance_category" value="{{ $categoryName }}" class="peer hidden" 
                                        x-model="selectedDistanceCategory" 
                                        @checked(strtoupper((string) old('distance_category')) === $categoryName) 
                                        @disabled($isFull)
                                        required>
                                    <div class="relative flex flex-col items-center justify-center rounded-xl border border-white/10 bg-background-dark px-3 py-4 text-gray-400 transition-all peer-checked:border-primary peer-checked:bg-primary/10 peer-checked:text-primary peer-checked:shadow-[0_0_15px_rgba(48,232,122,0.15)] hover:bg-white/5 {{ $isFull ? 'cursor-not-allowed' : '' }}">
                                        @if($isFull)
                                            <span class="absolute -top-2 left-1/2 -translate-x-1/2 rounded-full bg-red-500/20 px-2 py-0.5 text-[10px] font-bold text-red-400">PENUH</span>
                                        @elseif($remaining !== null && $remaining <= 10)
                                            <span class="absolute -top-2 left-1/2 -translate-x-1/2 rounded-full bg-amber-500/20 px-2 py-0.5 text-[10px] font-bold text-amber-400">SISA {{ $remaining }}</span>
                                        @endif
                                        <span class="text-lg font-bold {{ $isFull ? 'line-through' : '' }}">{{ $categoryName }}</span>
                                         @if($quota !== null)
                                            @php
                                                $progress = $quota > 0 ? round(($registeredCount / $quota) * 100) : 0;
                                                $progressColor = $progress >= 90 ? 'bg-red-500' : ($progress >= 70 ? 'bg-amber-500' : 'bg-primary');
                                            @endphp
                                            <div class="mt-1 w-full px-2">
                                                <div class="text-[9px] text-gray-500 mb-0.5 text-center">
                                                    <span>Terisi {{ $progress }}%</span>
                                                </div>
                                                <div class="h-1 w-full rounded-full bg-gray-700/30">
                                                    <div class="h-1 rounded-full {{ $progressColor }}" style="width: {{ $progress }}%"></div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="mt-1 text-[10px] text-gray-600">{{ $registeredCount }} peserta</span>
                                        @endif
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-6 rounded-2xl border border-white/10 bg-background-dark/40 p-5 sm:p-6">
                            <div class="mb-4">
                                <h3 class="text-base font-bold text-white">Ukuran Jersey</h3>
                                <p class="mt-1 text-xs leading-relaxed text-gray-400">Pilih ukuran jersey secara terpisah setelah menentukan kategori jarak.</p>
                            </div>

                            <div class="grid grid-cols-4 gap-2 sm:grid-cols-7">
                                @foreach (['2XS', 'XS', 'S', 'M', 'L', 'XL', 'XXL'] as $size)
                                <label class="cursor-pointer">
                                    <input type="radio" name="jersey_size" value="{{ $size }}" class="peer hidden" @checked(old('jersey_size') === $size) required>
                                    <div class="flex items-center justify-center rounded-xl border border-white/10 bg-background-dark py-3 text-sm font-bold text-gray-400 transition-all peer-checked:border-primary peer-checked:bg-primary/10 peer-checked:text-primary hover:bg-white/5">
                                        {{ $size }}
                                    </div>
                                </label>
                                @endforeach
                            </div>

                            <div x-data="{ previewOpen: false }" class="mt-4 rounded-2xl border border-white/10 bg-white/5 p-4">
                                <div class="mb-3 flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-bold text-white">Panduan Ukuran Jersey</p>
                                        <p class="mt-1 text-xs leading-relaxed text-gray-400">Gunakan ukuran kaos yang biasa dipakai sebagai acuan. Jika berada di antara dua ukuran, disarankan pilih ukuran yang lebih besar untuk kenyamanan saat race.</p>
                                    </div>
                                    <div class="rounded-full bg-primary/10 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-primary">
                                        Size Chart
                                    </div>
                                </div>

                                <button type="button" @click="previewOpen = true" class="mb-4 block w-full overflow-hidden rounded-xl border border-white/10 bg-background-dark p-2 text-left transition hover:border-primary/50 focus:outline-none focus:ring-2 focus:ring-primary/60">
                                    <img src="{{ asset('chart-size.jpeg') }}" alt="Panduan ukuran jersey raglan unisex" class="h-auto w-full object-contain">
                                </button>

                                <div x-show="previewOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4" @click.self="previewOpen = false" @keydown.escape.window="previewOpen = false">
                                    <div class="relative w-full max-w-5xl">
                                        <button type="button" @click="previewOpen = false" class="absolute right-3 top-3 z-10 rounded-full bg-black/70 px-3 py-1 text-xs font-bold text-white transition hover:bg-black">
                                            Tutup
                                        </button>
                                        <img src="{{ asset('chart-size.jpeg') }}" alt="Preview panduan ukuran jersey raglan unisex" class="max-h-[90vh] w-full rounded-2xl object-contain shadow-2xl">
                                    </div>
                                </div>

                                <div class="overflow-hidden rounded-xl border border-white/10">
                                    <div class="grid grid-cols-4 bg-white/5 text-[11px] font-bold uppercase tracking-[0.16em] text-gray-400">
                                        <div class="px-3 py-2">Size</div>
                                        <div class="px-3 py-2">A / Dada</div>
                                        <div class="px-3 py-2">B / Panjang</div>
                                        <div class="px-3 py-2">Catatan</div>
                                    </div>
                                    @php
                                        $jerseySizeChart = [
                                            ['size' => '2XS', 'chest' => '42 cm', 'length' => '60 cm', 'note' => 'Raglan unisex'],
                                            ['size' => 'XS', 'chest' => '45 cm', 'length' => '63 cm', 'note' => 'Raglan unisex'],
                                            ['size' => 'S', 'chest' => '48 cm', 'length' => '66 cm', 'note' => 'Raglan unisex'],
                                            ['size' => 'M', 'chest' => '51 cm', 'length' => '69 cm', 'note' => 'Raglan unisex'],
                                            ['size' => 'L', 'chest' => '54 cm', 'length' => '72 cm', 'note' => 'Raglan unisex'],
                                            ['size' => 'XL', 'chest' => '57 cm', 'length' => '75 cm', 'note' => 'Raglan unisex'],
                                            ['size' => '2XL', 'chest' => '59 cm', 'length' => '78 cm', 'note' => 'Raglan unisex'],
                                        ];
                                    @endphp
                                    @foreach ($jerseySizeChart as $chart)
                                        <div class="grid grid-cols-4 border-t border-white/10 text-sm text-gray-300 first:border-t-0">
                                            <div class="px-3 py-2.5 font-bold text-white">{{ $chart['size'] }}</div>
                                            <div class="px-3 py-2.5">{{ $chart['chest'] }}</div>
                                            <div class="px-3 py-2.5">{{ $chart['length'] }}</div>
                                            <div class="px-3 py-2.5 text-xs sm:text-sm">{{ $chart['note'] }}</div>
                                        </div>
                                    @endforeach
                                </div>

                                <p class="mt-3 text-[11px] leading-relaxed text-gray-500">*A = chest, B = front length. Ukuran pada tabel mengikuti angka di gambar dan dapat memiliki toleransi pengukuran sekitar 1-1.5 cm.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Step 2: Data Diri --}}
                <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="rounded-2xl border border-white/10 bg-secondary-dark p-6 sm:p-8">
                        <h2 class="text-xl font-bold text-white mb-1 font-display">Data Diri</h2>
                        <p class="text-sm text-gray-500 mb-6">Lengkapi informasi data diri Anda</p>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-300">Nama Lengkap <span class="text-red-400">*</span></label>
                                <input name="name" value="{{ old('name') }}" class="w-full rounded-xl border border-white/10 bg-background-dark px-4 py-3 text-sm text-white placeholder-gray-500 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary transition-colors" placeholder="Nama lengkap sesuai KTP" required>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-300">Tanggal Lahir <span class="text-red-400">*</span></label>
                                <input name="birth_date" value="{{ old('birth_date') }}" type="date" class="w-full rounded-xl border border-white/10 bg-background-dark px-4 py-3 text-sm text-white focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary transition-colors" required>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-300">Jenis Kelamin <span class="text-red-400">*</span></label>
                                <select name="gender" class="w-full rounded-xl border border-white/10 bg-background-dark px-4 py-3 text-sm text-white focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary transition-colors" required>
                                    <option value="">Pilih jenis kelamin</option>
                                    <option value="male" @selected(old('gender') === 'male')>Laki-laki</option>
                                    <option value="female" @selected(old('gender') === 'female')>Perempuan</option>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-300">NIK <span class="text-red-400">*</span></label>
                                <input name="nik" value="{{ old('nik') }}" class="w-full rounded-xl border border-white/10 bg-background-dark px-4 py-3 text-sm text-white placeholder-gray-500 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary transition-colors" placeholder="Nomor Induk Kependudukan (16 digit)" required>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Step 3: Kontak --}}
                <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="rounded-2xl border border-white/10 bg-secondary-dark p-6 sm:p-8">
                        <h2 class="text-xl font-bold text-white mb-1 font-display">Informasi Kontak</h2>
                        <p class="text-sm text-gray-500 mb-6">Nomor HP & alamat yang bisa dihubungi</p>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-300">Nomor HP <span class="text-red-400">*</span></label>
                                <input name="phone" value="{{ old('phone') }}" class="w-full rounded-xl border border-white/10 bg-background-dark px-4 py-3 text-sm text-white placeholder-gray-500 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary transition-colors" placeholder="08xxxxxxxxxx" required>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-300">Email <span class="text-red-400">*</span></label>
                                <input name="email" value="{{ old('email') }}" type="email" class="w-full rounded-xl border border-white/10 bg-background-dark px-4 py-3 text-sm text-white placeholder-gray-500 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary transition-colors" placeholder="email@contoh.com" required>
                                <p class="mt-1.5 flex items-center gap-1.5 text-xs text-amber-400">
                                    <x-heroicon-o-exclamation-triangle class="h-3.5 w-3.5" />
                                    Pastikan email ditulis dengan benar. Informasi penting akan dikirim ke email ini.
                                </p>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-300">Alamat <span class="text-red-400">*</span></label>
                                <textarea name="address" rows="3" class="w-full rounded-xl border border-white/10 bg-background-dark px-4 py-3 text-sm text-white placeholder-gray-500 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary transition-colors" placeholder="Alamat lengkap" required>{{ old('address') }}</textarea>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-300">Status Kontak Darurat <span class="text-red-400">*</span></label>
                                <select name="emergency_contact_relationship" class="w-full rounded-xl border border-white/10 bg-background-dark px-4 py-3 text-sm text-white focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary transition-colors" required>
                                    <option value="">Pilih hubungan keluarga</option>
                                    <option value="father" @selected(old('emergency_contact_relationship') === 'father')>Ayah</option>
                                    <option value="mother" @selected(old('emergency_contact_relationship') === 'mother')>Ibu</option>
                                    <option value="husband" @selected(old('emergency_contact_relationship') === 'husband')>Suami</option>
                                    <option value="wife" @selected(old('emergency_contact_relationship') === 'wife')>Istri</option>
                                    <option value="child" @selected(old('emergency_contact_relationship') === 'child')>Anak</option>
                                    <option value="other_family" @selected(old('emergency_contact_relationship') === 'other_family')>Keluarga Lain</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-300">Nama Kontak Darurat <span class="text-red-400">*</span></label>
                                <input name="emergency_contact_name" value="{{ old('emergency_contact_name') }}" class="w-full rounded-xl border border-white/10 bg-background-dark px-4 py-3 text-sm text-white placeholder-gray-500 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary transition-colors" placeholder="Nama lengkap kontak darurat" required>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-300">Nomor Kontak Darurat <span class="text-red-400">*</span></label>
                                <input name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}" class="w-full rounded-xl border border-white/10 bg-background-dark px-4 py-3 text-sm text-white placeholder-gray-500 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary transition-colors" placeholder="08xxxxxxxxxx" required>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Final Step: Informasi Setelah Submit --}}
                <div x-show="currentStep === 2" class="mt-6 rounded-2xl border border-primary/20 bg-primary/5 p-5 text-sm text-gray-300">
                    <p class="font-bold text-white">Setelah kirim pendaftaran:</p>
                    <p class="mt-2 leading-relaxed">Kami akan meninjau data pendaftaran Anda terlebih dahulu. Jika disetujui, link pembayaran akan dikirim ke email. Setelah pembayaran direview dan disetujui admin, Anda akan menerima email berisi bukti pendaftaran, nomor BIB, dan QR code.</p>
                </div>

                {{-- Navigation Buttons --}}
                <div class="mt-8 flex items-center justify-between">
                    <button type="button" x-show="currentStep > 0" @click="prevStep()" class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-transparent px-6 py-3 text-sm font-bold text-white hover:bg-white/5 transition-all active:scale-95">
                        <x-heroicon-o-arrow-left class="h-4 w-4" />
                        Sebelumnya
                    </button>
                    <div x-show="currentStep === 0"></div>

                    <button type="button" x-show="currentStep < 2" @click="nextStep()" class="inline-flex items-center gap-2 rounded-xl bg-primary px-6 py-3 text-sm font-bold text-background-dark shadow-[0_0_20px_rgba(48,232,122,0.2)] transition-all hover:bg-primary-hover active:scale-95">
                        Selanjutnya
                        <x-heroicon-o-arrow-right class="h-4 w-4" />
                    </button>

                    <button type="submit" x-show="currentStep === 2" data-loading-label="Mengirim..." class="inline-flex items-center gap-2 rounded-xl bg-primary px-8 py-3 text-sm font-bold text-background-dark shadow-[0_0_20px_rgba(48,232,122,0.3)] transition-all hover:bg-primary-hover active:scale-95">
                        <x-heroicon-o-check-circle class="h-4 w-4" />
                        Kirim Pendaftaran
                    </button>
                </div>
            </form>
        </div>
    </section>

    <script>
        function registrationForm(categoryPrices, initialDistanceCategory = '') {
            return {
                currentStep: 0,
                steps: ['Lomba', 'Data Diri', 'Kontak'],
                selectedDistanceCategory: initialDistanceCategory,
                categoryPrices,
                stepErrors: [],
                get selectedCategoryPriceLabel() {
                    if (!this.selectedDistanceCategory || !this.categoryPrices[this.selectedDistanceCategory]) {
                        return 'Pilih kategori jarak terlebih dahulu';
                    }

                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        maximumFractionDigits: 0,
                    }).format(this.categoryPrices[this.selectedDistanceCategory]);
                },
                validateStep0() {
                    this.stepErrors = [];

                    if (!this.selectedDistanceCategory) {
                        this.stepErrors.push('Pilih kategori jarak terlebih dahulu');
                    }

                    const jerseySize = document.querySelector('input[name="jersey_size"]:checked');
                    if (!jerseySize) {
                        this.stepErrors.push('Pilih ukuran jersey terlebih dahulu');
                    }

                    return this.stepErrors.length === 0;
                },
                validateStep1() {
                    this.stepErrors = [];

                    const name = document.querySelector('input[name="name"]').value.trim();
                    if (!name) {
                        this.stepErrors.push('Nama lengkap wajib diisi');
                    }

                    const birthDate = document.querySelector('input[name="birth_date"]').value;
                    if (!birthDate) {
                        this.stepErrors.push('Tanggal lahir wajib diisi');
                    }

                    const gender = document.querySelector('select[name="gender"]').value;
                    if (!gender) {
                        this.stepErrors.push('Jenis kelamin wajib dipilih');
                    }

                    const nik = document.querySelector('input[name="nik"]').value.trim();
                    if (!nik) {
                        this.stepErrors.push('NIK wajib diisi');
                    } else if (nik.length < 16) {
                        this.stepErrors.push('NIK minimal 16 digit');
                    }

                    return this.stepErrors.length === 0;
                },
                nextStep() {
                    if (this.currentStep === 0) {
                        if (!this.validateStep0()) {
                            this.showValidationError();
                            return;
                        }
                    } else if (this.currentStep === 1) {
                        if (!this.validateStep1()) {
                            this.showValidationError();
                            return;
                        }
                    }

                    if (this.currentStep < 2) {
                        this.currentStep++;
                        this.stepErrors = [];
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },
                showValidationError() {
                    const errorMessage = this.stepErrors.join('\n');

                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'fixed top-24 left-1/2 transform -translate-x-1/2 z-[60] max-w-md w-[calc(100%-2rem)]';
                    errorDiv.innerHTML = `
                        <div class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-4 text-red-100 shadow-lg backdrop-blur-sm">
                            <div class="flex items-start gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-6 w-6 shrink-0 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                <div class="flex-1 text-sm font-medium leading-relaxed">
                                    <p class="font-bold text-white mb-1">Mohon lengkapi data berikut:</p>
                                    <ul class="list-disc pl-4 space-y-1">
                                        ${this.stepErrors.map(err => `<li>${err}</li>`).join('')}
                                    </ul>
                                </div>
                                <button type="button" onclick="this.closest('.fixed').remove()" class="ml-auto text-red-400 hover:text-red-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(errorDiv);

                    setTimeout(() => {
                        errorDiv.remove();
                    }, 5000);
                },
                prevStep() {
                    if (this.currentStep > 0) {
                        this.currentStep--;
                        this.stepErrors = [];
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                }
            }
        }
    </script>
@endsection
