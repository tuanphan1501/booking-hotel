import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../../app/l10n/generated/app_localizations.dart';
import '../../app/providers.dart';
import '../../app/theme/app_colors.dart';
import '../../app/theme/app_typography.dart';
import '../../core/config/app_config.dart';
import '../../core/format/money_formatter.dart';

/// MB-16b Language settings — switches immediately, with a preview of date and money formats.
///
/// Author: PH.Tuan · Created: 2026-10-18
class LanguageScreen extends ConsumerWidget {
  /// Creates the language screen.
  const LanguageScreen({super.key});

  /// Native language names (never translated).
  static const Map<String, String> nativeNames = {'vi': 'Tiếng Việt', 'ja': '日本語', 'en': 'English'};

  /// English language names shown as a secondary line.
  static const Map<String, String> _englishNames = {'vi': 'Vietnamese', 'ja': 'Japanese', 'en': 'English'};

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final currentLanguageCode = ref.watch(localeProvider).languageCode;
    final previewDate = DateTime(2026, 11, 20);

    return Scaffold(
      appBar: AppBar(title: Text(l10n.languageTitle)),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Text(l10n.languageDescription, style: AppText.s.copyWith(color: AppColors.ink2)),
          const SizedBox(height: 12),
          Card(
            child: RadioGroup<String>(
              groupValue: currentLanguageCode,
              onChanged: (languageCode) => ref.read(localeProvider.notifier).setLanguage(languageCode!),
              child: Column(
                children: [
                  for (final languageCode in AppConfig.supportedLanguageCodes)
                    RadioListTile<String>(
                      value: languageCode,
                      title: Text(nativeNames[languageCode]!),
                      subtitle: Text(_englishNames[languageCode]!),
                      secondary: CircleAvatar(
                        backgroundColor: AppColors.line2,
                        child:
                            Text(languageCode.toUpperCase(), style: AppText.xs.copyWith(fontWeight: FontWeight.w700)),
                      ),
                    ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(l10n.languagePreview, style: AppText.s.copyWith(fontWeight: FontWeight.w600)),
                  const SizedBox(height: 8),
                  _PreviewRow(
                    label: l10n.languagePreviewDate,
                    value: DateFormat.MMMEd(currentLanguageCode).format(previewDate),
                  ),
                  _PreviewRow(
                    label: l10n.languagePreviewAmount,
                    value: MoneyFormatter(currentLanguageCode).format(3415400),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

/// Label / value row of the preview card.
class _PreviewRow extends StatelessWidget {
  const _PreviewRow({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        children: [
          Expanded(child: Text(label, style: AppText.s.copyWith(color: AppColors.ink2))),
          Text(value, style: AppText.s.copyWith(fontWeight: FontWeight.w600)),
        ],
      ),
    );
  }
}
