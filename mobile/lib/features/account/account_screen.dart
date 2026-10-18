import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/l10n/generated/app_localizations.dart';
import '../../app/providers.dart';
import '../../app/theme/app_colors.dart';
import '../../app/theme/app_spacing.dart';
import '../../app/theme/app_typography.dart';
import '../../widgets/app_button.dart';
import '../../widgets/app_card.dart';
import '../../widgets/app_chip.dart';
import '../../widgets/app_icon.dart';
import '../auth/application/auth_controller.dart';
import '../auth/domain/customer.dart';
import 'language_screen.dart';

/// MB-16 Account.
///
/// - Layout follows design/mobile/screens/MB-16.png: large title, profile card with tier chip,
///   two grouped lists of settings, sign-out button and "Delete account" link.
/// - Guests see a sign-in card instead of the profile; language stays available.
/// - Rows whose screens do not exist yet show a "coming soon" message.
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

  void _showComingSoon(BuildContext context) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(AppLocalizations.of(context).authComingSoon)));
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final authState = ref.watch(authControllerProvider);
    final languageCode = ref.watch(localeProvider).languageCode;
    final isSignedIn = authState is AuthSignedIn;

    return Scaffold(
      body: SafeArea(
        child: ListView(
          padding: const EdgeInsets.fromLTRB(AppSpace.l, AppSpace.m, AppSpace.l, AppSpace.xl),
          children: [
            Padding(
              padding: const EdgeInsets.only(left: 4, bottom: AppSpace.l),
              child: Text(l10n.accountTitle, style: AppText.xl.copyWith(fontSize: 28)),
            ),
            switch (authState) {
              AuthSignedIn(:final customer) => _ProfileCard(customer: customer),
              AuthChecking() => const AppCard(
                  child: SizedBox(height: 56, child: Center(child: CircularProgressIndicator())),
                ),
              AuthGuest() => AppCard(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      Text(l10n.accountSignInPrompt, style: AppText.b),
                      const SizedBox(height: AppSpace.m),
                      AppButton(label: l10n.accountSignIn, onPressed: () => context.push('/login?returnTo=/account')),
                    ],
                  ),
                ),
            },
            const SizedBox(height: AppSpace.l),
            AppCard(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
              child: Column(
                children: [
                  if (isSignedIn) ...[
                    AppListRow(
                      icon: AppIcons.user,
                      title: l10n.accountPersonalInfo,
                      onTap: () => _showComingSoon(context),
                    ),
                    AppListRow(
                      icon: AppIcons.heart,
                      title: l10n.accountFavorites,
                      onTap: () => _showComingSoon(context),
                    ),
                  ],
                  AppListRow(
                    icon: AppIcons.globe,
                    title: l10n.accountLanguage,
                    value: LanguageScreen.nativeNames[languageCode],
                    onTap: () => context.push('/account/language'),
                  ),
                  AppListRow(
                    icon: AppIcons.bell,
                    title: l10n.accountNotifications,
                    showDivider: false,
                    onTap: () => _showComingSoon(context),
                  ),
                ],
              ),
            ),
            const SizedBox(height: AppSpace.l),
            AppCard(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
              child: Column(
                children: [
                  if (isSignedIn)
                    AppListRow(
                      icon: AppIcons.lock,
                      title: l10n.accountChangePassword,
                      onTap: () => _showComingSoon(context),
                    ),
                  AppListRow(icon: AppIcons.document, title: l10n.accountTerms, onTap: () => _showComingSoon(context)),
                  AppListRow(
                    icon: AppIcons.help,
                    title: l10n.accountHelp,
                    showDivider: false,
                    onTap: () => _showComingSoon(context),
                  ),
                ],
              ),
            ),
            if (isSignedIn) ...[
              const SizedBox(height: AppSpace.l),
              AppButton(
                label: l10n.accountLogout,
                icon: AppIcons.logout,
                variant: AppButtonVariant.secondary,
                onPressed: () => _confirmLogout(context, ref),
              ),
              const SizedBox(height: AppSpace.m),
              Center(
                child: TextButton(
                  onPressed: () => _showComingSoon(context),
                  child: Text(
                    l10n.accountDelete,
                    style: AppText.b.copyWith(color: AppColors.rose, fontWeight: FontWeight.w600),
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

/// Signed-in customer: initials avatar, name, email and membership tier.
class _ProfileCard extends StatelessWidget {
  const _ProfileCard({required this.customer});

  final Customer customer;

  @override
  Widget build(BuildContext context) {
    return AppCard(
      child: Row(
        children: [
          Container(
            width: 56,
            height: 56,
            decoration: const BoxDecoration(color: AppColors.brandWash, shape: BoxShape.circle),
            alignment: Alignment.center,
            child: Text(customer.initials, style: AppText.l.copyWith(color: AppColors.brandInk)),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  customer.fullName,
                  style: AppText.m.copyWith(fontWeight: FontWeight.w700),
                  overflow: TextOverflow.ellipsis,
                ),
                if (customer.email != null)
                  Text(
                    customer.email!,
                    style: AppText.s.copyWith(color: AppColors.ink3),
                    overflow: TextOverflow.ellipsis,
                  ),
              ],
            ),
          ),
          if (customer.memberTierName != null)
            AppChip(icon: AppIcons.gem, tone: AppChipTone.gold, label: customer.memberTierName!),
        ],
      ),
    );
  }
}
