import 'package:flutter/material.dart';

import '../app/theme/app_colors.dart';
import 'app_icon.dart';

/// Color tone of an [AppChip] (design: .chip variants).
///
/// Author: PH.Tuan · Created: 2026-10-19
enum AppChipTone {
  neutral(AppColors.line2, AppColors.ink2),
  brand(AppColors.brandWash, AppColors.brandInk),

  /// Membership tiers and points only.
  gold(AppColors.goldWash, AppColors.gold),
  amber(AppColors.amberWash, AppColors.amber),
  rose(AppColors.roseWash, AppColors.rose);

  const AppChipTone(this.background, this.foreground);

  final Color background;
  final Color foreground;
}

/// Pill-shaped label with an optional icon (e.g. "2.450 điểm", "Gold", "2 đêm").
///
/// Author: PH.Tuan · Created: 2026-10-19
class AppChip extends StatelessWidget {
  /// Creates a chip.
  const AppChip({required this.label, this.icon, this.tone = AppChipTone.neutral, this.onTap, super.key});

  final String label;

  /// Optional icon name from [AppIcons].
  final String? icon;
  final AppChipTone tone;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    final chip = Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(color: tone.background, borderRadius: BorderRadius.circular(999)),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (icon != null) ...[AppIcon(icon!, size: 16, color: tone.foreground), const SizedBox(width: 6)],
          Text(label, style: TextStyle(fontSize: 13, fontWeight: FontWeight.w500, color: tone.foreground)),
        ],
      ),
    );

    return onTap == null ? chip : GestureDetector(onTap: onTap, child: chip);
  }
}
