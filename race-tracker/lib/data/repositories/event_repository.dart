import 'package:drift/drift.dart';

import '../datasources/local/app_database.dart';
import '../models/import_models.dart';

class EventRepository {
  final AppDatabase _db;

  EventRepository(this._db);

  Future<bool> eventExists(String eventCode) async {
    final event = await _db.getEventByCode(eventCode);
    return event != null;
  }

  Future<void> importEvent(EventDataImport data) async {
    final exists = await eventExists(data.event.eventCode);

    if (exists) {
      final existing = await _db.getEventByCode(data.event.eventCode);
      if (existing != null) {
        await _db.deleteEvent(existing.id);
      }
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

  Future<List<Event>> getAllEvents() async {
    return _db.getAllEvents();
  }

  Future<void> deleteEvent(int eventId) async {
    await _db.deleteEvent(eventId);
  }
}
