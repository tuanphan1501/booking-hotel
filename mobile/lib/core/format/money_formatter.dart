import 'package:intl/intl.dart';

/// Formats VND amounts like the backend and the design rules.
///
/// - vi: "3.415.400 ₫"   ja: "3,415,400₫"   en: "3,415,400 ₫"
///
/// Author: PH.Tuan · Created: 2026-10-18
class MoneyFormatter {
  /// Creates a formatter for [languageCode] ("vi", "ja" or "en").
  MoneyFormatter(this.languageCode);

  /// UI language used to pick separators and the currency suffix.
  final String languageCode;

  /// Formats [amount] with every digit, e.g. 1350000 → "1.350.000 ₫".
  String format(int amount) {
    final digits = NumberFormat.decimalPattern(languageCode == 'vi' ? 'vi_VN' : 'en_US').format(amount.abs());
    final sign = amount < 0 ? '−' : '';

    return languageCode == 'ja' ? '$sign$digits₫' : '$sign$digits ₫';
  }
}
