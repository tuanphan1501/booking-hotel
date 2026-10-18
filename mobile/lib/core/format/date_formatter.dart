import 'package:intl/intl.dart';

/// Formats dates the way the design shows them, per language.
///
/// - vi: short "T6, 20/11", long "Thứ 6, 20/11/2026" (weekday numbers, like Vietnamese calendars)
/// - ja: short "11/20(金)", long "2026/11/20(金)"
/// - en: short "Fri, Nov 20", long "Friday, November 20, 2026"
///
/// Author: PH.Tuan · Created: 2026-10-19
class DateFormatter {
  /// Creates a formatter for [languageCode] ("vi", "ja" or "en").
  DateFormatter(this.languageCode);

  /// UI language.
  final String languageCode;

  /// Vietnamese short weekday names, Monday = index 1 … Sunday = index 7 ([DateTime.weekday]).
  static const List<String> _vietnameseShortWeekdays = ['', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'];

  /// Vietnamese long weekday names, indexed like [_vietnameseShortWeekdays].
  static const List<String> _vietnameseLongWeekdays = [
    '',
    'Thứ 2',
    'Thứ 3',
    'Thứ 4',
    'Thứ 5',
    'Thứ 6',
    'Thứ 7',
    'Chủ nhật',
  ];

  /// Formats a stay date in lists and search fields, e.g. "T6, 20/11".
  String short(DateTime date) => switch (languageCode) {
        'vi' => '${_vietnameseShortWeekdays[date.weekday]}, ${DateFormat('dd/MM').format(date)}',
        'ja' => DateFormat('M/d(E)', 'ja').format(date),
        _ => DateFormat('EEE, MMM d', 'en').format(date),
      };

  /// Formats a full date with the year, e.g. "Thứ 6, 20/11/2026".
  String long(DateTime date) => switch (languageCode) {
        'vi' => '${_vietnameseLongWeekdays[date.weekday]}, ${DateFormat('dd/MM/yyyy').format(date)}',
        'ja' => DateFormat('yyyy/MM/dd(E)', 'ja').format(date),
        _ => DateFormat('EEEE, MMMM d, yyyy', 'en').format(date),
      };
}
