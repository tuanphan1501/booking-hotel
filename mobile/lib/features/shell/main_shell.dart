import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../app/l10n/generated/app_localizations.dart';
import '../../app/theme/app_colors.dart';
import '../../app/theme/app_typography.dart';
import '../../widgets/app_icon.dart';

/// Bottom tab bar shell: Home (MB-03) · Trips (MB-10) · Offers (MB-14) · Account (MB-16).
///
/// - Each tab keeps its own navigation stack (go_router StatefulShellRoute).
/// - Tab bar follows the design: white, top border, line icons, brand color for the selected tab.
///
/// Author: PH.Tuan · Created: 2026-10-18
class MainShell extends StatelessWidget {
  /// Creates the shell around the current tab.
  const MainShell({required this.navigationShell, super.key});

  final StatefulNavigationShell navigationShell;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final tabs = [
      (AppIcons.home, l10n.tabHome),
      (AppIcons.list, l10n.tabTrips),
      (AppIcons.ticket, l10n.tabOffers),
      (AppIcons.user, l10n.tabAccount),
    ];

    return Scaffold(
      body: navigationShell,
      bottomNavigationBar: DecoratedBox(
        decoration: const BoxDecoration(
          color: Colors.white,
          border: Border(top: BorderSide(color: AppColors.line)),
        ),
        child: SafeArea(
          top: false,
          child: SizedBox(
            height: 60,
            child: Row(
              children: [
                for (var index = 0; index < tabs.length; index++)
                  Expanded(
                    child: _TabItem(
                      icon: tabs[index].$1,
                      label: tabs[index].$2,
                      isSelected: index == navigationShell.currentIndex,
                      onTap: () =>
                          navigationShell.goBranch(index, initialLocation: index == navigationShell.currentIndex),
                    ),
                  ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

/// One tab: 24 px line icon above an 11.5 px label.
class _TabItem extends StatelessWidget {
  const _TabItem({required this.icon, required this.label, required this.isSelected, required this.onTap});

  final String icon;
  final String label;
  final bool isSelected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final color = isSelected ? AppColors.brand : AppColors.ink3;

    return Semantics(
      selected: isSelected,
      button: true,
      label: label,
      excludeSemantics: true,
      child: InkWell(
        onTap: onTap,
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            AppIcon(icon, size: 24, color: color),
            const SizedBox(height: 3),
            Text(
              label,
              style: AppText.tab.copyWith(color: color, fontWeight: isSelected ? FontWeight.w600 : FontWeight.w500),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
      ),
    );
  }
}
