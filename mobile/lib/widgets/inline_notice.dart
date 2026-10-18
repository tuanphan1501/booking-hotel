import 'package:flutter/material.dart';

import '../app/theme/app_colors.dart';
import '../app/theme/app_typography.dart';
import 'app_icon.dart';

/// Tone of an [InlineNotice] (design: .toast variants).
///
/// Author: PH.Tuan · Created: 2026-10-19
enum InlineNoticeTone {
  /// Information (e.g. translation note on MB-16b).
  info(AppColors.skyWash, Color(0xFF22488A), AppIcons.info),

  /// Warning or pending (e.g. payment hold timer).
  warning(AppColors.amberWash, AppColors.amberText, AppIcons.warning),

  /// Positive confirmation.
  success(AppColors.brandWash, AppColors.brandInk, AppIcons.check);

  const InlineNoticeTone(this.background, this.foreground, this.icon);

  final Color background;
  final Color foreground;
  final String icon;
}

/// Message box placed inside a page (not a floating snackbar).
///
/// Author: PH.Tuan · Created: 2026-10-19
class InlineNotice extends StatelessWidget {
  /// Creates a notice.
  const InlineNotice({required this.message, this.tone = InlineNoticeTone.info, super.key});

  final String message;
  final InlineNoticeTone tone;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(color: tone.background, borderRadius: BorderRadius.circular(14)),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          AppIcon(tone.icon, color: tone.foreground),
          const SizedBox(width: 10),
          Expanded(child: Text(message, style: AppText.s.copyWith(color: tone.foreground))),
        ],
      ),
    );
  }
}
