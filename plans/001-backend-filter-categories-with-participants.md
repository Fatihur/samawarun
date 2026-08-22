# Plan 001: Filter kategori di mobile-export agar hanya kategori yang memiliki peserta

> **Executor instructions**: Follow this plan step by step. Run every
> verification command and confirm the expected result before moving to the
> next step. If anything in the "STOP conditions" section occurs, stop and
> report — do not improvise. When done, update the status row for this plan
> in `plans/README.md` — unless a reviewer dispatched you and told you they
> maintain the index.
>
> **Drift check (run first)**: `git diff --stat 11026ea..HEAD -- app/Http/Controllers/Admin/Api/MobileExportController.php app/Models/Participant.php app/Models/Event.php`
> If any in-scope file changed since this plan was written, compare the
> "Current state" excerpts against the live code before proceeding; on a
> mismatch, treat it as a STOP condition.

## Status

- **Priority**: P1
- **Effort**: S
- **Risk**: LOW
- **Depends on**: none
- **Category**: correctness / bug
- **Planned at**: commit `11026ea`, 2026-08-22
- **Issue**: —

## Why this matters

Saat ini `MobileExportController` (`app/Http/Controllers/Admin/Api/MobileExportController.php:37-42`) mengembalikan **semua** `distanceCategories` yang ter-attach ke event, tanpa peduli apakah kategori itu punya peserta verified atau tidak. Contoh: event punya kategori `3K Kids`, `5K`, `7K`, `10K`, `20K` di pivot, tapi hanya `3K Kids` dan `5K` yang punya peserta — app tetap menerima `20K` yang kosong. Di flow baru, mobile harus hanya menampilkan kategori yang memiliki peserta (prasyarat untuk Plan 002). Jika filter tidak dilakukan di server, mobile harus menebak dan tetap menyimpan baris `CategoryStartTimes` kosong yang memicu tab kosong dan waktu start yang tidak relevan.

## Current state

Relevant files:

- `app/Http/Controllers/Admin/Api/MobileExportController.php` — single-action controller untuk `GET /admin/api/mobile-export?event_id=`. Respons berisi `event`, `categories`, `participants`.
- `app/Models/Event.php:118-123` — `distanceCategories(): BelongsToMany` dengan pivot `price,quota`.
- `app/Models/Participant.php:65` — `distance_category` string disimpan uppercased (contoh `5K`, `10K`).

Excerpt `MobileExportController.php:12-59` (HEAD `11026ea`):

```php
class MobileExportController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_id' => ['required', 'exists:events,id'],
        ]);

        $event = Event::query()
            ->with('distanceCategories')
            ->findOrFail($validated['event_id']);

        $participants = Participant::query()
            ->where('event_id', $event->id)
            ->where('status', Participant::STATUS_VERIFIED)
            ->whereNotNull('bib_number')
            ->get()
            ->map(fn (Participant $p) => [
                'id' => $p->id,
                'bib_number' => $p->bib_number,
                'name' => $p->name,
                'gender' => $p->gender,
                'distance_category' => $p->distance_category,
                'jersey_size' => $p->jersey_size,
            ]);

        $categories = $event->distanceCategories
            ->map(fn ($cat) => [
                'name' => $cat->name,
                'price' => $cat->pivot->price ?? $cat->price,
                'quota' => $cat->pivot->quota ?? null,
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'event' => [ ... ],
                'categories' => $categories,
                'participants' => $participants,
            ],
        ]);
    }
}
```

Repo conventions:

- Validasi dengan `$request->validate([...])` — lihat `MobileSyncController.php:15-21` sebagai exemplar.
- Gunakan `strtoupper()` untuk perbandingan kategori — lihat `Event.php:185` `where('distance_category', strtoupper($distanceCategory))`.
- Response shape harus tetap `{success, data: {event, categories, participants}}` — mobile `EventDataImport.fromJson` di `race-tracker/lib/data/models/import_models.dart:79-93` mengasumsikan shape ini.

## Commands you will need

| Purpose | Command | Expected on success |
|---------|---------|---------------------|
| Install | `composer install` | exit 0 |
| Typecheck (syntax) | `php -l app/Http/Controllers/Admin/Api/MobileExportController.php` | `No syntax errors` |
| Tests | `php artisan test --filter=MobileExport` | all pass (or 0 tests if suite belum ada) |
| Lint (Pint) | `vendor/bin/pint --test app/Http/Controllers/Admin/Api/MobileExportController.php` | exit 0 atau `style OK` |
| Routes | `php artisan route:list --path=mobile-export` | menampilkan `GET ... mobile-export` |

Jika `vendor/bin/pint` tidak ada, cukup jalankan `php -l` dan `php artisan test`.

## Scope

**In scope** (only files you should modify):

- `app/Http/Controllers/Admin/Api/MobileExportController.php` — logika filter kategori.
- `tests/Feature/MobileExportControllerTest.php` — buat baru untuk mengunci behavior.

**Out of scope** (do NOT touch, even though they look related):

- `app/Http/Controllers/Admin/Api/MobileSyncController.php` — endpoint sync terpisah, jangan ubah.
- `race-tracker/` — perubahan mobile ada di Plan 002/003, jangan campur.
- `app/Models/Event.php` / `Participant.php` — tidak perlu mengubah model, hanya query di controller.
- Migrasi / seed — tidak perlu.

## Git workflow

- Branch: `advisor/001-filter-categories-with-participants` (atau ikuti pola `git log --oneline` yang ada: `fix: ...` / `feat: ...`).
- Commit: 1 commit untuk controller + 1 commit untuk test, pesan `fix(api): filter mobile-export categories to only those with verified participants`.
- Do NOT push atau buka PR kecuali operator instruksikan.

## Steps

### Step 1: Buat test karakterisasi untuk behavior baru

Buat `tests/Feature/MobileExportControllerTest.php` (atau `tests/Feature/Api/MobileExportControllerTest.php` jika folder `Api` ada).

Test cases (gunakan `RefreshDatabase`, `Event::factory`, `DistanceCategory::factory`, `Participant::factory`):

1. `test_returns_only_categories_with_verified_participants` — Buat event dengan 3 kategori (`5K`, `7K`, `10K`). Attach semua ke event via `$event->distanceCategories()->attach([$cat5K->id => ['price'=>..., 'quota'=>...], ...])`. Buat 2 peserta verified di `5K` dan 1 di `10K`, **nol** di `7K`. Hit `GET /admin/api/mobile-export?event_id={id}`. Assert JSON `data.categories` hanya berisi `5K` dan `10K` (tidak ada `7K`), dan `data.participants` berisi 3 row.
2. `test_returns_empty_categories_when_no_participants` — Event dengan 2 kategori tapi 0 peserta verified. Assert `categories` adalah `[]` dan `participants` adalah `[]`.
3. `test_case_insensitive_match` — Buat peserta dengan `distance_category = '5k'` lower, kategori `5K` upper. Assert tetap match (menguji `strtoupper` branch).
4. `test_ignores_unverified_participants_for_category_filter` — Buat peserta `status=pending` di `7K` — kategori `7K` tetap tidak muncul karena query filter `STATUS_VERIFIED`.

Untuk data factory, lihat `database/factories/` — jika factory untuk `Event`/`DistanceCategory` belum ada, buat data manual dengan `Event::create([...])` dan `DistanceCategory::create([...])` seperti di `DistanceCategoryController.php:23`.

**Verify**: `php artisan test --filter=MobileExportControllerTest` → FAIL (karena controller belum di-fix) — ini expected merah. Lanjut ke Step 2.

### Step 2: Patch controller untuk filter kategori

Edit `app/Http/Controllers/Admin/Api/MobileExportController.php`:

Ganti blok:

```php
$categories = $event->distanceCategories
    ->map(fn ($cat) => [
        'name' => $cat->name,
        'price' => $cat->pivot->price ?? $cat->price,
        'quota' => $cat->pivot->quota ?? null,
    ]);
```

Menjadi (pseudocode exact — implement persis):

```php
$participants = Participant::query()
    ->where('event_id', $event->id)
    ->where('status', Participant::STATUS_VERIFIED)
    ->whereNotNull('bib_number')
    ->get()
    ->map(fn (Participant $p) => [
        'id' => $p->id,
        'bib_number' => $p->bib_number,
        'name' => $p->name,
        'gender' => $p->gender,
        'distance_category' => $p->distance_category,
        'jersey_size' => $p->jersey_size,
    ]);

// NEW: derive distinct categories that actually have verified participants
$participantCategories = $participants
    ->pluck('distance_category')
    ->map(fn ($v) => strtoupper((string) $v))
    ->filter()
    ->unique()
    ->values();

$categories = $event->distanceCategories
    ->filter(fn ($cat) => $participantCategories->contains(strtoupper((string) $cat->name)))
    ->map(fn ($cat) => [
        'name' => $cat->name,
        'price' => $cat->pivot->price ?? $cat->price,
        'quota' => $cat->pivot->quota ?? null,
    ])
    ->values();
```

Catatan:

- `strtoupper((string) ...)` untuk case-insensitive, konsisten dengan `Event.php:185`.
- `->values()` untuk re-index agar JSON jadi array, bukan object dengan key sparse.
- Jika `$participants` kosong, `$participantCategories` kosong → `$categories` kosong — ini behavior yang diinginkan (Plan 002 akan handle tab kosong).
- Jangan ubah query `$participants` — plan ini hanya filter `$categories`.

**Verify**:

- `php -l app/Http/Controllers/Admin/Api/MobileExportController.php` → `No syntax errors`
- `vendor/bin/pint --test app/Http/Controllers/Admin/Api/MobileExportController.php` atau `php artisan test --filter=MobileExportControllerTest` → sekarang 4 tests PASS.

### Step 3: Manual smoke via route list

Jalankan `php artisan route:list --path=mobile-export` dan pastikan route masih `GET admin/api/mobile-export  Admin\Api\MobileExportController`. Tidak ada perubahan route.

**Verify**: `php artisan route:list --path=mobile-export` → 1 baris, method GET.

## Test plan

- New tests: file `tests/Feature/MobileExportControllerTest.php` dengan 4 cases di Step 1.
- Pattern to follow: cari test existing di `tests/Feature/` — jika belum ada untuk API, pakai struktur `tests/Feature/ExampleTest.php` sebagai template (extends `TestCase`, uses `RefreshDatabase`).
- Verification: `php artisan test` → semua tests pass, termasuk 4 baru. Jika repo tidak punya test runner yang jalan, minimal `php -l` dan `pint --test` harus hijau.

## Done criteria

Machine-checkable. ALL must hold:

- [ ] `php -l app/Http/Controllers/Admin/Api/MobileExportController.php` exits 0
- [ ] `php artisan test --filter=MobileExportControllerTest` exits 0 dengan 4 tests pass
- [ ] `grep -n "participantCategories" app/Http/Controllers/Admin/Api/MobileExportController.php` returns >=1 match (filter code ada)
- [ ] `grep -n "->filter(fn (\$cat)" app/Http/Controllers/Admin/Api/MobileExportController.php` returns match (kategori di-filter)
- [ ] `php artisan route:list --path=mobile-export` masih menampilkan route GET
- [ ] No files outside in-scope list are modified (`git status --porcelain` hanya shows controller + test file)
- [ ] `plans/README.md` status row untuk 001 updated ke DONE

## STOP conditions

Stop and report back (do not improvise) if:

- Code di `MobileExportController.php:19-43` tidak match excerpt (codebase drift sejak `11026ea`).
- `distanceCategories` relationship di `Event.php` tidak lagi punya pivot `price/quota` (asumsi filter bergantung pivot).
- Participant factory tidak ada dan `Participant::create` gagal karena kolom required berbeda (mis. `distance_category` enum constraint).
- Test dengan `RefreshDatabase` error karena SQLite vs MySQL mismatch di `phpunit.xml` (maka STOP dan minta konfirmasi untuk pakai `DatabaseTransactions` atau mock).

## Maintenance notes

- Jika nanti ada kategori baru (mis. `3K Kids`, `21K`) yang punya peserta, endpoint otomatis mengeksposnya tanpa perubahan code — karena filter berbasis data, bukan enum.
- Jika workflow peserta berubah (mis. status baru selain `verified`), periksa `Participant::STATUS_VERIFIED` di filter — sinkron dengan Plan 002 yang juga filter di mobile.
- Reviewer harus fokus: apakah `strtoupper` cukup untuk case-insensitivity, atau perlu `trim()` juga jika data legacy punya spasi.
- Follow-up deferred: pagination untuk `participants` jika event > 5k peserta (tidak di scope plan ini).
