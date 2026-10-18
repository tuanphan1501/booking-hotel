import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../app/l10n/generated/app_localizations.dart';

/// Bottom tab bar shell: Home (MB-03) · Trips (MB-10) · Offers (MB-14) · Account (MB-16).
///
/// - Each tab keeps its own navigation stack (go_router StatefulShellRoute).
///
/// Author: PH.Tuan · Created: 2026-10-18
class MainShell extends StatelessWidget {
  /// Creates the shell around the current tab.
  const MainShell({required this.navigationShell, super.key});

  final StatefulNavigationShell navigationShell;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);

    return Scaffold(
      body: navigationShell,
      bottomNavigationBar: NavigationBar(
        selectedIndex: navigationShell.currentIndex,
        onDestinationSelected: (index) =>
            navigationShell.goBranch(index, initialLocation: index == navigationShell.currentIndex),
        destinations: [
          NavigationDestination(
            icon: const Icon(Icons.home_outlined),
            selectedIcon: const Icon(Icons.home),
            label: l10n.tabHome,
          ),
          NavigationDestination(
            icon: const Icon(Icons.luggage_outlined),
            selectedIcon: const Icon(Icons.luggage),
            label: l10n.tabTrips,
          ),
          NavigationDestination(
            icon: const Icon(Icons.confirmation_number_outlined),
            selectedIcon: const Icon(Icons.confirmation_number),
            label: l10n.tabOffers,
          ),
          NavigationDestination(
            icon: const Icon(Icons.person_outline),
            selectedIcon: const Icon(Icons.person),
            label: l10n.tabAccount,
          ),
        ],
      ),
    );
  }
}
