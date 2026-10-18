// Type scale 24 / 20 / 17 / 15 / 13 / 12 — nothing below 12 (except the 11.5 tab bar label).
// Fonts: Be Vietnam Pro (vi, en), Noto Sans JP (ja) — loaded with google_fonts and cached on the device.

import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import 'app_colors.dart';

/// Font family per UI language.
///
/// Author: PH.Tuan · Created: 2026-10-18
abstract final class AppFonts {
  /// Returns the font family for [locale]: Noto Sans JP for Japanese, Be Vietnam Pro otherwise.
  static String? of(Locale locale) =>
      locale.languageCode == 'ja' ? GoogleFonts.notoSansJp().fontFamily : GoogleFonts.beVietnamPro().fontFamily;
}

/// Text styles of the design system (design tokens "type").
///
/// Author: PH.Tuan · Created: 2026-10-18
abstract final class AppText {
  static const xl =
      TextStyle(fontSize: 24, height: 30 / 24, fontWeight: FontWeight.w700, letterSpacing: -0.2, color: AppColors.ink);
  static const l = TextStyle(fontSize: 20, height: 26 / 20, fontWeight: FontWeight.w700, color: AppColors.ink);
  static const m = TextStyle(fontSize: 17, height: 24 / 17, fontWeight: FontWeight.w600, color: AppColors.ink);
  static const b = TextStyle(fontSize: 15, height: 22 / 15, fontWeight: FontWeight.w400, color: AppColors.ink);
  static const s = TextStyle(fontSize: 13, height: 18 / 13, fontWeight: FontWeight.w400, color: AppColors.ink);
  static const xs = TextStyle(fontSize: 12, height: 16 / 12, fontWeight: FontWeight.w400, color: AppColors.ink);
  static const tab = TextStyle(fontSize: 11.5, height: 14 / 11.5, fontWeight: FontWeight.w500);

  /// Amounts: bold with tabular figures so columns line up.
  static const price = TextStyle(fontWeight: FontWeight.w700, fontFeatures: [FontFeature.tabularFigures()]);
}
