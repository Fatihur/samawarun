# Plan 003: Mobile — leaderboard per kategori dipisah Laki-laki / Perempuan

> **Executor instructions**: Follow this plan step by step. Run every
> verification command and confirm the expected result before moving to the
> next step. If anything in the "STOP conditions" section occurs, stop and
> report — do not improvise. When done, update the status row for this plan
> in `plans/README.md` — unless a reviewer dispatched you and told you they
> maintain the index.
>
> **Drift check (run first)**: `git diff --stat 11026ea..HEAD -- race-tracker/lib/presentation/screens/results/results_screen.dart race-tracker/lib/data/datasources/local/app_database.dart race-tracker/lib/data/services/result_export_service.dart race-tracker/lib/core/utils/bib_utils.dart`
> If any in-scope file changed since this plan was written, compare the
> "Current state" excerpts against the live code before proceeding; on a
> mismatch, treat it as a STOP condition.

## Status

- **Priority**: P1
- **Effort**: M
- **Risk**: MED (mengubah provider shape + UI nested tabs + export)
- **Depends on**: plans/002-mobile-dynamic-categories.md (kategori harus sudah dinamis)
- **Category**: feature
- **Planned at**: commit `11026ea`, 2026-08-22
- **Issue**: —

## Why this matters

Saat ini leaderboard (`race-tracker/lib/presentation/screens/results/results_screen.dart:9-35` `resultsByCategoryProvider`) hanya group by `distanceCategory` (`5K`, `7K`, `10K`) tanpa memisah gender. Semua peserta laki-laki dan perempuan campur dalam satu ranking per kategori. User ingin **per kategori dipisahkan juga untuk leaderboard laki dan perempuan** — mis. `5K - Laki-laki` dan `5K - Perempuan` ranking terpisah. Di data, `Participant.gender` sudah ada (`app_database.dart:29` `gender text nullable`, `import_models.dart:57` `gender?`), tapi tidak dipakai sama sekali di UI maupun export. Tanpa split, pemenang perempuan tenggelam di ranking campur dan export Excel/PDF tidak bisa dipakai untuk pengumuman juara per gender.

## Current state

Files:

- `race-tracker/lib/presentation/screens/results/results_screen.dart:9-35` — provider `resultsByCategoryProvider` return `Map<String, List<Map>>` dimana key = `distanceCategory`, value = sorted by `raceDurationSeconds`. Tidak ada grouping gender.
- `race-tracker/lib/data/datasources/local/app_database.dart:29` — `Participants.gender` nullable text (isi contoh: `L`, `P`, `male`, `female`, `Laki-laki`, `Perempuan` — tergantung data web, belum dinormalisasi).
- `race-tracker/lib/data/services/result_export_service.dart:34-41` — `_groupByCategory` hanya group by `category`; export Excel buat sheet per kategori (bukan per gender), PDF group section per kategori.
- `race-tracker/lib/presentation/screens/race_dashboard/race_dashboard_screen.dart:25-39` — `recordedProvider` juga tidak split gender, tapi ini ringkasan recent finish, bukan leaderboard resmi — boleh tetap campur atau optional split (out of scope primary).
- `race-tracker/lib/data/models/import_models.dart:52-62` — `ParticipantImport.gender` nullable String.

Excerpt `results_screen.dart:9-35`:

```dart
final resultsByCategoryProvider = FutureProvider.family<Map<String, List<Map<String, dynamic>>>, int>((ref, eventId) async {
  final db = ref.watch(databaseProvider);
  final results = await db.getResultsByEvent(eventId);
  final grouped = <String, List<Map<String, dynamic>>>{};
  for (final result in results) {
    final participant = await db.getParticipantByBib(eventId, result.bibNumber);
    if (participant != null) {
      final category = participant.distanceCategory;
      grouped.putIfAbsent(category, () => []);
      grouped[category]!.add({'result': result, 'participant': participant});
    }
  }
  for (final category in grouped.keys) {
    grouped[category]!.sort((a, b) => (a['result'] as RaceResult).raceDurationSeconds.compareTo((b['result'] as RaceResult).raceDurationSeconds));
  }
  return grouped;
});
```

Excerpt `result_export_service.dart:34-41`:

```dart
Map<String, List<ResultExportRow>> _groupByCategory(List<ResultExportRow> rows) {
  final grouped = <String, List<ResultExportRow>>{};
  for (final row in rows) {
    grouped.putIfAbsent(row.category, () => []).add(row);
  }
  ...
}
```

Conventions:

- `BibUtils.formatDuration` untuk display durasi — lihat `core/utils/bib_utils.dart:12-20`.
- Gender display di mobile belum ada konvensi; di web, `Participant.gender` mungkin isi `L`/`P` atau `male`/`female`. Harus dinormalisasi.
- Export menggunakan `ResultExportRow(category, durationSeconds, ...)` — belum ada field `gender`.

## Commands you will need

| Purpose | Command | Expected on success |
|---------|---------|---------------------|
| Analyze | `flutter analyze` | No issues found |
| Tests | `flutter test` | all pass |
| Format | `dart format --set-exit-if-changed --output=none .` | exit 0 |

## Scope

**In scope** (only files you should modify):

- `race-tracker/lib/core/utils/bib_utils.dart` — tambah helper `normalizeGender` / `genderLabel` (atau buat `lib/core/utils/gender_utils.dart` baru jika ingin terpisah).
- `race-tracker/lib/data/services/result_export_service.dart` — tambah field `gender` di `ResultExportRow`, update `_groupByCategory` atau buat `_groupByCategoryAndGender`, update `exportExcel` dan `buildPdfBytes` untuk buat section/sheet per gender.
- `race-tracker/lib/presentation/screens/results/results_screen.dart` — ubah provider untuk group by kategori + gender, ubah UI jadi nested tab atau segmented control L/P per kategori.
- `race-tracker/lib/presentation/providers/database_providers.dart` — tidak perlu ubah kecuali tambah provider baru (opsional).

**Out of scope** (do NOT touch):

- `race-tracker/lib/data/datasources/local/app_database.dart` — jangan ubah schema; cukup baca `gender` yang sudah ada. Jika butuh helper query baru, boleh tambah method read-only `getParticipantsByEvent` sudah ada, jadi tidak perlu migrasi.
- `race-tracker/lib/presentation/screens/race_dashboard/race_dashboard_screen.dart` — leaderboard gender split hanya di `ResultsScreen`; dashboard recent list boleh tetap campur (jika ingin split, buat plan terpisah).
- `app/Http/Controllers/Admin/Api/MobileExportController.php` — sudah di Plan 001; tidak perlu ubah untuk gender (peserta sudah include `gender`).
- `race-tracker/lib/presentation/screens/home/home_screen.dart` — sudah di Plan 002.

## Git workflow

- Branch: `advisor/003-gender-split-leaderboard`
- Commit messages: `feat(mobile): split leaderboard by gender per category` + `feat(mobile): export Excel/PDF split by gender`
- Do NOT push.

## Steps

### Step 1: Buat helper normalisasi gender

Edit `race-tracker/lib/core/utils/bib_utils.dart` (atau buat `lib/core/utils/gender_utils.dart` jika ingin isolasi).

Tambahkan:

```dart
class GenderUtils {
  GenderUtils._();

  /// Normalizes raw gender string from DB/web to 'L', 'P', or null.
  static String? normalize(String? raw) {
    if (raw == null) return null;
    final v = raw.trim().toLowerCase();
    if (v.isEmpty) return null;
    if (v == 'l' || v == 'male' || v == 'laki-laki' || v == 'laki' || v == 'm') return 'L';
    if (v == 'p' || v == 'female' || v == 'perempuan' || v == 'f') return 'P';
    return null; // unknown
  }

  static String label(String? normalized) {
    switch (normalized) {
      case 'L': return 'Laki-laki';
      case 'P': return 'Perempuan';
      default: return 'Tidak Diketahui';
    }
  }

  static String shortLabel(String? normalized) {
    switch (normalized) {
      case 'L': return 'L';
      case 'P': return 'P';
      default: return '-';
    }
  }
}
```

Pastikan import tidak circular. `BibUtils` tetap ada, cukup tambah class baru di file yang sama atau file baru. Jika buat file baru `gender_utils.dart`, import di `results_screen.dart` dan `result_export_service.dart`.

**Verify**: `flutter analyze` → no errors.

### Step 2: Update ResultExportRow dan group logic untuk gender

Edit `race-tracker/lib/data/services/result_export_service.dart`:

1. Tambah field `gender` ke `ResultExportRow`:

```dart
class ResultExportRow {
  final String bibNumber;
  final String name;
  final String category;
  final String? gender; // raw atau normalized? pilih raw, normalized di grouping
  final int durationSeconds;
  final String? finishedAt;
  const ResultExportRow({..., this.gender, ...});
}
```

2. Ubah `_groupByCategory` menjadi `_groupByCategoryAndGender` atau pertahankan `_groupByCategory` untuk backward compat dan tambah helper baru:

```dart
Map<String, Map<String, List<ResultExportRow>>> _groupByCategoryAndGender(List<ResultExportRow> rows) {
  final grouped = <String, Map<String, List<ResultExportRow>>>{};
  for (final row in rows) {
    final cat = row.category;
    final g = GenderUtils.normalize(row.gender) ?? 'Unknown';
    grouped.putIfAbsent(cat, () => {});
    grouped[cat]!.putIfAbsent(g, () => []).add(row);
  }
  // sort keys and sort each list by duration
  ...
  return grouped;
}
```

Tapi untuk Excel/PDF, kita ingin sheet/section per `category + gender`. Implement opsi:

- **Excel**: satu sheet per `kategori - gender`, nama sheet `${category} - Laki-laki` / `${category} - Perempuan`. Jika `gender` unknown, sheet `${category} - Campur`. Batasi nama sheet <=31 char (Excel limit) — truncate jika perlu.
- **PDF**: per kategori buat header `Kategori 5K`, lalu sub-header `Laki-laki` dan `Perempuan` masing-masing tabel. Jika kategori hanya punya satu gender, hanya tampilkan yang ada.

Update `exportExcel`:

```dart
final grouped = _groupByCategoryAndGender(rows);
if (grouped.isEmpty) { ... }
grouped.forEach((category, byGender) {
  byGender.forEach((genderKey, categoryRows) {
    final label = GenderUtils.label(genderKey == 'Unknown' ? null : genderKey);
    final sheetName = '${category} - $label'.replaceAll(RegExp(r'[^\w \-]'), '_');
    // Excel sheet name max 31
    final safeName = sheetName.length > 31 ? sheetName.substring(0, 31) : sheetName;
    final sheet = excel[safeName];
    // ... headers include maybe Gender column or not needed since sheet is per gender
    // But keep 6 columns as before, just filtered by gender.
  });
});
```

Update `buildPdfBytes` similarly: iterate `grouped.forEach((category, byGender) { widgets.add(header Kategori $category); byGender.forEach((g, rows){ widgets.add(subheader GenderUtils.label(...)); widgets.add(table) }) })`.

Jangan lupa tambahkan import `import '../../core/utils/bib_utils.dart'` atau `gender_utils.dart`.

**Verify**: `flutter analyze` → no errors.

### Step 3: Ubah provider ResultsScreen untuk gender split

Edit `race-tracker/lib/presentation/screens/results/results_screen.dart`.

Ganti `resultsByCategoryProvider` dari `Map<String, List>` menjadi `Map<String, Map<String, List>>` (kategori -> gender -> list).

Implement:

```dart
final resultsByCategoryAndGenderProvider = FutureProvider.family<Map<String, Map<String, List<Map<String, dynamic>>>>, int>((ref, eventId) async {
  final db = ref.watch(databaseProvider);
  final results = await db.getResultsByEvent(eventId);
  final grouped = <String, Map<String, List<Map<String, dynamic>>>{};

  for (final result in results) {
    final participant = await db.getParticipantByBib(eventId, result.bibNumber);
    if (participant != null) {
      final category = participant.distanceCategory;
      final genderKey = GenderUtils.normalize(participant.gender) ?? 'Unknown';
      grouped.putIfAbsent(category, () => {});
      grouped[category]!.putIfAbsent(genderKey, () => []);
      grouped[category]![genderKey]!.add({
        'result': result,
        'participant': participant,
      });
    }
  }

  for (final cat in grouped.keys) {
    for (final g in grouped[cat]!.keys) {
      grouped[cat]![g]!.sort((a, b) {
        final rA = a['result'] as RaceResult;
        final rB = b['result'] as RaceResult;
        return rA.raceDurationSeconds.compareTo(rB.raceDurationSeconds);
      });
    }
  }

  return grouped;
});
```

Pertahankan provider lama sebagai deprecated wrapper jika ingin, tapi lebih baik ganti langsung dan update semua consumer (hanya ResultsScreen dan RaceDashboardScreen.recordedProvider — yang kedua bisa tetap pakai yang lama). Untuk kompatibilitas, bisa keep `resultsByCategoryProvider` dan tambah yang baru `resultsByCategoryAndGenderProvider` — pilih keep both agar `race_dashboard_screen.dart:148` tidak broken.

**Verify**: `flutter analyze`.

### Step 4: Ubah UI ResultsScreen jadi nested gender tabs

Edit `ResultsScreen.build` (setelah Plan 002, sudah dinamis kategori). Sekarang di dalam tiap kategori, tampilkan gender toggle.

Opsi UI (pilih salah satu, recommended **SegmentedButton** di dalam tiap TabBarView page):

```dart
// Inside TabBarView children: categories.map((category) {
  final genderMap = results[category] ?? {}; // Map<String, List>
  final genders = ['L', 'P']; // fixed order; only show if data exists or always show with empty state
  return DefaultTabController(
    length: 2,
    child: Column(
      children: [
        TabBar(tabs: [Tab(text: 'Laki-laki'), Tab(text: 'Perempuan')]),
        Expanded(
          child: TabBarView(
            children: genders.map((g) {
              final list = genderMap[g] ?? [];
              if (list.isEmpty) {
                return Center(child: Text('Belum ada hasil $category - ${GenderUtils.label(g)}'));
              }
              return ListView.builder(
                padding: EdgeInsets.all(16),
                itemCount: list.length,
                itemBuilder: (context, index) {
                  final data = list[index];
                  // ... same Card as before but subtitle includes gender
                },
              );
            }).toList(),
          ),
        ),
      ],
    ),
  );
} // )
```

Alternatif jika tidak ingin nested `DefaultTabController`, gunakan `SegmentedButton` + `StatefulWidget` untuk pilih gender per category (simpan selected gender di `State`). Pilih yang paling simpel dan tidak breaking `DefaultTabController` outer.

Pastikan outer `TabBar` (kategori) tetap `isScrollable: true` jika kategori banyak.

Update `_exportData` untuk include gender:

Di `_exportData`, saat build `rows`, sertakan `gender: participant.gender`:

```dart
rows.add(ResultExportRow(
  bibNumber: participant.bibNumber,
  name: participant.name,
  category: participant.distanceCategory,
  gender: participant.gender,
  durationSeconds: result.raceDurationSeconds,
  finishedAt: _formatFinishTime(result.raceFinishedAt),
));
```

Dan `grouped` untuk cek kosong harus cek nested: `grouped.values.every((byGender) => byGender.values.every((list) => list.isEmpty))`.

**Verify**: `flutter analyze` → 0 errors. `flutter test` → pass. Manual visual check: kategori `5K` punya 3 L dan 2 P → tab L menampilkan 3 ranked, tab P menampilkan 2 ranked.

### Step 5: Update sorting dan empty states

- Jika `Unknown` gender exists (peserta tanpa gender), tampilkan tab ketiga `Lainnya` atau gabungkan ke empty state. Recommended: jika `Unknown` ada, tambahkan tab ketiga `Lainnya` dengan `GenderUtils.label(null)`. Implement dinamis: `final availableGenders = genderMap.keys.toList()..sort()` dan buat TabBar dari `availableGenders.map((g)=>Tab(text: GenderUtils.label(g=='Unknown'?null:g)))`. Jika `Unknown` banyak, ini transparan.
- Pastikan ranking `index+1` reset per gender (sudah karena list per gender).

**Verify**: `flutter analyze`.

## Test plan

- Unit `gender_utils_test.dart`:
  - `normalize L` → `'L'` untuk inputs `L`, `male`, `Laki-laki`, `laki`, `M`.
  - `normalize P` → `'P'` untuk `P`, `female`, `Perempuan`, `F`.
  - `normalize null/empty/unknown` → null.
  - `label` returns Indonesia strings.
- Widget `results_screen_gender_test.dart`:
  - Mock DB: participants `5K` L 2 orang, P 1 orang, `7K` L 1 orang. Pump `ResultsScreen` (dengan `ProviderScope` override database). Assert kategori TabBar length 2 (`5K`, `7K`), dan di dalam `5K` page, gender TabBar length 2 dan count ListTile per gender sesuai.
- Export test `result_export_service_test.dart`:
  - Buat rows mix `5K` L, `5K` P, `10K` L. Call `buildPdfBytes` dan `exportExcel` grouping — assert grouped keys contain `5K` -> `L`/`P`, `10K` -> `L`.

Pattern: lihat `test/widget_test.dart` untuk ProviderScope setup. Untuk mock DB, buat `FakeAppDatabase` atau override provider dengan instance yang sudah diisi via `drift` in-memory (gunakan `NativeDatabase.memory()` jika perlu).

## Done criteria

Machine-checkable. ALL must hold:

- [ ] `grep -rn "GenderUtils" race-tracker/lib/` returns >=2 matches (helper dipakai)
- [ ] `grep -rn "resultsByCategoryAndGenderProvider\|_groupByCategoryAndGender" race-tracker/lib/` returns >=1 match
- [ ] `grep -n "gender" race-tracker/lib/data/services/result_export_service.dart` returns >=1 (field di ResultExportRow)
- [ ] `grep -n "Tab(text: 'Laki" race-tracker/lib/presentation/screens/results/results_screen.dart` returns >=1 (gender tab ada)
- [ ] `grep -n "DefaultTabController" race-tracker/lib/presentation/screens/results/results_screen.dart` returns >=2 (nested controllers untuk kategori + gender) atau `SegmentedButton` found
- [ ] `flutter analyze` exits 0
- [ ] `flutter test` exits 0
- [ ] No files outside in-scope list modified (`git status`)
- [ ] `plans/README.md` row 003 updated to DONE

## STOP conditions

Stop and report back (do not improvise) if:

- `Participant.gender` di `app_database.dart` menyimpan format yang tidak terduga (mis. enum integer) — maka `normalize` tidak cocok; laporkan sample value.
- `ResultExportRow` sudah dipakai di banyak tempat dan menambah field `gender` breaking existing calls — jika `ResultExportRow` constructor required `gender` akan break callers di `ResultsScreen._exportData`; maka jadikan `gender` optional (`String?`) dengan default null.
- Hasil `getResultsByEvent` ternyata belum include `gender` karena peserta belum di-import dengan gender (semua null) — maka gender split akan selalu kosong; laporkan bahwa data training perlu include gender.
- Nested `DefaultTabController` menyebabkan `TabController` conflict (outer length vs inner length) — jika error `Controller's length does not match`, switch ke `SegmentedButton` approach daripada nested TabController.

## Maintenance notes

- Jika web menambah kategori gender `X` (non-binary) di masa depan, `GenderUtils.normalize` akan return null → masuk `Unknown`/`Lainnya`. Update helper jika perlu.
- Export Excel sheet name limit 31 char — jika kategori panjang seperti `3K Kids Ceria - Perempuan` >31, truncation sudah di Step 2.
- Reviewer fokus: apakah `normalize` cukup permissive untuk data legacy `gender` yang mungkin uppercase/lowercase mixed; dan apakah `Unknown` harus disembunyikan atau ditampilkan sebagai tab ketiga.
- Follow-up: sort natural kategori (mis. `5K` < `10K`) belum handle jika kategori non-numeric seperti `3K Kids`.
