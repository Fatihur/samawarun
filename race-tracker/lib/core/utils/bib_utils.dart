class BibUtils {
  BibUtils._();

  static String? detectCategory(String bibNumber) {
    if (bibNumber.isEmpty) return null;
    if (bibNumber.startsWith('10')) return '10K';
    if (bibNumber.startsWith('7')) return '7K';
    if (bibNumber.startsWith('5')) return '5K';
    return null;
  }

  static String formatDuration(int seconds) {
    final hours = seconds ~/ 3600;
    final minutes = (seconds % 3600) ~/ 60;
    final secs = seconds % 60;
    if (hours > 0) {
      return '${hours.toString().padLeft(2, '0')}:${minutes.toString().padLeft(2, '0')}:${secs.toString().padLeft(2, '0')}';
    }
    return '${minutes.toString().padLeft(2, '0')}:${secs.toString().padLeft(2, '0')}';
  }
}

class GenderUtils {
  GenderUtils._();

  static String? normalize(String? raw) {
    if (raw == null) return null;
    final v = raw.trim().toLowerCase();
    if (v.isEmpty) return null;
    if (v == 'l' || v == 'male' || v == 'laki-laki' || v == 'laki' || v == 'm') return 'L';
    if (v == 'p' || v == 'female' || v == 'perempuan' || v == 'f') return 'P';
    return null;
  }

  static String label(String? normalized) {
    switch (normalized) {
      case 'L':
        return 'Laki-laki';
      case 'P':
        return 'Perempuan';
      default:
        return 'Tidak Diketahui';
    }
  }

  static String shortLabel(String? normalized) {
    switch (normalized) {
      case 'L':
        return 'L';
      case 'P':
        return 'P';
      default:
        return '-';
    }
  }
}
