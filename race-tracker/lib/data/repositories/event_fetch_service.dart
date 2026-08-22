import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:drift/drift.dart';

import '../datasources/local/app_database.dart';
import '../models/import_models.dart';
import '../../presentation/providers/database_providers.dart';

final eventFetchProvider = Provider<EventFetchService>((ref) {
  return EventFetchService(ref.watch(databaseProvider));
});

class EventFetchService {
  final AppDatabase _db;
  final String _baseUrl = 'https://samawarun.site';

  EventFetchService(this._db);

  Future<List<dynamic>> fetchActiveEvents() async {
    // Fetch events from web - parse HTML or use list endpoint
    return [];
  }

  Future<EventDataImport?> fetchEvent(int eventId) async {
    final dio = Dio();
    final response = await dio.get(
      '$_baseUrl/admin/api/mobile-export',
      queryParameters: {'event_id': eventId},
    );

    if (response.statusCode == 200 && response.data['success'] == true) {
      return EventDataImport.fromJson(response.data['data']);
    }

    throw Exception('Gagal mengambil data event');
  }

  Future<void> syncEventToLocal(EventDataImport data) async {
    final exists = await _db.getEventByCode(data.event.eventCode);

    if (exists != null) {
      await _db.deleteEvent(exists.id);
    }

    final eventId = await _db.insertEvent(
      EventsCompanion(
        eventCode: Value(data.event.eventCode),
        name: Value(data.event.name),
        description: Value(data.event.description),
        date: Value(data.event.date ?? ''),
        startTime: Value(data.event.startTime),
        location: Value(data.event.location),
        isImported: const Value(true),
      ),
    );

    final participantCategoryNames = data.participants
        .map((p) => p.distanceCategory.toUpperCase())
        .toSet();

    final categoriesToInsert = data.categories
        .where((c) => participantCategoryNames.contains(c.name.toUpperCase()))
        .toList();

    if (categoriesToInsert.isNotEmpty) {
      for (final category in categoriesToInsert) {
        await _db.insertStartTime(
          CategoryStartTimesCompanion(
            eventId: Value(eventId),
            categoryName: Value(category.name),
            startTime: const Value(''),
          ),
        );
      }
    } else if (participantCategoryNames.isNotEmpty) {
      for (final name in participantCategoryNames) {
        final original = data.participants
            .firstWhere((p) => p.distanceCategory.toUpperCase() == name)
            .distanceCategory;
        await _db.insertStartTime(
          CategoryStartTimesCompanion(
            eventId: Value(eventId),
            categoryName: Value(original),
            startTime: const Value(''),
          ),
        );
      }
    }

    for (final participant in data.participants) {
      if (participant.bibNumber.isNotEmpty) {
        await _db.insertParticipant(
          ParticipantsCompanion(
            eventId: Value(eventId),
            bibNumber: Value(participant.bibNumber),
            name: Value(participant.name),
            gender: Value(participant.gender),
            distanceCategory: Value(participant.distanceCategory),
            jerseySize: Value(participant.jerseySize),
          ),
        );
      }
    }
  }
}
