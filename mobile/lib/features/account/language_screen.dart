import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/l10n/generated/app_localizations.dart';
import '../../app/providers.dart';
import '../../app/theme/app_colors.dart';
import '../../app/theme/app_spacing.dart';
import '../../app/theme/app_typography.dart';
import '../../core/config/app_config.dart';
import '../../core/format/date_formatter.dart';
import '../../core/format/money_formatter.dart';
import '../../widgets/app_card.dart';
import '../../widgets/app_controls.dart';
import '../../widgets/app_icon.dart';
import '../../widgets/inline_notice.dart';

/// MB-16b Language settings — switches immediately, with a preview of date and money formats.
///
/// - Layout follows design/mobile/screens/MB-16b.png: centered title with back button, description,
///   language list with code tiles and radios, outlined preview card, information notice.
///
/// Author: PH.Tuan · Created: 2026-10-18
class LanguageScreen extends ConsumerWidget {
  /// Creates the language screen.
  const LanguageScreen({super.key});

  /// Native language names (never translated).
  static const Map<String, String> nativeNames = {'vi': 'Tiếng Việt', 'ja': '日本語', 'en': 'English'};

  /// English language names shown as a secondary line.
  static const Map<String, String> _englishNames = {'vi': 'Vietnamese', 'ja': 'Japanese', 'en': 'English'};

  /// Sample values used in the preview card.
  static final DateTime _previewDate = DateTime(2026, 11, 20);
  static const int _previewAmount = 3415400;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final currentLanguageCode = ref.watch(localeProvider).languageCode;
    final languageCodes = AppConfig.supportedLanguageCodes;

    return Scaffold(
      appBar: AppBar(
        leading: IconButton(
          onPressed: () => context.pop(),
          tooltip: MaterialLocalizations.of(context).backButtonTooltip,
          icon: const AppIcon(AppIcons.back, size: 24, color: AppColors.ink),
        ),
        title: Text(l10n.languageTitle),
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(AppSpace.l, AppSpace.s, AppSpace.l, AppSpace.xl),
        children: [
          Text(l10n.languageDescription, style: AppText.s.copyWith(color: AppColors.ink2, fontSize: 14)),
          const SizedBox(height: AppSpace.m),
          AppCard(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
            child: Column(
              children: [
                for (var index = 0; index < languageCodes.length; index++)
                  _LanguageRow(
                    code: languageCodes[index],
                    nativeName: nativeNames[languageCodes[index]]!,
                    englishName: _englishNames[languageCodes[index]]!,
                    isSelected: languageCodes[index] == currentLanguageCode,
                    showDivider: index < languageCodes.length - 1,
                    onTap: () => ref.read(localeProvider.notifier).setLanguage(languageCodes[index]),
                  ),
              ],
            ),
          ),
          const SizedBox(height: AppSpace.l),
          AppCard(
            outlined: true,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(l10n.languagePreview, style: AppText.s.copyWith(fontWeight: FontWeight.w700)),
                const SizedBox(height: AppSpace.s),
                _PreviewRow(
                  label: l10n.languagePreviewDate,
                  value: DateFormatter(currentLanguageCode).long(_previewDate),
                ),
                _PreviewRow(
                  label: l10n.languagePreviewAmount,
                  value: MoneyFormatter(currentLanguageCode).format(_previewAmount),
                ),
              ],
            ),
          ),
          const SizedBox(height: AppSpace.l),
          InlineNotice(message: l10n.languageDataNote),
        ],
      ),
    );
  }
}

/// Language option: 2-letter code tile, native name, English name, radio.
class _LanguageRow extends StatelessWidget {
  const _LanguageRow({
    required this.code,
    required this.nativeName,
    required this.englishName,
    required this.isSelected,
    required this.showDivider,
    required this.onTap,
  });

  final String code;
  final String nativeName;
  final String englishName;
  final bool isSelected;
  final bool showDivider;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Semantics(
      selected: isSelected,
      button: true,
      child: InkWell(
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 10),
          decoration: BoxDecoration(
            border: showDivider ? const Border(bottom: BorderSide(color: AppColors.line2)) : null,
          ),
          child: Row(
            children: [
              Container(
                width: 36,
                height: 36,
                decoration:
                    const BoxDecoration(color: AppColors.line2, borderRadius: BorderRadius.all(Radius.circular(10))),
                alignment: Alignment.center,
                child: Text(
                  code.toUpperCase(),
                  style: AppText.s.copyWith(fontWeight: FontWeight.w700, color: AppColors.ink2),
                ),
              ),
              const SizedBox(width: AppSpace.m),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(nativeName, style: AppText.m.copyWith(fontWeight: FontWeight.w700)),
                    Text(englishName, style: AppText.s.copyWith(color: AppColors.ink3)),
                  ],
                ),
              ),
              AppRadioIndicator(isSelected: isSelected),
            ],
          ),
        ),
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
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(
        children: [
          Expanded(child: Text(label, style: AppText.b.copyWith(color: AppColors.ink2))),
          Text(value, style: AppText.b.copyWith(fontFeatures: AppText.price.fontFeatures)),
        ],
      ),
    );
  }
}
