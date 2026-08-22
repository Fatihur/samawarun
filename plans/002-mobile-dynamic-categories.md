# Plan 002: Mobile — ambil kategori dinamis dari server (hanya yang punya peserta)

> **Executor instructions**: Follow this plan step by step. Run every
> verification command and confirm the expected result before moving to the
> next step. If anything in the "STOP conditions" section occurs, stop and
> report — do not improvise. When done, update the status row for this plan
> in `plans/README.md` — unless a reviewer dispatched you and told you they
> maintain the index.
>
> **Drift check (run first)**: `git diff --stat 11026ea..HEAD -- race-tracker/lib/presentation/screens/results/results_screen.dart race-tracker/lib/presentation/screens/home/home_screen.dart race-tracker/lib/data/repositories/event_repository.dart race-tracker/lib/data/repositories/event_fetch_service.dart race-tracker/lib/data/datasources/local/app_database.dart race-tracker/lib/data/models/import_models.dart`
> If any in-scope file changed since this plan was written, compare the
> "Current state" excerpts against the live code before proceeding; on a
> mismatch, treat it as a STOP condition.

## Status

- **Priority**: P1
- **Effort**: M
- **Risk**: MED (mengubah alur import + UI tab; DB masih schemaVersion 1)
- **Depends on**: plans/001-backend-filter-categories-with-participants.md
- **Category**: tech-debt / feature
- **Planned at**: commit `11026ea`, 2026-08-22
- **Issue**: —

## Why this matters

Mobile saat ini hardcode kategori di dua tempat: `ResultsScreen` (`race-tracker/lib/presentation/screens/results/results_screen.dart:144-149` TabBar `5K/7K/10K` + `categories = ['5K','7K','10K']` di `build`) dan `HomeScreen` (`race-tracker/lib/presentation/screens/home/home_screen.dart:113` `eventIds = [1,2]`). Juga di repo layer (`event_repository.dart:38-46` dan `event_fetch_service.dart:57-65`) semua kategori dari server langsung di-insert ke `CategoryStartTimes` tanpa filter, sehingga tab kosong tetap muncul untuk kategori tanpa peserta (mis. `20K` tidak punya peserta tapi tetap ada tab). User ingin kategori diambil dari server **hanya** yang memiliki peserta — mis. jika hanya `3K Kids` punya peserta, hanya itu yang tampil. Plan ini menghapus hardcode, membuat kategori fully data-driven dari `participants.distanceCategory`, dan membuat UI tab menjadi dinamis. Ini prasyarat untuk Plan 003 (split gender per kategori) karena jumlah dan nama kategori tidak lagi statis.

## Current state

Files and roles:

- `race-tracker/lib/presentation/screens/results/results_screen.dart` — leaderboard. Provider `resultsByCategoryProvider` group by `distanceCategory` tapi UI tab hardcode 3. `DefaultTabController(length: 3)` + `TabBar(tabs: [5K,7K,10K])` + `categories = ['5K','7K','10K']`.
- `race-tracker/lib/presentation/screens/home/home_screen.dart:111-124` — `_syncFromWeb` pilih event via `SimpleDialog` dengan `eventIds = [1,2]` hardcode.
- `race-tracker/lib/data/repositories/event_repository.dart:16-62` — `importEvent` insert semua `data.categories` ke `CategoryStartTimes` dengan `startTime=''`.
- `race-tracker/lib/data/repositories/event_fetch_service.dart:13-82` — `EventFetchService` duplikat logic `syncEventToLocal` yang juga insert semua categories; `fetchEvent` GET `https://samawarun.site/admin/api/mobile-export?event_id=`; `_baseUrl` hardcode.
- `race-tracker/lib/data/datasources/local/app_database.dart:49-57` — table `CategoryStartTimes(eventId, categoryName, startTime)` + query `getStartTimesByEvent`, `getStartTimeForCategory`.
- `race-tracker/lib/data/models/import_models.dart:34-49` — `CategoryImport(name, price, quota)`, `EventDataImport(event, categories, participants)`.

Excerpts (HEAD `11026ea`):

`results_screen.dart:111-149`:
```dart
return DefaultTabController(
  length: 3,
  child: Scaffold(
    appBar: AppBar(
      bottom: const TabBar(tabs: [Tab(text: '5K'), Tab(text: '7K'), Tab(text: '10K')]),
    ),
    body: resultsAsync.when(data: (results) {
      final categories = ['5K', '7K', '10K'];
      return TabBarView(children: categories.map((category) { ... }).toList());
    }),
  ),
);
```

`home_screen.dart:111-123`:
```dart
void _syncFromWeb(BuildContext context, WidgetRef ref) async {
  final eventIds = [1, 2]; // JARun 2026 and other events
  final eventId = await showDialog<int>(... SimpleDialog with eventIds.map ...);
```

`event_repository.dart:38-46`:
```dart
for (final category in data.categories) {
  await _db.insertStartTime(CategoryStartTimesCompanion(
    eventId: Value(eventId),
    categoryName: Value(category.name),
    startTime: const Value(''),
  ));
}
```

`event_fetch_service.dart:57-65` sama persis (duplikasi).

Conventions:

- Riverpod `FutureProvider.family` — lihat `race_dashboard_screen.dart:15-18` `startTimesProvider`.
- Drift `Value(...)` untuk insert — lihat `app_database.dart:70-76`.
- Indonesian UI text — pertahankan bahasa Indonesia untuk label baru.

## Commands you will need

| Purpose | Command | Expected on success |
|---------|---------|---------------------|
| Analyze | `flutter analyze` | `No issues found!` atau 0 errors (warnings about unused import boleh) |
| Tests | `flutter test` | all pass |
| Format check | `dart format --set-exit-if-changed --output=none .` | exit 0 (atau tampilkan file changed) |
| Build (optional) | `flutter build apk --debug` | exit 0 — hanya jika perlu verifikasi kompilasi |

Jika `flutter` tidak ada di PATH (di env audit tidak terdeteksi), gunakan `dart analyze` via `fvm` atau cukup `flutter analyze` dari IDE; executor harus laporkan jika toolchain tidak tersedia dan treat sebagai STOP (lihat STOP conditions).

## Scope

**In scope** (only files you should modify):

- `race-tracker/lib/data/repositories/event_repository.dart` — filter insert kategori.
- `race-tracker/lib/data/repositories/event_fetch_service.dart` — filter insert + perbaiki duplikasi atau extract helper (optional, tapi boleh).
- `race-tracker/lib/data/datasources/local/app_database.dart` — tambah query `getDistinctCategoriesByEvent` atau `getCategoriesWithParticipants` jika diperlukan (atau cukup pakai existing `getStartTimesByEvent` + `getParticipantsByEvent` untuk derive).
- `race-tracker/lib/presentation/screens/results/results_screen.dart` — hapus hardcode 3 tab, buat dynamic.
- `race-tracker/lib/presentation/screens/race_dashboard/package_waktu_screen.dart` — pastikan waktu start UI juga pakai kategori dinamis (akan otomatis jika CategoryStartTimes sudah terfilter).
- `race-tracker/lib/presentation/screens/home/home_screen.dart` — hapus `eventIds=[1,2]` hardcode; ganti dengan input text atau fetch daftar event dari server (lihat STOP conditions untuk opsi).
- `race-tracker/lib/data/models/import_models.dart` — tidak perlu ubah, tapi boleh tambah helper `get categoriesWithParticipants` jika berguna.

**Out of scope** (do NOT touch):

- `race-tracker/lib/data/services/result_export_service.dart` — perubahan export ada di Plan 003.
- `race-tracker/lib/presentation/screens/results/results_screen.dart` gender split — di Plan 003, tapi di plan ini jangan ubah grouping gender (tetap group by category saja, hanya kategori yang dinamis).
- `app/Http/Controllers/...MobileExportController.php` — sudah di Plan 001.
- Schema drift `schemaVersion` — jangan bump version atau migrasi; cukup filter di application layer.

## Git workflow

- Branch: `advisor/002-mobile-dynamic-categories`
- Commit: `refactor(mobile): derive categories from participants instead of hardcode` + `fix(mobile): filter CategoryStartTimes to categories with participants`
- Pesan commit Indonesia/English campur seperti repo existing — lihat `git log --oneline` exemplar `feat: add mobile API endpoints for Race Tracker app` dan `fix: ...`.
- Do NOT push.

## Steps

### Step 1: Tambah query helper (jika belum ada) untuk kategori dinamis

Buka `race-tracker/lib/data/datasources/local/app_database.dart`.

Tambahkan method baru di `AppDatabase` (setelah `getStartTimeForCategory`):

```dart
Future<List<String>> getDistinctCategoriesByEvent(int eventId) async {
  final participants = await getParticipantsByEvent(eventId);
  final cats = participants.map((p) => p.distanceCategory).toSet().toList()..sort();
  return cats;
}
```

Alternatif jika ingin menghindari `Set` sorting custom: bisa derive dari `getStartTimesByEvent` — tapi karena setelah filter, `CategoryStartTimes` sudah hanya berisi kategori yang punya peserta, maka `getStartTimesByEvent` sebenarnya sudah cukup. Namun helper `getDistinctCategoriesByEvent` lebih explicit dan tidak bergantung pada Plan 001 yang sudah filter server; sarankan implement helper ini agar mobile tetap benar bahkan jika server belum di-deploy.

**Verify**: `flutter analyze` → no new errors di `app_database.dart`.

### Step 2: Filter insert kategori di repo layer

Edit `race-tracker/lib/data/repositories/event_repository.dart:38-46`:

Sebelum:
```dart
for (final category in data.categories) {
  await _db.insertStartTime(... category.name ...);
}
```

Sesudah (exact logic):

```dart
// Only store categories that actually have at least one participant.
// This implements "kategori yang di ambil adalah kategori yang memiliki peserta".
final participantCategoryNames = data.participants
    .map((p) => p.distanceCategory.toUpperCase())
    .toSet();

final categoriesToInsert = data.categories
    .where((c) => participantCategoryNames.contains(c.name.toUpperCase()))
    .toList();

// Fallback: if server already filtered (Plan 001), this is a no-op.
// If server not yet filtered, this guarantees mobile does not create empty CategoryStartTimes.
for (final category in categoriesToInsert) {
  await _db.insertStartTime(
    CategoryStartTimesCompanion(
      eventId: Value(eventId),
      categoryName: Value(category.name),
      startTime: const Value(''),
    ),
  );
}
```

Jika `categoriesToInsert` kosong (edge: peserta punya kategori yang tidak ada di `data.categories` karena case mismatch atau data legacy), fallback: gunakan `participantCategoryNames` langsung:

```dart
if (categoriesToInsert.isEmpty && participantCategoryNames.isNotEmpty) {
  for (final name in participantCategoryNames) {
    // Preserve original casing from participant data (first occurrence)
    final original = data.participants.firstWhere((p) => p.distanceCategory.toUpperCase() == name).distanceCategory;
    await _db.insertStartTime(CategoryStartTimesCompanion(
      eventId: Value(eventId),
      categoryName: Value(original),
      startTime: const Value(''),
    ));
  }
}
```

Lakukan perubahan yang sama di `race-tracker/lib/data/repositories/event_fetch_service.dart:57-65` (`syncEventToLocal`). Jika ingin DRY, extract private helper `_filterCategories(List<CategoryImport>, List<ParticipantImport>)` di masing-masing file atau buat util di `core/utils/category_utils.dart` — tapi jangan over-engineer; cukup duplikat logic dengan komentar `// Keep in sync with EventRepository`.

**Verify**: `flutter analyze` → no errors.

### Step 3: Hapus hardcode eventIds di HomeScreen

Edit `race-tracker/lib/presentation/screens/home/home_screen.dart:111-125`.

Ganti:
```dart
final eventIds = [1, 2];
final eventId = await showDialog<int>(... SimpleDialogOption for each id ...);
```

Menjadi salah satu opsi (pilih yang paling sederhana dan tidak menambah dependency backend list endpoint yang belum ada):

**Opsi A (recommended, minimal):** Input dialog text field untuk event_id.

```dart
final controller = TextEditingController();
final eventId = await showDialog<int>(
  context: context,
  builder: (ctx) => AlertDialog(
    title: const Text('Sinkron dari Web'),
    content: TextField(
      controller: controller,
      keyboardType: TextInputType.number,
      decoration: const InputDecoration(labelText: 'Masukkan Event ID', hintText: 'contoh: 1'),
    ),
    actions: [
      TextButton(onPressed: () => Navigator.of(ctx).pop(), child: const Text('Batal')),
      ElevatedButton(
        onPressed: () {
          final id = int.tryParse(controller.text.trim());
          if (id != null) Navigator.of(ctx).pop(id);
        },
        child: const Text('Sinkron'),
      ),
    ],
  ),
);
```

Jika user punya endpoint `GET /admin/api/mobile-events` untuk list events, tambahkan fallback: coba fetch list, jika gagal fallback ke input. Tapi jangan mengasumsikan endpoint list ada — jika `fetchActiveEvents()` masih stub return `[]`, jangan pakai.

Hapus komentar `// Hardcoded event IDs from samawarun.site`.

**Verify**: `flutter analyze` → no errors; jalankan `flutter test` jika ada widget test untuk HomeScreen (tidak ada saat ini, jadi cukup analyze).

### Step 4: Buat ResultsScreen kategori dinamis

Edit `race-tracker/lib/presentation/screens/results/results_screen.dart`.

Perubahan besar:

1. Tambah provider baru di atas file (setelah `resultsByCategoryProvider`):

```dart
final categoriesProvider = FutureProvider.family<List<String>, int>((ref, eventId) async {
  final db = ref.watch(databaseProvider);
  // Derive dari CategoryStartTimes jika ada, else dari participants distinct
  final startTimes = await db.getStartTimesByEvent(eventId);
  if (startTimes.isNotEmpty) {
    final cats = startTimes.map((e) => e.categoryName).toList()..sort();
    return cats;
  }
  final participants = await db.getParticipantsByEvent(eventId);
  final cats = participants.map((p) => p.distanceCategory).toSet().toList()..sort();
  return cats;
});
```

Atau gunakan `getDistinctCategoriesByEvent` yang dibuat di Step 1.

2. Ganti `ResultsScreen.build`:

- Hapus `length: 3` hardcode dan `const TabBar(tabs: [5K,7K,10K])`.
- Watch `categoriesProvider(event.id)` dan `resultsByCategoryProvider(event.id)`.
- Jika `categories.isEmpty` → tampilkan empty state `Belum ada kategori dengan peserta` (jangan crash).
- Jika `categories.isNotEmpty` → `DefaultTabController(length: categories.length, child: Scaffold( appBar: AppBar(bottom: TabBar(tabs: categories.map((c)=>Tab(text:c)).toList())), body: TabBarView(children: categories.map((category){ final catResults = results[category] ?? []; ... }).toList())))`
- Pastikan `resultsByCategoryProvider` tetap group by `distanceCategory` (tidak perlu ubah untuk plan ini).

Pastikan `results` sorting tetap `raceDurationSeconds` ascending seperti di `resultsByCategoryProvider:26-32`.

Handle loading/error: jika `categoriesAsync` loading, tampilkan `CircularProgressIndicator`; jika error, tampilkan `Text('Error: $e')`. Jika `resultsAsync` loading terpisah, handle nested.

Contoh skeleton (harus diadaptasi agar compile):

```dart
@override
Widget build(BuildContext context, WidgetRef ref) {
  final categoriesAsync = ref.watch(categoriesProvider(event.id));
  final resultsAsync = ref.watch(resultsByCategoryProvider(event.id));

  return categoriesAsync.when(
    loading: () => Scaffold(appBar: AppBar(title: Text('Hasil — ${event.name}')), body: Center(child: CircularProgressIndicator())),
    error: (e, _) => Scaffold(appBar: AppBar(title: Text('Hasil — ${event.name}')), body: Center(child: Text('Error: $e'))),
    data: (categories) {
      if (categories.isEmpty) {
        return Scaffold(
          appBar: AppBar(title: Text('Hasil — ${event.name}')),
          body: Center(child: Text('Belum ada kategori dengan peserta')),
        );
      }
      return DefaultTabController(
        length: categories.length,
        child: Scaffold(
          appBar: AppBar(
            title: Text('Hasil — ${event.name}'),
            bottom: TabBar(tabs: categories.map((c) => Tab(text: c)).toList()),
          ),
          body: resultsAsync.when(
            loading: () => Center(child: CircularProgressIndicator()),
            error: (e, _) => Center(child: Text('Error: $e')),
            data: (results) => TabBarView(
              children: categories.map((category) {
                final categoryResults = results[category] ?? [];
                // ... existing ListView.builder logic ...
              }).toList(),
            ),
          ),
        ),
      );
    },
  );
}
```

Pindahkan `PopupMenuButton` export ke `AppBar.actions` di setiap branch Scaffold agar tetap ada.

Hapus `final categories = ['5K','7K','10K'];` di `resultsAsync.when data` block.

**Verify**: `flutter analyze` → 0 errors. `flutter test` → pass.

### Step 5: Verifikasi PackageWaktuScreen juga dinamis

`package_waktu_screen.dart` sudah dinamis (load dari `db.getStartTimesByEvent`), jadi setelah Step 2, screen ini otomatis hanya menampilkan kategori yang punya peserta. Tidak perlu ubah, tapi verifikasi dengan membuka screen tersebut setelah import event baru — hanya kategori dengan peserta yang muncul.

Jika ingin explicit, tambahkan empty state di `PackageWaktuScreen.build` ketika `_startTimes.isEmpty`: tampilkan `Center(child: Text('Belum ada kategori dengan peserta'))` daripada ListView kosong.

**Verify**: `flutter analyze` → clean.

## Test plan

New tests (tambahkan di `race-tracker/test/`):

- `test/category_filter_test.dart` — unit test untuk helper filter:
  - `filter categories returns only those with participants` — input categories `[5K,7K,10K,20K]`, participants `[5K,10K]` → result `[5K,10K]`.
  - `case-insensitive match` — categories `[5K]`, participants `['5k']` → match.
  - `fallback when categories empty but participants have category` — categories `[]`, participants `['3K Kids']` → should create `3K Kids`.
- Widget test `test/results_screen_categories_test.dart` — pump `ResultsScreen` dengan mock `AppDatabase` yang return `CategoryStartTimes` `['3K Kids','5K']` dan pastikan `TabBar` length 2 dan tabs text contains those.

Pattern to follow: `test/widget_test.dart` existing (ProviderScope + MaterialApp). Untuk mock DB, gunakan `ProviderScope(overrides: [databaseProvider.overrideWithValue(mockDb)])`.

Jika mock DB sulit, cukup unit test untuk helper filter + manual verify via `flutter analyze`.

## Done criteria

Machine-checkable. ALL must hold:

- [ ] `grep -rn "length: 3" race-tracker/lib/presentation/screens/results/results_screen.dart` returns 0 matches
- [ ] `grep -rn "'5K','7K','10K'" race-tracker/lib/` returns 0 matches (atau `grep -n "5K.*7K.*10K"` 0)
- [ ] `grep -rn "eventIds = \[1, 2\]" race-tracker/lib/presentation/screens/home/home_screen.dart` returns 0 matches
- [ ] `grep -rn "participantCategoryNames\|categoriesToInsert\|getDistinctCategoriesByEvent" race-tracker/lib/` returns >=1 match (filter logic ada)
- [ ] `flutter analyze` exits 0 (atau hanya warnings unrelated)
- [ ] `flutter test` exits 0
- [ ] `git status --porcelain` hanya shows files in scope (tidak ada file di luar scope yang berubah)
- [ ] `plans/README.md` row 002 updated to DONE

## STOP conditions

Stop and report back (do not improvise) if:

- `app_database.g.dart` regenerated dan `CategoryStartTimes` sudah punya kolom baru — drift schema.
- `EventDataImport` di `import_models.dart` tidak punya `categories`/`participants` (struktur JSON berubah dari server).
- `fetchEvent` endpoint `https://samawarun.site/admin/api/mobile-export` mengembalikan shape berbeda (mis. `data.event` null) — maka input dialog perlu handle null.
- Tidak ada toolchain `flutter`/`dart` — maka tidak bisa run `flutter analyze`; laporkan dan minta user jalankan manual.
- `HomeScreen` ternyata diharapkan tetap pakai hardcoded karena `fetchActiveEvents()` akan diimplementasi sebagai list endpoint — jika user ingin list endpoint, STOP dan tanyakan apakah harus implement `GET /admin/api/mobile-events` di backend (Plan 001 scope backend list).

## Maintenance notes

- Jika kategori baru bertipe `3K Kids` muncul, sorting `..sort()` akan urut lexicographically (`10K` < `3K` secara string). Pertimbangkan sorting natural (numerik prefix) di follow-up jika UX terganggu.
- Jika server belum deploy Plan 001, mobile tetap aman karena filter ganda (server + local). Setelah server deploy, local fallback tetap tidak merugikan.
- Reviewer harus cek: fallback `participantCategoryNames` → create `CategoryStartTimes` dengan casing original tidak duplikat dengan existing pivot name.
- Follow-up: jika event punya banyak kategori (mis. 8), `TabBar` akan scroll; tambahkan `isScrollable: true` di `TabBar` untuk mencegah overflow — plan ini sudah harus set `isScrollable: true` jika `categories.length > 3`.
