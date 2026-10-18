import 'package:flutter/material.dart';

/// Color tokens of the Vivu Stay design system.
///
/// Source of truth: design/mobile/tokens/design-tokens.json.
///
/// Author: PH.Tuan · Created: 2026-10-18
abstract final class AppColors {
  // Text & surfaces
  /// Primary text.
  static const ink = Color(0xFF2B1A16);

  /// Secondary text (8.6:1 on white).
  static const ink2 = Color(0xFF5C4640);

  /// Tertiary text (5.7:1 on white — WCAG AA).
  static const ink3 = Color(0xFF7A615A);
  static const line = Color(0xFFEFE3DF);
  static const line2 = Color(0xFFF6EEEB);

  /// Screen background.
  static const paper = Color(0xFFFBF6F4);
  static const surface = Color(0xFFFFFFFF);

  // Brand — the only color for primary actions
  static const brand = Color(0xFFB42318);
  static const brandInk = Color(0xFF8A1A12);
  static const brandWash = Color(0xFFFDECE9);
  static const brandSoft = Color(0xFFF3B4AA);

  // Gold — membership tiers and points only
  static const gold = Color(0xFF9A6516);
  static const goldWash = Color(0xFFF8EDDA);
  static const goldHi = Color(0xFFC98A2E);

  // Status colors
  /// Errors and cancellations — always paired with an icon.
  static const rose = Color(0xFF9F1239);
  static const roseWash = Color(0xFFFCE7EE);

  /// Warnings, pending states, payment hold timer.
  static const amber = Color(0xFFB4540A);
  static const amberWash = Color(0xFFFDF0E1);
  static const amberText = Color(0xFF6E4505);

  /// Information, "in house".
  static const sky = Color(0xFF6E4B3E);
  static const skyWash = Color(0xFFF3EAE6);

  // Controls
  static const disabledBg = Color(0xFFE8DCD8);
  static const disabledFg = Color(0xFF9C8781);
  static const placeholder = Color(0xFF9C8781);
  static const controlBorder = Color(0xFFC9B6B0);
  static const dangerBorder = Color(0xFFEFC5C1);
  static const scrim = Color(0x801E120F);

  /// Background of the booking "key card" (MB-09, MB-11, MB-13).
  static const keyCardGradient = LinearGradient(
    begin: Alignment(-0.34, -0.94), // ~160°
    end: Alignment(0.34, 0.94),
    colors: [Color(0xFF6A1D14), Color(0xFF2B1A16)],
  );

  /// Membership progress bar (MB-13).
  static const progressGold = LinearGradient(
    colors: [goldHi, Color(0xFFE2C27E)],
  );
  static const progressTrack = Color(0xFFEFE4DA);
}
