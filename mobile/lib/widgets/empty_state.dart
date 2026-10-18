import 'package:flutter/material.dart';

import '../app/theme/app_colors.dart';
import '../app/theme/app_typography.dart';

/// Empty / informational state with an illustration, a message and an optional action (design: .empty).
///
/// Author: PH.Tuan · Created: 2026-10-18
class EmptyState extends StatelessWidget {
  /// Creates an empty state.
  const EmptyState({
    required this.icon,
    required this.title,
    required this.message,
    this.actionLabel,
    this.onAction,
    super.key,
  });

  final IconData icon;
  final String title;
  final String message;
  final String? actionLabel;
  final VoidCallback? onAction;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 96,
              height: 96,
              decoration: BoxDecoration(color: AppColors.brandWash, borderRadius: BorderRadius.circular(30)),
              child: Icon(icon, size: 44, color: AppColors.brand),
            ),
            const SizedBox(height: 16),
            Text(title, style: AppText.m, textAlign: TextAlign.center),
            const SizedBox(height: 6),
            Text(message, style: AppText.s.copyWith(color: AppColors.ink3), textAlign: TextAlign.center),
            if (actionLabel != null) ...[
              const SizedBox(height: 16),
              FilledButton(onPressed: onAction, child: Text(actionLabel!)),
            ],
          ],
        ),
      ),
    );
  }
}
