import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/l10n/generated/app_localizations.dart';
import '../../app/providers.dart';
import '../../app/theme/app_colors.dart';
import '../../app/theme/app_spacing.dart';
import '../../app/theme/app_typography.dart';
import '../../core/format/date_formatter.dart';
import '../../core/format/money_formatter.dart';
import '../../core/network/api_exception.dart';
import '../../widgets/app_button.dart';
import '../../widgets/app_card.dart';
import '../booking/data/booking_repository.dart';
import '../booking/domain/booking_models.dart';
import '../booking/presentation/booking_widgets.dart';

/// MB-11 Trip detail (code, stay, guest, price) with MB-11c cancel confirmation sheet.
///
/// - PENDING_PAYMENT trips show "Pay now" (back to MB-08).
///
/// Author: PH.Tuan · Created: 2026-10-19
class TripDetailScreen extends ConsumerWidget {
  /// Creates the screen.
  const TripDetailScreen({required this.bookingCode, super.key});

  final String bookingCode;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final languageCode = ref.watch(localeProvider).languageCode;
    final money = MoneyFormatter(languageCode);
    final dates = DateFormatter(languageCode);
    final booking = ref.watch(bookingDetailProvider(bookingCode));

    return Scaffold(
      appBar: AppBar(title: Text(l10n.tripDetailTitle, style: AppText.m)),
      body: booking.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, _) => LoadError(error: error, onRetry: () => ref.invalidate(bookingDetailProvider(bookingCode))),
        data: (data) => ListView(
          padding: const EdgeInsets.all(AppSpace.l),
          children: [
            BookingIllustration(data.illustration, height: 150),
            const SizedBox(height: AppSpace.l),
            Row(
              children: [
                Expanded(child: Text(data.hotelName, style: AppText.l)),
                BookingStatusChip(data),
              ],
            ),
            const SizedBox(height: 4),
            Text('${l10n.successCode}: ${data.code}', style: AppText.s.copyWith(color: AppColors.ink3)),
            const SizedBox(height: AppSpace.l),
            AppCard(
              child: Column(
                children: [
                  _InfoRow(label: l10n.bookingStepRoom, value: '${data.roomTypeName} · ${data.ratePlanName ?? ''}'),
                  _InfoRow(label: l10n.searchDates, value: '${dates.long(data.checkIn)} → ${dates.long(data.checkOut)}'),
                  _InfoRow(label: l10n.tripGuest, value: '${data.guestName} · ${data.guestPhone}'),
                  if (data.roomNumber != null) _InfoRow(label: l10n.tripRoom, value: data.roomNumber!),
                  if (data.estimatedArrival != null) _InfoRow(label: l10n.guestArrival, value: data.estimatedArrival!),
                ],
              ),
            ),
            const SizedBox(height: AppSpace.l),
            AppCard(
              child: Column(
                children: [
                  PriceRow(label: l10n.payRoomAmount(data.nights), value: money.format(data.amounts.roomAmount)),
                  PriceRow(label: '${l10n.payServiceCharge} + ${l10n.payTax}', value: money.format(data.amounts.serviceCharge + data.amounts.taxAmount)),
                  const Divider(height: 20, color: AppColors.line),
                  PriceRow(label: l10n.guestSubtotal, value: money.format(data.amounts.totalAmount), isTotal: true),
                  PriceRow(label: l10n.tripPaid, value: money.format(data.paidAmount)),
                ],
              ),
            ),
            const SizedBox(height: AppSpace.xl),
            if (data.status == BookingStatus.pendingPayment) ...[
              AppButton(label: l10n.tripPayNow, onPressed: () => context.push('/booking/${data.code}/payment')),
              const SizedBox(height: AppSpace.m),
            ],
            if (data.canCancel)
              AppButton(label: l10n.tripCancel, variant: AppButtonVariant.danger, onPressed: () => _confirmCancel(context, ref)),
          ],
        ),
      ),
    );
  }

  Future<void> _confirmCancel(BuildContext context, WidgetRef ref) async {
    final l10n = AppLocalizations.of(context);
    final confirmed = await showModalBottomSheet<bool>(
      context: context,
      showDragHandle: true,
      builder: (sheetContext) => SafeArea(
        child: Padding(
          padding: const EdgeInsets.fromLTRB(AppSpace.xl, 0, AppSpace.xl, AppSpace.l),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Text(l10n.tripCancelTitle, style: AppText.l),
              const SizedBox(height: AppSpace.s),
              Text(l10n.tripCancelBody, style: AppText.b.copyWith(color: AppColors.ink2)),
              const SizedBox(height: AppSpace.xl),
              AppButton(label: l10n.tripCancelConfirm, variant: AppButtonVariant.danger, onPressed: () => Navigator.pop(sheetContext, true)),
              const SizedBox(height: AppSpace.m),
              AppButton(label: l10n.tripKeep, variant: AppButtonVariant.secondary, onPressed: () => Navigator.pop(sheetContext, false)),
            ],
          ),
        ),
      ),
    );
    if (confirmed != true || !context.mounted) return;

    try {
      await ref.read(bookingRepositoryProvider).cancel(bookingCode);
      ref
        ..invalidate(bookingDetailProvider(bookingCode))
        ..invalidate(myBookingsProvider);
      if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(l10n.tripCancelled)));
    } on ApiException catch (exception) {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(exception.message ?? l10n.errorUnknown)));
      }
    }
  }
}

class _InfoRow extends StatelessWidget {
  const _InfoRow({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(width: 110, child: Text(label, style: AppText.s.copyWith(color: AppColors.ink3))),
          Expanded(child: Text(value, style: AppText.b)),
        ],
      ),
    );
  }
}
