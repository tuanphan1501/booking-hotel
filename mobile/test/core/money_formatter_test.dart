import 'package:flutter_test/flutter_test.dart';
import 'package:vivu_stay/core/format/money_formatter.dart';

/// VND formatting per language — must match the backend MoneyFormatter.
///
/// Author: PH.Tuan · Created: 2026-10-18
void main() {
  test('formats amounts per language', () {
    expect(MoneyFormatter('vi').format(3415400), '3.415.400 ₫');
    expect(MoneyFormatter('ja').format(3415400), '3,415,400₫');
    expect(MoneyFormatter('en').format(3415400), '3,415,400 ₫');
  });

  test('prefixes negative amounts with a minus sign', () {
    expect(MoneyFormatter('en').format(-100000), '−100,000 ₫');
  });
}
