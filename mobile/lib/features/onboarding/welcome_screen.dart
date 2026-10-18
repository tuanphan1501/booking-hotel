import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:go_router/go_router.dart';

import '../../app/l10n/generated/app_localizations.dart';
import '../../app/providers.dart';
import '../../app/theme/app_colors.dart';
import '../../app/theme/app_typography.dart';
import '../../core/config/app_config.dart';

/// MB-01 Welcome & language — shown only on the first launch.
///
/// - The language preselects the device language when it is supported.
/// - Choosing a language applies immediately; "Get started" opens the home tab.
///
/// Author: PH.Tuan · Created: 2026-10-18
class WelcomeScreen extends ConsumerWidget {
  /// Creates the welcome screen.
  const WelcomeScreen({super.key});

  /// Native names of the supported languages (never translated).
  static const Map<String, String> _languageNames = {'vi': 'Tiếng Việt', 'ja': '日本語', 'en': 'English'};

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final currentLanguageCode = ref.watch(localeProvider).languageCode;

    return AnnotatedRegion<SystemUiOverlayStyle>(
      value: SystemUiOverlayStyle.light,
      child: Scaffold(
        backgroundColor: AppColors.ink,
        body: Stack(
          fit: StackFit.expand,
          children: [
            Positioned(
              top: 0,
              left: 0,
              right: 0,
              height: MediaQuery.sizeOf(context).height * 0.64,
              child: SvgPicture.asset('assets/illustrations/sea.svg', fit: BoxFit.cover),
            ),
            const DecoratedBox(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [Colors.transparent, AppColors.ink],
                  stops: [0.40, 0.64],
                ),
              ),
            ),
            SafeArea(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(24, 0, 24, 32),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.end,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Row(
                      children: [
                        SvgPicture.asset('assets/brand/vivustay-mark.svg', width: 48, height: 48),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(l10n.appName, style: AppText.l.copyWith(color: Colors.white)),
                              Text(l10n.welcomeTagline, style: AppText.s.copyWith(color: Colors.white70)),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 24),
                    Text(
                      l10n.welcomeChooseLanguage,
                      style: AppText.s.copyWith(color: Colors.white, fontWeight: FontWeight.w600),
                    ),
                    const SizedBox(height: 12),
                    for (final languageCode in AppConfig.supportedLanguageCodes) ...[
                      _LanguageOption(
                        label: _languageNames[languageCode]!,
                        isSelected: languageCode == currentLanguageCode,
                        onTap: () => ref.read(localeProvider.notifier).setLanguage(languageCode),
                      ),
                      const SizedBox(height: 10),
                    ],
                    const SizedBox(height: 8),
                    FilledButton(
                      style: FilledButton.styleFrom(backgroundColor: Colors.white, foregroundColor: AppColors.ink),
                      onPressed: () async {
                        await ref.read(appPreferencesProvider).setOnboarded();
                        if (context.mounted) context.go('/home');
                      },
                      child: Text(l10n.welcomeStart),
                    ),
                    const SizedBox(height: 12),
                    Text(
                      l10n.welcomeChangeLater,
                      textAlign: TextAlign.center,
                      style: AppText.xs.copyWith(color: Colors.white70),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

/// One selectable language row (radio style on a dark background).
class _LanguageOption extends StatelessWidget {
  const _LanguageOption({required this.label, required this.isSelected, required this.onTap});

  final String label;
  final bool isSelected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Semantics(
      selected: isSelected,
      button: true,
      child: Material(
        color: Colors.white.withValues(alpha: 0.14),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(14),
          side: BorderSide(color: Colors.white.withValues(alpha: isSelected ? 0.9 : 0.4), width: 1.5),
        ),
        child: InkWell(
          borderRadius: BorderRadius.circular(14),
          onTap: onTap,
          child: SizedBox(
            height: 52,
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Row(
                children: [
                  Expanded(
                    child: Text(label, style: AppText.b.copyWith(color: Colors.white, fontWeight: FontWeight.w600)),
                  ),
                  Icon(isSelected ? Icons.radio_button_checked : Icons.radio_button_off, color: Colors.white),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
