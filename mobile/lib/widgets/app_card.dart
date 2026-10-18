import 'package:flutter/material.dart';

import '../app/theme/app_colors.dart';
import '../app/theme/app_spacing.dart';
import '../app/theme/app_typography.dart';
import 'app_icon.dart';

/// White surface with an 18 px radius and the soft card shadow (design: .card).
///
/// - [outlined] draws a 1 px border instead of the shadow (design: .card.flat).
///
/// Author: PH.Tuan · Created: 2026-10-19
class AppCard extends StatelessWidget {
  /// Creates a card around [child].
  const AppCard({
    required this.child,
    this.padding = const EdgeInsets.all(14),
    this.color = Colors.white,
    this.outlined = false,
    super.key,
  });

  final Widget child;
  final EdgeInsetsGeometry padding;
  final Color color;
  final bool outlined;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: padding,
      decoration: BoxDecoration(
        color: color,
        borderRadius: const BorderRadius.all(AppRadius.l),
        border: outlined ? Border.all(color: AppColors.line) : null,
        boxShadow: outlined ? null : AppShadow.card,
      ),
      child: child,
    );
  }
}

/// Row of a settings-style list: icon tile, title, optional value, chevron (design: .list-row).
///
/// Author: PH.Tuan · Created: 2026-10-19
class AppListRow extends StatelessWidget {
  /// Creates a list row.
  const AppListRow({
    required this.icon,
    required this.title,
    this.value,
    this.onTap,
    this.showDivider = true,
    super.key,
  });

  /// Icon name from [AppIcons].
  final String icon;
  final String title;

  /// Secondary text shown before the chevron (e.g. the current language).
  final String? value;
  final VoidCallback? onTap;

  /// Draws the separator under the row (false for the last row of a group).
  final bool showDivider;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      child: Container(
        constraints: const BoxConstraints(minHeight: 52),
        decoration: BoxDecoration(
          border: showDivider ? const Border(bottom: BorderSide(color: AppColors.line2)) : null,
        ),
        padding: const EdgeInsets.symmetric(vertical: 8),
        child: Row(
          children: [
            Container(
              width: 36,
              height: 36,
              decoration:
                  const BoxDecoration(color: AppColors.line2, borderRadius: BorderRadius.all(Radius.circular(10))),
              alignment: Alignment.center,
              child: AppIcon(icon, color: AppColors.ink2),
            ),
            const SizedBox(width: AppSpace.m),
            Expanded(child: Text(title, style: AppText.b)),
            if (value != null) ...[
              Text(value!, style: AppText.s.copyWith(color: AppColors.ink3)),
              const SizedBox(width: AppSpace.s),
            ],
            const AppIcon(AppIcons.chevronRight, color: AppColors.ink2),
          ],
        ),
      ),
    );
  }
}
