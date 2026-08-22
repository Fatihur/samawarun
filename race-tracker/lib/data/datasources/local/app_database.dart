import 'dart:io';

import 'package:drift/drift.dart';
import 'package:drift/native.dart';
import 'package:path_provider/path_provider.dart';
import 'package:path/path.dart' as p;

part 'app_database.g.dart';

class Events extends Table {
  IntColumn get id => integer().autoIncrement()();
  TextColumn get eventCode => text().named('event_code')();
  TextColumn get name => text()();
  TextColumn get description => text().nullable()();
  TextColumn get date => text()();
  TextColumn get startTime => text().named('start_time').nullable()();
  TextColumn get location => text().nullable()();
  BoolColumn get isImported => boolean().withDefault(const Constant(true))();

  @override
  List<Set<Column>> get uniqueKeys => [{eventCode}];
}

class Participants extends Table {
  IntColumn get id => integer().autoIncrement()();
  IntColumn get eventId => integer().references(Events, #id)();
  TextColumn get bibNumber => text().named('bib_number')();
  TextColumn get name => text()();
  TextColumn get gender => text().nullable()();
  TextColumn get distanceCategory => text().named('distance_category')();
  TextColumn get jerseySize => text().named('jersey_size').nullable()();

  @override
  List<Set<Column>> get uniqueKeys => [{eventId, bibNumber}];
}

class RaceResults extends Table {
  IntColumn get id => integer().autoIncrement()();
  IntColumn get eventId => integer().references(Events, #id)();
  TextColumn get bibNumber => text().named('bib_number')();
  TextColumn get raceFinishedAt => text().named('race_finished_at')();
  IntColumn get raceDurationSeconds => integer().named('race_duration_seconds')();
  BoolColumn get isSynced => boolean().withDefault(const Constant(false))();

  @override
  List<Set<Column>> get uniqueKeys => [{eventId, bibNumber}];
}

class CategoryStartTimes extends Table {
  IntColumn get id => integer().autoIncrement()();
  IntColumn get eventId => integer().references(Events, #id)();
  TextColumn get categoryName => text().named('category_name')();
  TextColumn get startTime => text().named('start_time')();

  @override
  List<Set<Column>> get uniqueKeys => [{eventId, categoryName}];
}

@DriftDatabase(tables: [Events, Participants, RaceResults, CategoryStartTimes])
class AppDatabase extends _$AppDatabase {
  AppDatabase() : super(_openConnection());

  @override
  int get schemaVersion => 1;

  // Event queries
  Future<List<Event>> getAllEvents() => select(events).get();
  Future<Event?> getEventByCode(String code) =>
      (select(events)..where((t) => t.eventCode.equals(code))).getSingleOrNull();
  Future<int> insertEvent(EventsCompanion event) => into(events).insert(event);
  Future<void> deleteEvent(int eventId) async {
    await (delete(raceResults)..where((t) => t.eventId.equals(eventId))).go();
    await (delete(categoryStartTimes)..where((t) => t.eventId.equals(eventId))).go();
    await (delete(participants)..where((t) => t.eventId.equals(eventId))).go();
    await (delete(events)..where((t) => t.id.equals(eventId))).go();
  }

  // Participant queries
  Future<List<Participant>> getParticipantsByEvent(int eventId) =>
      (select(participants)..where((t) => t.eventId.equals(eventId))).get();
  Future<int> insertParticipant(ParticipantsCompanion participant) =>
      into(participants).insert(participant);

  // Race result queries
  Future<List<RaceResult>> getResultsByEvent(int eventId) =>
      (select(raceResults)..where((t) => t.eventId.equals(eventId))).get();
  Future<RaceResult?> getResultByBib(int eventId, String bib) =>
      (select(raceResults)..where((t) => t.eventId.equals(eventId) & t.bibNumber.equals(bib))).getSingleOrNull();
  Future<int> insertResult(RaceResultsCompanion result) =>
      into(raceResults).insert(result);
  Future<void> updateResult(RaceResult result) =>
      (update(raceResults)..where((t) => t.id.equals(result.id))).write(
        RaceResultsCompanion(
          raceFinishedAt: Value(result.raceFinishedAt),
          raceDurationSeconds: Value(result.raceDurationSeconds),
          isSynced: const Value(false),
        ),
      );
  Future<void> markResultSynced(int id) =>
      (update(raceResults)..where((t) => t.id.equals(id))).write(
        const RaceResultsCompanion(isSynced: Value(true)),
      );
  Future<int> unsyncedCount(int eventId) async {
    final query = select(raceResults)
      ..where((t) => t.eventId.equals(eventId) & t.isSynced.equals(false));
    final results = await query.get();
    return results.length;
  }
  Future<void> resetResults(int eventId) async {
    await (delete(raceResults)..where((t) => t.eventId.equals(eventId))).go();
  }

  // Category start time queries
  Future<List<CategoryStartTime>> getStartTimesByEvent(int eventId) =>
      (select(categoryStartTimes)..where((t) => t.eventId.equals(eventId))).get();
  Future<CategoryStartTime?> getStartTimeForCategory(int eventId, String category) async {
    // Case-insensitive & trim-insensitive lookup to handle '3KKIDS' vs '3Kkids' vs '3K Kids'
    final normalizedInput = category.trim().toUpperCase();
    final all = await getStartTimesByEvent(eventId);
    for (final st in all) {
      if (st.categoryName.trim().toUpperCase() == normalizedInput) {
        return st;
      }
    }
    // Fallback: also try without spaces (e.g. '3K Kids' vs '3KKIDS')
    final noSpaceInput = normalizedInput.replaceAll(' ', '').replaceAll('-', '');
    for (final st in all) {
      final noSpaceStored = st.categoryName.trim().toUpperCase().replaceAll(' ', '').replaceAll('-', '');
      if (noSpaceStored == noSpaceInput) return st;
    }
    return null;
  }
  Future<void> insertStartTime(CategoryStartTimesCompanion startTime) =>
      into(categoryStartTimes).insert(startTime);

  Future<List<String>> getDistinctCategoriesByEvent(int eventId) async {
    final participants = await getParticipantsByEvent(eventId);
    final cats = participants.map((p) => p.distanceCategory.trim().toUpperCase()).toSet().toList()..sort();
    return cats;
  }

  // Participant lookup by BIB
  Future<Participant?> getParticipantByBib(int eventId, String bib) =>
      (select(participants)
        ..where((t) => t.eventId.equals(eventId) & t.bibNumber.equals(bib)))
          .getSingleOrNull();
  Future<List<Participant>> getUnrecordedParticipants(int eventId) async {
    final recordedBibs = select(raceResults)
      ..where((t) => t.eventId.equals(eventId));
    final recorded = await recordedBibs.get();
    final bibs = recorded.map((r) => r.bibNumber).toList();
    final query = select(participants)
      ..where((t) => t.eventId.equals(eventId));
    final all = await query.get();
    return all.where((p) => !bibs.contains(p.bibNumber)).toList();
  }
}

LazyDatabase _openConnection() {
  return LazyDatabase(() async {
    final dbFolder = await getApplicationDocumentsDirectory();
    final file = File(p.join(dbFolder.path, 'race_tracker.db'));
    return NativeDatabase.createInBackground(file);
  });
}
