import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../app/l10n/generated/app_localizations.dart';
import '../../app/theme/app_typography.dart';
import '../../widgets/empty_state.dart';

/// MB-10 My trips — empty state until the bookings API (`GET /api/v1/bookings`) is implemented.
///
/// Author: PH.Tuan · Created: 2026-10-18
class TripsScreen extends StatelessWidget {
  /// Creates the trips tab.
  const TripsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);

    return Scaffold(
      appBar: AppBar(title: Text(l10n.tabTrips, style: AppText.xl), centerTitle: false),
      body: EmptyState(
        icon: Icons.luggage_outlined,
        title: l10n.tripsEmptyTitle,
        message: l10n.tripsEmptyBody,
        actionLabel: l10n.tripsFindRoom,
        onAction: () => context.go('/home'),
      ),
    );
  }
}
