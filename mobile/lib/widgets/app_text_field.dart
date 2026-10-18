import 'package:flutter/material.dart';

import '../app/theme/app_colors.dart';
import '../app/theme/app_typography.dart';

/// Text input with a label that is always visible above the field (design: .fld / .inp).
///
/// - Errors appear under the field; [helpText] is shown when there is no error.
///
/// Author: PH.Tuan · Created: 2026-10-18
class AppTextField extends StatelessWidget {
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
  final Widget? suffix;
  final ValueChanged<String>? onSubmitted;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: AppText.s.copyWith(fontWeight: FontWeight.w500, color: AppColors.ink2)),
        const SizedBox(height: 6),
        TextFormField(
          controller: controller,
          validator: validator,
          keyboardType: keyboardType,
          textInputAction: textInputAction,
          autofillHints: autofillHints,
          obscureText: obscureText,
          onFieldSubmitted: onSubmitted,
          autovalidateMode: AutovalidateMode.onUserInteraction,
          style: AppText.b,
          decoration: InputDecoration(
            suffixIcon: suffix,
            errorText: errorText,
            helperText: helpText,
            errorMaxLines: 3,
            helperMaxLines: 2,
          ),
        ),
      ],
    );
  }
}
