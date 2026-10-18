import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/l10n/generated/app_localizations.dart';
import '../../app/providers.dart';
import '../../app/theme/app_colors.dart';
import '../../app/theme/app_typography.dart';
import '../auth/application/auth_controller.dart';
import '../auth/domain/customer.dart';
import 'language_screen.dart';

/// MB-16 Account: profile card, language and sign-out. Guests see a sign-in prompt instead.
///
/// Author: PH.Tuan · Created: 2026-10-18
class AccountScreen extends ConsumerWidget {
  /// Creates the account tab.
  const AccountScreen({super.key});

  Future<void> _confirmLogout(BuildContext context, WidgetRef ref) async {
    final l10n = AppLocalizations.of(context);
    final confirmed = await showAdaptiveDialog<bool>(
      context: context,
      builder: (dialogContext) => AlertDialog.adaptive(
        content: Text(l10n.accountLogoutConfirm),
        actions: [
          TextButton(onPressed: () => Navigator.pop(dialogContext, false), child: Text(l10n.commonCancel)),
          TextButton(onPressed: () => Navigator.pop(dialogContext, true), child: Text(l10n.accountLogout)),
        ],
      ),
    );

    if (confirmed ?? false) {
      await ref.read(authControllerProvider.notifier).logout();
    }
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final authState = ref.watch(authControllerProvider);
    final languageCode = ref.watch(localeProvider).languageCode;

    return Scaffold(
      appBar: AppBar(title: Text(l10n.accountTitle, style: AppText.xl), centerTitle: false),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          switch (authState) {
            AuthSignedIn(:final customer) => _ProfileCard(customer: customer),
            AuthChecking() => const Card(
                child: Padding(padding: EdgeInsets.all(24), child: Center(child: CircularProgressIndicator())),
              ),
            AuthGuest() => Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      Text(l10n.accountSignInPrompt, style: AppText.b),
                      const SizedBox(height: 12),
                      FilledButton(
                        onPressed: () => context.push('/login?returnTo=/account'),
                        child: Text(l10n.accountSignIn),
                      ),
                    ],
                  ),
                ),
              ),
          },
          const SizedBox(height: 16),
          Card(
            child: ListTile(
              leading: const Icon(Icons.language),
              title: Text(l10n.accountLanguage),
              trailing: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    LanguageScreen.nativeNames[languageCode] ?? languageCode,
                    style: AppText.s.copyWith(color: AppColors.ink3),
                  ),
                  const Icon(Icons.chevron_right),
                ],
              ),
              onTap: () => context.push('/account/language'),
            ),
          ),
          if (authState is AuthSignedIn) ...[
            const SizedBox(height: 24),
            OutlinedButton.icon(
              onPressed: () => _confirmLogout(context, ref),
              icon: const Icon(Icons.logout),
              label: Text(l10n.accountLogout),
            ),
          ],
        ],
      ),
    );
  }
}

/// Signed-in customer: initials avatar, name, email, member code and tier.
class _ProfileCard extends StatelessWidget {
  const _ProfileCard({required this.customer});

  final Customer customer;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final nameParts = customer.fullName.trim().split(RegExp(r'\s+'));
    final initials = (nameParts.first.characters.first + (nameParts.length > 1 ? nameParts.last.characters.first : ''))
        .toUpperCase();

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          children: [
            CircleAvatar(
              radius: 28,
              backgroundColor: AppColors.brandWash,
              child: Text(initials, style: AppText.m.copyWith(color: AppColors.brandInk)),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(customer.fullName, style: AppText.m),
                  if (customer.email != null) Text(customer.email!, style: AppText.s.copyWith(color: AppColors.ink3)),
                  Text(l10n.accountMemberCode(customer.memberCode), style: AppText.xs.copyWith(color: AppColors.ink3)),
                ],
              ),
            ),
            if (customer.memberTierName != null)
              Chip(
                avatar: const Icon(Icons.diamond_outlined, size: 16, color: AppColors.gold),
                label: Text(customer.memberTierName!),
                backgroundColor: AppColors.goldWash,
                labelStyle: AppText.xs.copyWith(color: AppColors.gold, fontWeight: FontWeight.w600),
              ),
          ],
        ),
      ),
    );
  }
}
