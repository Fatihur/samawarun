import 'dart:typed_data';

import 'package:excel/excel.dart';
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:printing/printing.dart';
import 'package:share_plus/share_plus.dart';

import '../../core/utils/bib_utils.dart';
import '../datasources/local/app_database.dart';

class ResultExportRow {
  final String bibNumber;
  final String name;
  final String category;
  final String? gender;
  final int durationSeconds;
  final String? finishedAt;

  const ResultExportRow({
    required this.bibNumber,
    required this.name,
    required this.category,
    this.gender,
    required this.durationSeconds,
    this.finishedAt,
  });
}

class ResultExportService {
  String _fileName(Event event, String extension) {
    final base = 'hasil_${event.eventCode}'.replaceAll(RegExp(r'[^\w\-]'), '_');
    return '$base.$extension';
  }

  Map<String, List<ResultExportRow>> _groupByCategory(List<ResultExportRow> rows) {
    final grouped = <String, List<ResultExportRow>>{};
    for (final row in rows) {
      grouped.putIfAbsent(row.category, () => []).add(row);
    }
    final sortedKeys = grouped.keys.toList()..sort();
    return {for (final key in sortedKeys) key: grouped[key]!};
  }

  Map<String, Map<String, List<ResultExportRow>>> _groupByCategoryAndGender(
      List<ResultExportRow> rows) {
    final grouped = <String, Map<String, List<ResultExportRow>>>{};
    for (final row in rows) {
      final cat = row.category;
      final g = GenderUtils.normalize(row.gender) ?? 'Unknown';
      grouped.putIfAbsent(cat, () => {});
      grouped[cat]!.putIfAbsent(g, () => []).add(row);
    }
    // Sort categories
    final sortedCats = grouped.keys.toList()..sort();
    final result = <String, Map<String, List<ResultExportRow>>>{};
    for (final cat in sortedCats) {
      final byGender = grouped[cat]!;
      // Sort each gender list by duration
      for (final g in byGender.keys) {
        byGender[g]!.sort((a, b) => a.durationSeconds.compareTo(b.durationSeconds));
      }
      // Order genders L, P, Unknown
      final ordered = <String, List<ResultExportRow>>{};
      for (final k in ['L', 'P', 'Unknown']) {
        if (byGender.containsKey(k)) ordered[k] = byGender[k]!;
      }
      // Add any remaining (should not happen)
      for (final k in byGender.keys) {
        if (!ordered.containsKey(k)) ordered[k] = byGender[k]!;
      }
      result[cat] = ordered;
    }
    return result;
  }

  Future<void> exportExcel(Event event, List<ResultExportRow> rows) async {
    final excel = Excel.createExcel();
    // Remove default sheet if we will create custom ones
    final defaultSheet = excel.getDefaultSheet();
    bool defaultRemoved = false;

    if (rows.isEmpty) {
      excel['Hasil'].appendRow([TextCellValue('Belum ada data hasil race.')]);
    } else {
      final grouped = _groupByCategoryAndGender(rows);
      grouped.forEach((category, byGender) {
        byGender.forEach((genderKey, categoryRows) {
          final label = genderKey == 'Unknown'
              ? 'Campur'
              : GenderUtils.label(genderKey);
          var sheetName = '$category - $label';
          sheetName = sheetName.replaceAll(RegExp(r'[\[\]\*\?\/\\:]'), '_');
          if (sheetName.length > 31) sheetName = sheetName.substring(0, 31);
          final sheet = excel[sheetName];

          final headers = ['Peringkat', 'BIB', 'Nama', 'Kategori', 'Gender', 'Waktu Lari', 'Waktu Finish'];
          for (var c = 0; c < headers.length; c++) {
            final cell = sheet.cell(CellIndex.indexByColumnRow(columnIndex: c, rowIndex: 0));
            cell.value = TextCellValue(headers[c]);
            cell.cellStyle = CellStyle(
              bold: true,
              horizontalAlign: HorizontalAlign.Center,
              backgroundColorHex: ExcelColor.grey200,
            );
          }

          for (var i = 0; i < categoryRows.length; i++) {
            final row = categoryRows[i];
            sheet.appendRow([
              IntCellValue(i + 1),
              TextCellValue(row.bibNumber),
              TextCellValue(row.name),
              TextCellValue(row.category),
              TextCellValue(row.gender ?? '-'),
              TextCellValue(BibUtils.formatDuration(row.durationSeconds)),
              TextCellValue(row.finishedAt ?? '-'),
            ]);
          }

          sheet.setColumnWidth(0, 10);
          sheet.setColumnWidth(1, 15);
          sheet.setColumnWidth(2, 30);
          sheet.setColumnWidth(3, 12);
          sheet.setColumnWidth(4, 12);
          sheet.setColumnWidth(5, 14);
          sheet.setColumnWidth(6, 22);
        });
      });
      // Try to remove default sheet if not used
      if (defaultSheet != null && excel.sheets.keys.length > 1) {
        // excel.delete is not public in some versions; keep default if cannot delete
        // We leave it — having an empty default sheet is harmless.
      }
      if (!defaultRemoved && excel.sheets.containsKey('Sheet1') && grouped.isNotEmpty) {
        // leave as is; Excel will show it empty — not harmful
      }
    }

    final bytes = excel.encode();
    if (bytes == null) return;

    final fileName = _fileName(event, 'xlsx');
    await SharePlus.instance.share(ShareParams(
      files: [
        XFile.fromData(
          Uint8List.fromList(bytes),
          mimeType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
          name: fileName,
        ),
      ],
      fileNameOverrides: [fileName],
    ));
  }

  Future<Uint8List> buildPdfBytes(Event event, List<ResultExportRow> rows) async {
    final grouped = _groupByCategoryAndGender(rows);
    final doc = pw.Document(title: 'Hasil ${event.name}');

    final widgets = <pw.Widget>[
      pw.Text('HASIL RACE', style: pw.TextStyle(fontSize: 18, fontWeight: pw.FontWeight.bold)),
      pw.SizedBox(height: 4),
      pw.Text(event.name, style: pw.TextStyle(fontSize: 13)),
      pw.SizedBox(height: 16),
    ];

    if (grouped.isEmpty) {
      widgets.add(pw.Text('Belum ada data hasil race.', style: pw.TextStyle(color: PdfColors.grey)));
    }

    grouped.forEach((category, byGender) {
      widgets.add(pw.Text('Kategori $category',
          style: pw.TextStyle(fontSize: 13, fontWeight: pw.FontWeight.bold)));
      widgets.add(pw.SizedBox(height: 6));

      byGender.forEach((genderKey, categoryRows) {
        final label = genderKey == 'Unknown'
            ? 'Campur'
            : GenderUtils.label(genderKey == 'Unknown' ? null : genderKey);
        final tableData = <List<String>>[];
        for (var i = 0; i < categoryRows.length; i++) {
          final row = categoryRows[i];
          tableData.add([
            '${i + 1}',
            row.bibNumber,
            row.name,
            BibUtils.formatDuration(row.durationSeconds),
            row.finishedAt ?? '-',
          ]);
        }

        widgets.add(pw.Text(label,
            style: pw.TextStyle(fontSize: 11, fontWeight: pw.FontWeight.bold, color: PdfColors.green700)));
        widgets.add(pw.SizedBox(height: 4));
        widgets.add(
          pw.TableHelper.fromTextArray(
            headers: ['Peringkat', 'BIB', 'Nama', 'Waktu Lari', 'Waktu Finish'],
            data: tableData,
            headerStyle: pw.TextStyle(fontSize: 9, fontWeight: pw.FontWeight.bold, color: PdfColors.white),
            headerDecoration: pw.BoxDecoration(color: PdfColors.green700),
            cellStyle: pw.TextStyle(fontSize: 9),
            cellAlignment: pw.Alignment.centerLeft,
            border: pw.TableBorder.all(color: PdfColors.grey400, width: 0.5),
          ),
        );
        widgets.add(pw.SizedBox(height: 12));
      });
      widgets.add(pw.SizedBox(height: 8));
    });

    doc.addPage(pw.MultiPage(
      pageFormat: PdfPageFormat.a4,
      margin: const pw.EdgeInsets.all(32),
      build: (_) => widgets,
    ));

    return doc.save();
  }

  Future<void> exportPdf(Event event, List<ResultExportRow> rows) async {
    final bytes = await buildPdfBytes(event, rows);
    await Printing.sharePdf(bytes: bytes, filename: _fileName(event, 'pdf'));
  }
}
