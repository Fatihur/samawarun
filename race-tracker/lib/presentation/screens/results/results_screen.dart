import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/utils/bib_utils.dart';
import '../../../data/datasources/local/app_database.dart';
import '../../../data/services/result_export_service.dart';
import '../../providers/database_providers.dart';

String _normCat(String s) => s.trim().toUpperCase();

final resultsByCategoryProvider = FutureProvider.family<Map<String, List<Map<String, dynamic>>>, int>((ref, eventId) async {
  final db = ref.watch(databaseProvider);
  final results = await db.getResultsByEvent(eventId);
  final grouped = <String, List<Map<String, dynamic>>>{};

  for (final result in results) {
    final participant = await db.getParticipantByBib(eventId, result.bibNumber);
    if (participant != null) {
      final category = _normCat(participant.distanceCategory);
      grouped.putIfAbsent(category, () => []);
      grouped[category]!.add({
        'result': result,
        'participant': participant,
      });
    }
  }

  for (final category in grouped.keys) {
    grouped[category]!.sort((a, b) {
      final rA = a['result'] as RaceResult;
      final rB = b['result'] as RaceResult;
      return rA.raceDurationSeconds.compareTo(rB.raceDurationSeconds);
    });
  }

  return grouped;
});

final resultsByCategoryAndGenderProvider =
    FutureProvider.family<Map<String, Map<String, List<Map<String, dynamic>>>>, int>((ref, eventId) async {
  final db = ref.watch(databaseProvider);
  final results = await db.getResultsByEvent(eventId);
  final grouped = <String, Map<String, List<Map<String, dynamic>>>>{};

  for (final result in results) {
    final participant = await db.getParticipantByBib(eventId, result.bibNumber);
    if (participant != null) {
      final category = _normCat(participant.distanceCategory);
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

final categoriesProvider = FutureProvider.family<List<String>, int>((ref, eventId) async {
  final db = ref.watch(databaseProvider);
  final startTimes = await db.getStartTimesByEvent(eventId);
  if (startTimes.isNotEmpty) {
    // Normalize case-insensitively to handle '3Kkids' vs '3KKIDS'
    final cats = startTimes.map((e) => _normCat(e.categoryName)).toSet().toList()..sort();
    return cats;
  }
  final participants = await db.getParticipantsByEvent(eventId);
  final cats = participants.map((p) => _normCat(p.distanceCategory)).toSet().toList()..sort();
  return cats;
});

class ResultsScreen extends ConsumerWidget {
  final Event event;

  const ResultsScreen({super.key, required this.event});

  String? _formatFinishTime(String? iso) {
    if (iso == null || iso.isEmpty) return null;
    final dt = DateTime.tryParse(iso);
    if (dt == null) return iso;
    return '${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')}:${dt.second.toString().padLeft(2, '0')}';
  }

  Future<void> _exportData(BuildContext context, WidgetRef ref, String type) async {
    final grouped = ref.read(resultsByCategoryAndGenderProvider(event.id)).value;
    if (grouped == null || grouped.values.every((byGender) => byGender.values.every((list) => list.isEmpty))) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Belum ada data hasil untuk diexport')),
      );
      return;
    }

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => const AlertDialog(
        content: Row(
          children: [
            CircularProgressIndicator(),
            SizedBox(width: 16),
            Text('Membuat file...'),
          ],
        ),
      ),
    );

    final rows = <ResultExportRow>[];
    grouped.forEach((category, byGender) {
      byGender.forEach((genderKey, entries) {
        for (final data in entries) {
          final result = data['result'] as RaceResult;
          final participant = data['participant'] as Participant;
          rows.add(ResultExportRow(
            bibNumber: participant.bibNumber,
            name: participant.name,
            category: participant.distanceCategory,
            gender: participant.gender,
            durationSeconds: result.raceDurationSeconds,
            finishedAt: _formatFinishTime(result.raceFinishedAt),
          ));
        }
      });
    });

    try {
      final service = ResultExportService();
      if (type == 'excel') {
        await service.exportExcel(event, rows);
      } else {
        await service.exportPdf(event, rows);
      }
    } catch (e) {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal export: $e'), backgroundColor: Colors.red),
        );
      }
    } finally {
      if (context.mounted) {
        Navigator.of(context, rootNavigator: true).pop();
      }
    }
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final categoriesAsync = ref.watch(categoriesProvider(event.id));
    final resultsAsync = ref.watch(resultsByCategoryAndGenderProvider(event.id));

    return categoriesAsync.when(
      loading: () => Scaffold(
        appBar: AppBar(title: Text('Hasil — ${event.name}')),
        body: const Center(child: CircularProgressIndicator()),
      ),
      error: (e, _) => Scaffold(
        appBar: AppBar(title: Text('Hasil — ${event.name}')),
        body: Center(child: Text('Error: $e')),
      ),
      data: (categories) {
        if (categories.isEmpty) {
          return Scaffold(
            appBar: AppBar(title: Text('Hasil — ${event.name}')),
            body: const Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.emoji_events_outlined, size: 64, color: Colors.grey),
                  SizedBox(height: 16),
                  Text('Belum ada kategori dengan peserta', style: TextStyle(color: Colors.grey)),
                ],
              ),
            ),
          );
        }

        return DefaultTabController(
          length: categories.length,
          child: Scaffold(
            appBar: AppBar(
              title: Text('Hasil — ${event.name}'),
              actions: [
                PopupMenuButton<String>(
                  tooltip: 'Export Data',
                  onSelected: (value) => _exportData(context, ref, value),
                  itemBuilder: (context) => const [
                    PopupMenuItem(
                      value: 'excel',
                      child: Row(
                        children: [
                          Icon(Icons.table_chart_outlined),
                          SizedBox(width: 12),
                          Text('Export Excel'),
                        ],
                      ),
                    ),
                    PopupMenuItem(
                      value: 'pdf',
                      child: Row(
                        children: [
                          Icon(Icons.picture_as_pdf_outlined),
                          SizedBox(width: 12),
                          Text('Export PDF'),
                        ],
                      ),
                    ),
                  ],
                ),
              ],
              bottom: TabBar(
                isScrollable: categories.length > 3,
                tabs: categories.map((c) => Tab(text: c)).toList(),
              ),
            ),
            body: resultsAsync.when(
              loading: () => const Center(child: CircularProgressIndicator()),
              error: (e, _) => Center(child: Text('Error: $e')),
              data: (results) {
                return TabBarView(
                  children: categories.map((category) {
                    final genderMap = results[category] ?? {};
                    // Always show L and P, plus Unknown if exists
                    final hasUnknown = genderMap.containsKey('Unknown');
                    final genders = hasUnknown ? ['L', 'P', 'Unknown'] : ['L', 'P'];

                    return DefaultTabController(
                      length: genders.length,
                      child: Column(
                        children: [
                          TabBar(
                            tabs: genders.map((g) {
                              final label = g == 'Unknown' ? 'Campur' : GenderUtils.label(g);
                              return Tab(text: label);
                            }).toList(),
                          ),
                          Expanded(
                            child: TabBarView(
                              children: genders.map((genderKey) {
                                final list = genderMap[genderKey] ?? [];
                                if (list.isEmpty) {
                                  final label = genderKey == 'Unknown' ? 'Campur' : GenderUtils.label(genderKey);
                                  return Center(
                                    child: Column(
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        Icon(Icons.people_outline, size: 64, color: Colors.grey.shade300),
                                        const SizedBox(height: 16),
                                        Text('Belum ada hasil $category - $label',
                                            style: const TextStyle(color: Colors.grey)),
                                      ],
                                    ),
                                  );
                                }
                                return ListView.builder(
                                  padding: const EdgeInsets.all(16),
                                  itemCount: list.length,
                                  itemBuilder: (context, index) {
                                    final data = list[index];
                                    final result = data['result'] as RaceResult;
                                    final participant = data['participant'] as Participant;
                                    return Card(
                                      child: ListTile(
                                        leading: CircleAvatar(
                                          backgroundColor: index == 0
                                              ? Colors.amber
                                              : index == 1
                                                  ? Colors.grey.shade300
                                                  : index == 2
                                                      ? Colors.brown.shade200
                                                      : null,
                                          child: Text('${index + 1}'),
                                        ),
                                        title: Text(
                                          participant.bibNumber,
                                          style: const TextStyle(fontFamily: 'monospace', fontWeight: FontWeight.bold),
                                        ),
                                        subtitle: Text(participant.name),
                                        trailing: Text(
                                          BibUtils.formatDuration(result.raceDurationSeconds),
                                          style: const TextStyle(
                                              fontFamily: 'monospace', fontWeight: FontWeight.bold, fontSize: 14),
                                        ),
                                      ),
                                    );
                                  },
                                );
                              }).toList(),
                            ),
                          ),
                        ],
                      ),
                    );
                  }).toList(),
                );
              },
            ),
          ),
        );
      },
    );
  }
}
