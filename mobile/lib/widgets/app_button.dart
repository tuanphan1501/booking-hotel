import 'package:flutter/material.dart';

import '../app/theme/app_colors.dart';
import 'app_icon.dart';

/// Visual style of an [AppButton] (design: .btn variants).
///
/// Author: PH.Tuan · Created: 2026-10-19
enum AppButtonVariant {
  /// Brand red — the single main action of a screen.
  primary,

  /// White with a 1.5 px border.
  secondary,

  /// Near-black (e.g. "Continue with Apple").
  dark,

  /// White on dark backgrounds (welcome screen).
  light,

  /// White with a rose border for destructive actions.
  danger,
}

/// Button of the design system: 48 px high, 14 px radius, optional leading icon and loading state.
///
/// Author: PH.Tuan · Created: 2026-10-19
class AppButton extends StatelessWidget {
  /// Creates a button. Pass null [onPressed] to disable it.
  const AppButton({
    required this.label,
    required this.onPressed,
    this.variant = AppButtonVariant.primary,
    this.icon,
    this.leading,
    this.isLoading = false,
    this.expand = true,
    super.key,
  });

  final String label;
  final VoidCallback? onPressed;
  final AppButtonVariant variant;

  /// Optional icon name from [AppIcons] shown before the label.
  final String? icon;

  /// Custom leading widget (e.g. a multi-color logo); takes precedence over [icon].
  final Widget? leading;

  /// Shows a spinner and ignores taps.
  final bool isLoading;

  /// Stretches to the available width.
  final bool expand;

  @override
  Widget build(BuildContext context) {
    final (background, foreground, borderColor) = switch (variant) {
      AppButtonVariant.primary => (AppColors.brand, Colors.white, null),
      AppButtonVariant.secondary => (Colors.white, AppColors.ink, AppColors.line),
      AppButtonVariant.dark => (const Color(0xFF111111), Colors.white, null),
      AppButtonVariant.light => (Colors.white, AppColors.ink, null),
      AppButtonVariant.danger => (Colors.white, AppColors.rose, AppColors.dangerBorder),
    };
    final isEnabled = onPressed != null && !isLoading;

    final content = isLoading
        ? SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.5, color: foreground))
        : Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              if (leading != null) ...[leading!, const SizedBox(width: 10)],
              if (leading == null && icon != null) ...[AppIcon(icon!, color: foreground), const SizedBox(width: 8)],
              Flexible(child: Text(label, overflow: TextOverflow.ellipsis)),
            ],
          );

    return SizedBox(
      width: expand ? double.infinity : null,
      height: 48,
      child: TextButton(
        onPressed: isEnabled ? onPressed : null,
        style: TextButton.styleFrom(
          backgroundColor: isEnabled || isLoading ? background : AppColors.disabledBg,
          foregroundColor: foreground,
          disabledForegroundColor: isLoading ? foreground : AppColors.disabledFg,
          padding: const EdgeInsets.symmetric(horizontal: 20),
          textStyle: const TextStyle(fontSize: 15, fontWeight: FontWeight.w600),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(14),
            side: borderColor == null ? BorderSide.none : BorderSide(color: borderColor, width: 1.5),
          ),
        ),
        child: content,
      ),
    );
  }
}
