import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../data/datasources/local/app_database.dart';
import '../../../data/repositories/event_fetch_service.dart';
import '../../providers/database_providers.dart';
import '../race_dashboard/race_dashboard_screen.dart';

final eventsProvider = FutureProvider<List<Event>>((ref) async {
  final db = ref.watch(databaseProvider);
  return db.getAllEvents();
});

class HomeScreen extends ConsumerWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final eventsAsync = ref.watch(eventsProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Samawa Run Race Tracker'),
      ),
      body: eventsAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text('Error: $e')),
        data: (events) {
          if (events.isEmpty) {
            return const Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.sports_motorsports, size: 64, color: Colors.grey),
                  SizedBox(height: 16),
                  Text('Belum ada event', style: TextStyle(fontSize: 18, color: Colors.grey)),
                  SizedBox(height: 8),
                  Text('Sinkronkan event dari website', style: TextStyle(fontSize: 14, color: Colors.grey)),
                ],
              ),
            );
          }

          return ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: events.length,
            itemBuilder: (context, index) {
              final event = events[index];
              return Card(
                child: ListTile(
                  leading: const Icon(Icons.event),
                  title: Text(event.name),
                  subtitle: Text('${event.date} • ${event.location ?? ''}'),
                  trailing: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      IconButton(
                        icon: const Icon(Icons.delete_outline, color: Colors.red),
                        onPressed: () => _confirmDelete(context, ref, event),
                      ),
                      const Icon(Icons.chevron_right),
                    ],
                  ),
                  onTap: () {
                    Navigator.of(context).push(
                      MaterialPageRoute(
                        builder: (_) => RaceDashboardScreen(event: event),
                      ),
                    );
                  },
                ),
              );
            },
          );
        },
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => _syncFromWeb(context, ref),
        icon: const Icon(Icons.sync),
        label: const Text('Sinkron dari Web'),
      ),
    );
  }

  void _confirmDelete(BuildContext context, WidgetRef ref, Event event) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Hapus Event'),
        content: Text('Hapus event "${event.name}" dan semua data?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
            onPressed: () async {
              final db = ref.read(databaseProvider);
              await (db.delete(db.events)..where((t) => t.id.equals(event.id))).go();
              ref.invalidate(eventsProvider);
              if (ctx.mounted) Navigator.of(ctx).pop();
            },
            child: const Text('Hapus', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  void _syncFromWeb(BuildContext context, WidgetRef ref) async {
    final controller = TextEditingController();
    final eventId = await showDialog<int>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Sinkron dari Web'),
        content: TextField(
          controller: controller,
          keyboardType: TextInputType.number,
          decoration: const InputDecoration(
            labelText: 'Masukkan Event ID',
            hintText: 'contoh: 1',
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(),
            child: const Text('Batal'),
          ),
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

    if (eventId == null || !context.mounted) return;

    // Show loading
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => const AlertDialog(
        content: Row(
          children: [
            CircularProgressIndicator(),
            SizedBox(width: 16),
            Text('Mengambil data dari web...'),
          ],
        ),
      ),
    );

    try {
      final fetchService = ref.read(eventFetchProvider);
      final data = await fetchService.fetchEvent(eventId);
      
      if (data != null) {
        await fetchService.syncEventToLocal(data);
        ref.invalidate(eventsProvider);
      }
      
      if (context.mounted) {
        Navigator.of(context).pop(); // dismiss loading
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Event berhasil disinkronkan (${data?.participants.length ?? 0} peserta)')),
        );
      }
    } catch (e) {
      if (context.mounted) {
        Navigator.of(context).pop(); // dismiss loading
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal sinkron: $e'), backgroundColor: Colors.red),
        );
      }
    }
  }
}
