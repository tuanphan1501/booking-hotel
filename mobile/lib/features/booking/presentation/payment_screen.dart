import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../app/l10n/generated/app_localizations.dart';
import '../../../app/providers.dart';
import '../../../app/theme/app_colors.dart';
import '../../../app/theme/app_spacing.dart';
import '../../../app/theme/app_typography.dart';
import '../../../core/format/money_formatter.dart';
import '../../../core/network/api_exception.dart';
import '../../../widgets/app_button.dart';
import '../../../widgets/app_card.dart';
import '../../../widgets/app_icon.dart';
import '../data/booking_repository.dart';
import '../domain/booking_models.dart';
import 'booking_widgets.dart';

/// MB-08 Payment: room-hold countdown, BR-03 price breakdown and the payment method.
///
/// - The countdown follows `hold_expires_at`; at 0 the button is disabled and the hold-expired notice is shown.
/// - The backend uses a sandbox gateway for now: payment is approved immediately (shown in a notice).
///
/// Author: PH.Tuan · Created: 2026-10-19
class PaymentScreen extends ConsumerStatefulWidget {
  /// Creates the screen.
  const PaymentScreen({required this.bookingCode, super.key});

  final String bookingCode;

  @override
  ConsumerState<PaymentScreen> createState() => _PaymentScreenState();
}

class _PaymentScreenState extends ConsumerState<PaymentScreen> {
  static const _methods = ['VNPAY', 'MOMO', 'CARD'];

  String _method = _methods.first;
  bool _isPaying = false;
  Timer? _ticker;

  @override
  void initState() {
    super.initState();
    _ticker = Timer.periodic(const Duration(seconds: 1), (_) => setState(() {}));
  }

  @override
  void dispose() {
    _ticker?.cancel();
    super.dispose();
  }

  Future<void> _pay() async {
    setState(() => _isPaying = true);
    try {
      await ref.read(bookingRepositoryProvider).pay(widget.bookingCode, _method);
      ref.invalidate(myBookingsProvider);
      if (mounted) context.pushReplacement('/booking/${widget.bookingCode}/success');
    } on ApiException catch (exception) {
      if (!mounted) return;
      final l10n = AppLocalizations.of(context);
      ref.invalidate(bookingDetailProvider(widget.bookingCode));
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(exception.code == ApiException.networkErrorCode ? l10n.errorNetwork : exception.message ?? l10n.errorUnknown),
      ));
    } finally {
      if (mounted) setState(() => _isPaying = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final money = MoneyFormatter(ref.watch(localeProvider).languageCode);
    final booking = ref.watch(bookingDetailProvider(widget.bookingCode));

    return Scaffold(
      appBar: AppBar(title: Text(l10n.payTitle, style: AppText.m)),
      body: booking.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, _) => LoadError(error: error, onRetry: () => ref.invalidate(bookingDetailProvider(widget.bookingCode))),
        data: (data) {
          final remaining = data.holdExpiresAt?.difference(DateTime.now()) ?? Duration.zero;
          final isExpired = data.status != BookingStatus.pendingPayment || remaining.isNegative;

          return Column(
            children: [
              Expanded(
                child: ListView(
                  padding: const EdgeInsets.all(AppSpace.l),
                  children: [
                    const BookingSteps(current: 2),
                    const SizedBox(height: AppSpace.l),
                    _HoldNotice(remaining: remaining, isExpired: isExpired),
                    const SizedBox(height: AppSpace.l),
                    AppCard(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(data.hotelName, style: AppText.m),
                          Text('${data.roomTypeName} · ${data.ratePlanName ?? ''}', style: AppText.s.copyWith(color: AppColors.ink2)),
                          const Divider(height: 24, color: AppColors.line),
                          PriceRow(label: l10n.payRoomAmount(data.nights), value: money.format(data.amounts.roomAmount)),
                          PriceRow(label: l10n.payServiceCharge, value: money.format(data.amounts.serviceCharge)),
                          PriceRow(label: l10n.payTax, value: money.format(data.amounts.taxAmount)),
                          const Divider(height: 20, color: AppColors.line),
                          PriceRow(label: l10n.payTotal, value: money.format(data.outstandingAmount), isTotal: true),
                        ],
                      ),
                    ),
                    const SizedBox(height: AppSpace.xl),
                    Text(l10n.payMethod, style: AppText.l),
                    const SizedBox(height: AppSpace.s),
                    for (final method in _methods)
                      RadioListTile<String>(
                        value: method,
                        groupValue: _method,
                        onChanged: (value) => setState(() => _method = value!),
                        activeColor: AppColors.brand,
                        contentPadding: EdgeInsets.zero,
                        title: Text(switch (method) { 'VNPAY' => 'VNPay', 'MOMO' => 'MoMo', _ => l10n.payCard }, style: AppText.b),
                      ),
                    const SizedBox(height: AppSpace.s),
                    Text(l10n.paySandbox, style: AppText.xs.copyWith(color: AppColors.ink3)),
                  ],
                ),
              ),
              BookingBottomBar(
                child: AppButton(
                  label: l10n.payButton(money.format(data.outstandingAmount)),
                  icon: AppIcons.lock,
                  isLoading: _isPaying,
                  onPressed: isExpired ? null : _pay,
                ),
              ),
            ],
          );
        },
      ),
    );
  }
}

class _HoldNotice extends StatelessWidget {
  const _HoldNotice({required this.remaining, required this.isExpired});

  final Duration remaining;
  final bool isExpired;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final isUrgent = isExpired || remaining.inSeconds <= 120;
    final time = '${remaining.inMinutes.toString().padLeft(2, '0')}:${(remaining.inSeconds % 60).toString().padLeft(2, '0')}';

    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: isUrgent ? AppColors.roseWash : AppColors.amberWash,
        borderRadius: const BorderRadius.all(AppRadius.m),
      ),
      child: Row(
        children: [
          Icon(Icons.schedule, color: isUrgent ? AppColors.rose : AppColors.amber),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              isExpired ? l10n.payHoldExpired : l10n.payHold(time),
              style: AppText.s.copyWith(color: isUrgent ? AppColors.rose : AppColors.amberText, fontWeight: FontWeight.w600),
            ),
          ),
        ],
      ),
    );
  }
}
