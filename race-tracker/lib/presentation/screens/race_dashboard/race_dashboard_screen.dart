import 'package:drift/drift.dart' hide Column, UpdateCompanion;
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:mobile_scanner/mobile_scanner.dart';

import '../../../core/utils/bib_utils.dart';
import '../../../data/datasources/local/app_database.dart';
import '../../providers/database_providers.dart';
import '../../widgets/bib_input_field.dart';
import '../../widgets/sync_status_badge.dart';
import '../results/results_screen.dart';
import '../settings/settings_screen.dart';
import 'package_waktu_screen.dart';

final startTimesProvider = FutureProvider.family<List<CategoryStartTime>, int>((ref, eventId) async {
  final db = ref.watch(databaseProvider);
  return db.getStartTimesByEvent(eventId);
});

final unrecordedProvider = FutureProvider.family<List<Participant>, int>((ref, eventId) async {
  final db = ref.watch(databaseProvider);
  return db.getUnrecordedParticipants(eventId);
});

final recordedProvider = FutureProvider.family<List<Map<String, dynamic>>, int>((ref, eventId) async {
  final db = ref.watch(databaseProvider);
  final results = await db.getResultsByEvent(eventId);
  final participants = <Map<String, dynamic>>[];
  for (final result in results) {
    final participant = await db.getParticipantByBib(eventId, result.bibNumber);
    if (participant != null) {
      participants.add({
        'result': result,
        'participant': participant,
      });
    }
  }
  return participants;
});

class RaceDashboardScreen extends ConsumerStatefulWidget {
  final Event event;

  const RaceDashboardScreen({super.key, required this.event});

  @override
  ConsumerState<RaceDashboardScreen> createState() => _RaceDashboardScreenState();
}

class _RaceDashboardScreenState extends ConsumerState<RaceDashboardScreen> {
  void _showError(String message) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message), backgroundColor: Colors.red),
    );
  }

  void _showWarning(String message, {VoidCallback? onConfirm}) {
    if (!mounted) return;
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Peringatan'),
        content: Text(message),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(),
            child: const Text('Batal'),
          ),
          if (onConfirm != null)
            ElevatedButton(
              onPressed: () {
                Navigator.of(ctx).pop();
                onConfirm();
              },
              child: const Text('Ya, Timpa'),
            ),
        ],
      ),
    );
  }

  Future<void> _recordFinish(String bibNumber) async {
    final db = ref.read(databaseProvider);
    final participant = await db.getParticipantByBib(widget.event.id, bibNumber);

    if (participant == null) {
      _showError('BIB tidak ditemukan di event ini');
      return;
    }

    final startTime = await db.getStartTimeForCategory(widget.event.id, participant.distanceCategory);
    if (startTime == null || startTime.startTime.isEmpty) {
      _showError('Kategori ${participant.distanceCategory} belum memiliki waktu start');
      return;
    }

    final now = DateTime.now();
    final parts = startTime.startTime.split(':');
    final startDateTime = DateTime(
      now.year,
      now.month,
      now.day,
      int.parse(parts[0]),
      int.parse(parts[1]),
    );

    if (now.isBefore(startDateTime)) {
      _showError('Race untuk kategori ${participant.distanceCategory} belum dimulai');
      return;
    }

    final existing = await db.getResultByBib(widget.event.id, bibNumber);
    if (existing != null) {
      _showWarning(
        'BIB ini sudah dicatat (durasi: ${BibUtils.formatDuration(existing.raceDurationSeconds)}). Timpa?',
        onConfirm: () => _saveResult(participant, now, startDateTime, existing),
      );
      return;
    }

    await _saveResult(participant, now, startDateTime, null);
  }

  Future<void> _saveResult(Participant participant, DateTime now, DateTime start, RaceResult? existing) async {
    final db = ref.read(databaseProvider);
    final duration = now.difference(start).inSeconds;

    if (existing != null) {
      await (db.update(db.raceResults)
        ..where((t) => t.id.equals(existing.id)))
          .write(RaceResultsCompanion(
        raceFinishedAt: Value(now.toIso8601String()),
        raceDurationSeconds: Value(duration),
        isSynced: const Value(false),
      ));
    } else {
      await db.insertResult(RaceResultsCompanion(
        eventId: Value(widget.event.id),
        bibNumber: Value(participant.bibNumber),
        raceFinishedAt: Value(now.toIso8601String()),
        raceDurationSeconds: Value(duration),
        isSynced: const Value(false),
      ));
    }

    ref.invalidate(recordedProvider(widget.event.id));
    ref.invalidate(unrecordedProvider(widget.event.id));
    ref.invalidate(resultsByCategoryProvider(widget.event.id));
    ref.invalidate(resultsByCategoryAndGenderProvider(widget.event.id));

    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('${participant.name} dicatat — ${BibUtils.formatDuration(duration)}'),
          backgroundColor: Colors.green,
        ),
      );
    }
  }

  Future<void> _confirmReset() async {
    if (!mounted) return;
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Reset Data Finish'),
        content: const Text(
          'Semua data finish (waktu & durasi lari) pada event ini akan dihapus. '
          'Peserta akan kembali menjadi belum tercatat. Lanjutkan?',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(false),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
            onPressed: () => Navigator.of(ctx).pop(true),
            child: const Text('Ya, Reset', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    final db = ref.read(databaseProvider);
    await db.resetResults(widget.event.id);
    ref.invalidate(recordedProvider(widget.event.id));
    ref.invalidate(unrecordedProvider(widget.event.id));
    ref.invalidate(resultsByCategoryProvider(widget.event.id));
    ref.invalidate(resultsByCategoryAndGenderProvider(widget.event.id));

    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Data finish berhasil direset.'),
          backgroundColor: Colors.green,
        ),
      );
    }
  }

  void _startScan() async {
    final result = await Navigator.of(context).push<String>(
      MaterialPageRoute(
        builder: (_) => Scaffold(
          appBar: AppBar(title: const Text('Scan BIB')),
          body: MobileScanner(
            onDetect: (capture) {
              final code = capture.barcodes.first.rawValue;
              if (code != null && mounted) {
                Navigator.of(context).pop(code);
              }
            },
          ),
        ),
      ),
    );

    if (result != null) {
      _recordFinish(result);
    }
  }

  @override
  Widget build(BuildContext context) {
    final startTimesAsync = ref.watch(startTimesProvider(widget.event.id));
    final recordedAsync = ref.watch(recordedProvider(widget.event.id));
    final unrecordedAsync = ref.watch(unrecordedProvider(widget.event.id));

    return Scaffold(
      appBar: AppBar(
        title: Text(widget.event.name),
        actions: [
          SyncStatusBadge(eventId: widget.event.id),
          const SizedBox(width: 8),
          IconButton(
            icon: const Icon(Icons.delete_sweep, color: Colors.red),
            onPressed: _confirmReset,
            tooltip: 'Reset Data Finish',
          ),
          IconButton(
            icon: const Icon(Icons.emoji_events),
            onPressed: () {
              Navigator.of(context).push(
                MaterialPageRoute(
                  builder: (_) => ResultsScreen(event: widget.event),
                ),
              );
            },
            tooltip: 'Hasil',
          ),
          IconButton(
            icon: const Icon(Icons.timer),
            onPressed: () async {
              await Navigator.of(context).push(
                MaterialPageRoute(
                  builder: (_) => PackageWaktuScreen(event: widget.event),
                ),
              );
              ref.invalidate(startTimesProvider(widget.event.id));
            },
            tooltip: 'Pengaturan Waktu',
          ),
          IconButton(
            icon: const Icon(Icons.settings),
            onPressed: () {
              Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const SettingsScreen()),
              );
            },
            tooltip: 'Pengaturan Server',
          ),
        ],
      ),
      body: Column(
        children: [
          startTimesAsync.when(
            loading: () => const SizedBox.shrink(),
            error: (_, _) => const SizedBox.shrink(),
            data: (startTimes) {
              if (startTimes.isEmpty) return const SizedBox.shrink();
              return Container(
                padding: const EdgeInsets.all(12),
                color: Colors.grey.shade100,
                child: Row(
                  children: startTimes.map((st) {
                    final hasTime = st.startTime.isNotEmpty;
                    return Padding(
                      padding: const EdgeInsets.only(right: 16),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(
                            hasTime ? Icons.timer : Icons.timer_off,
                            size: 16,
                            color: hasTime ? Colors.green : Colors.orange,
                          ),
                          const SizedBox(width: 4),
                          Text(
                            '${st.categoryName}: ${hasTime ? st.startTime : '-'}',
                            style: TextStyle(
                              fontSize: 12,
                              color: hasTime ? Colors.black : Colors.orange,
                              fontWeight: hasTime ? FontWeight.normal : FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                    );
                  }).toList(),
                ),
              );
            },
          ),
          Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                Expanded(
                  child: BibInputField(
                    eventId: widget.event.id,
                    onBibSubmitted: _recordFinish,
                  ),
                ),
                const SizedBox(width: 8),
                IconButton.filled(
                  onPressed: _startScan,
                  icon: const Icon(Icons.qr_code_scanner),
                  tooltip: 'Scan BIB',
                ),
              ],
            ),
          ),
          recordedAsync.when(
            loading: () => const Expanded(child: Center(child: CircularProgressIndicator())),
            error: (e, _) => Expanded(child: Center(child: Text('Error: $e'))),
            data: (recorded) {
              int unrecordedCount = 0;
              if (unrecordedAsync.hasValue) {
                unrecordedCount = unrecordedAsync.value?.length ?? 0;
              }
              return Expanded(
                child: Column(
                  children: [
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      child: Row(
                        children: [
                          Text(
                            '${recorded.length} / ${recorded.length + unrecordedCount} peserta tercatat',
                            style: const TextStyle(fontWeight: FontWeight.w600),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 8),
                    Expanded(
                      child: recorded.isEmpty
                          ? const Center(
                              child: Text('Belum ada pencatatan', style: TextStyle(color: Colors.grey)),
                            )
                          : ListView.builder(
                              padding: const EdgeInsets.symmetric(horizontal: 16),
                              itemCount: recorded.length,
                              itemBuilder: (context, index) {
                                final data = recorded[index];
                                final result = data['result'] as RaceResult;
                                final participant = data['participant'] as Participant;
                                return Card(
                                  child: ListTile(
                                    leading: CircleAvatar(
                                      child: Text('${index + 1}'),
                                    ),
                                    title: Text(
                                      participant.bibNumber,
                                      style: const TextStyle(fontFamily: 'monospace', fontWeight: FontWeight.bold),
                                    ),
                                    subtitle: Text('${participant.name} • ${participant.distanceCategory}'),
                                    trailing: Text(
                                      BibUtils.formatDuration(result.raceDurationSeconds),
                                      style: const TextStyle(fontFamily: 'monospace', fontWeight: FontWeight.bold),
                                    ),
                                  ),
                                );
                              },
                            ),
                    ),
                  ],
                ),
              );
            },
          ),
        ],
      ),
    );
  }
}
