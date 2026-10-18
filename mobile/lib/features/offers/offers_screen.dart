import 'package:flutter/material.dart';

import '../../app/l10n/generated/app_localizations.dart';
import '../../app/theme/app_typography.dart';
import '../../widgets/empty_state.dart';

/// MB-14 Voucher wallet — empty state until the vouchers API (`GET /api/v1/me/vouchers`) is implemented.
///
/// Author: PH.Tuan · Created: 2026-10-18
class OffersScreen extends StatelessWidget {
  /// Creates the offers tab.
  const OffersScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);

    return Scaffold(
      appBar: AppBar(title: Text(l10n.tabOffers, style: AppText.xl), centerTitle: false),
      body: EmptyState(
        icon: Icons.confirmation_number_outlined,
        title: l10n.offersEmptyTitle,
        message: l10n.offersEmptyBody,
      ),
    );
  }
}
