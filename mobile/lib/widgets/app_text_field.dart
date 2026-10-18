import 'package:flutter/material.dart';

import '../app/theme/app_colors.dart';
import '../app/theme/app_typography.dart';
import 'app_icon.dart';

/// Text input of the design system (design: .fld / .inp).
///
/// - The label is always visible above the field (never a floating placeholder).
/// - 48 px high, white, 12 px radius, 1.5 px border; 2 px brand border when focused; rose border on error.
/// - Errors (local validation or [errorText] from the server) appear under the field with a warning icon;
///   otherwise [helpText] is shown.
///
/// Author: PH.Tuan · Created: 2026-10-18
class AppTextField extends StatefulWidget {
  /// Creates a labeled text field.
  const AppTextField({
    required this.label,
    this.controller,
    this.helpText,
    this.errorText,
    this.validator,
    this.keyboardType,
    this.textInputAction,
    this.autofillHints,
    this.obscureText = false,
    this.suffix,
    this.onSubmitted,
    super.key,
  });

  final String label;
  final TextEditingController? controller;
  final String? helpText;

  /// Error from the server (e.g. wrong password); local validation uses [validator].
  final String? errorText;
  final String? Function(String?)? validator;
  final TextInputType? keyboardType;
  final TextInputAction? textInputAction;
  final Iterable<String>? autofillHints;
  final bool obscureText;

  /// Trailing widget inside the field (e.g. show / hide password).
  final Widget? suffix;
  final ValueChanged<String>? onSubmitted;

  @override
  State<AppTextField> createState() => _AppTextFieldState();
}

class _AppTextFieldState extends State<AppTextField> {
  final FocusNode _focusNode = FocusNode();

  @override
  void initState() {
    super.initState();
    _focusNode.addListener(() => setState(() {}));
  }

  @override
  void dispose() {
    _focusNode.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return FormField<String>(
      validator: (_) => widget.validator?.call(widget.controller?.text),
      builder: (field) {
        final errorMessage = field.errorText ?? widget.errorText;
        final hasError = errorMessage != null;
        final borderColor = hasError ? AppColors.rose : (_focusNode.hasFocus ? AppColors.brand : AppColors.line);
        final borderWidth = _focusNode.hasFocus ? 2.0 : 1.5;

        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(widget.label, style: AppText.s.copyWith(fontWeight: FontWeight.w500, color: AppColors.ink2)),
            const SizedBox(height: 6),
            AnimatedContainer(
              duration: const Duration(milliseconds: 120),
              constraints: const BoxConstraints(minHeight: 48),
              padding: EdgeInsets.only(left: 14, right: widget.suffix == null ? 14 : 4),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: borderColor, width: borderWidth),
              ),
              child: Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: widget.controller,
                      focusNode: _focusNode,
                      keyboardType: widget.keyboardType,
                      textInputAction: widget.textInputAction,
                      autofillHints: widget.autofillHints,
                      obscureText: widget.obscureText,
                      onSubmitted: widget.onSubmitted,
                      onChanged: field.didChange,
                      style: AppText.b,
                      decoration: const InputDecoration(
                        isCollapsed: true,
                        filled: false,
                        border: InputBorder.none,
                        enabledBorder: InputBorder.none,
                        focusedBorder: InputBorder.none,
                        contentPadding: EdgeInsets.symmetric(vertical: 13),
                      ),
                    ),
                  ),
                  if (widget.suffix != null) widget.suffix!,
                ],
              ),
            ),
            if (hasError) ...[
              const SizedBox(height: 6),
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Padding(
                    padding: EdgeInsets.only(top: 1),
                    child: AppIcon(AppIcons.warning, size: 16, color: AppColors.rose),
                  ),
                  const SizedBox(width: 4),
                  Expanded(child: Text(errorMessage, style: AppText.xs.copyWith(color: AppColors.rose))),
                ],
              ),
            ] else if (widget.helpText != null) ...[
              const SizedBox(height: 6),
              Text(widget.helpText!, style: AppText.xs.copyWith(color: AppColors.ink3)),
            ],
          ],
        );
      },
    );
  }
}
