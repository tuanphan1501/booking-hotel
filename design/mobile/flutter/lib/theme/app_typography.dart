// Thang chữ 24 / 20 / 17 / 15 / 13 / 12 — không dùng dưới 12 (trừ nhãn tab bar 11,5)
// Font: Be Vietnam Pro (vi), Noto Sans JP (ja) — khai báo trong pubspec.yaml hoặc dùng google_fonts.
import 'dart:ui' show FontFeature;
import 'package:flutter/material.dart';
import 'app_colors.dart';

abstract final class AppFonts {
  static const vi = 'BeVietnamPro';
  static const ja = 'NotoSansJP';
  static String of(Locale locale) => locale.languageCode == 'ja' ? ja : vi;
}

abstract final class AppText {
  static const xl = TextStyle(fontSize: 24, height: 30 / 24, fontWeight: FontWeight.w700, letterSpacing: -0.2, color: AppColors.ink);
  static const l = TextStyle(fontSize: 20, height: 26 / 20, fontWeight: FontWeight.w700, color: AppColors.ink);
  static const m = TextStyle(fontSize: 17, height: 24 / 17, fontWeight: FontWeight.w600, color: AppColors.ink);
  static const b = TextStyle(fontSize: 15, height: 22 / 15, fontWeight: FontWeight.w400, color: AppColors.ink);
  static const s = TextStyle(fontSize: 13, height: 18 / 13, fontWeight: FontWeight.w400, color: AppColors.ink);
  static const xs = TextStyle(fontSize: 12, height: 16 / 12, fontWeight: FontWeight.w400, color: AppColors.ink);
  static const tab = TextStyle(fontSize: 11.5, height: 14 / 11.5, fontWeight: FontWeight.w500);

  /// Số tiền: đậm + chữ số đều cột
  static const price = TextStyle(fontWeight: FontWeight.w700, fontFeatures: [FontFeature.tabularFigures()]);
}
