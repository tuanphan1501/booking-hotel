import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';

import '../../../app/l10n/generated/app_localizations.dart';
import '../../../app/theme/app_colors.dart';
import '../../../app/theme/app_spacing.dart';
import '../../../app/theme/app_typography.dart';
import '../../../core/network/api_exception.dart';
import '../../../widgets/app_button.dart';
import '../../../widgets/app_chip.dart';
import '../domain/booking_models.dart';

/// Building blocks shared by the booking screens (MB-04 … MB-11).
///
/// Author: PH.Tuan · Created: 2026-10-19

/// Placeholder illustration (assets/illustrations) used until real photos are served.
class BookingIllustration extends StatelessWidget {
  /// Creates the illustration.
  const BookingIllustration(this.name, {this.height = 140, this.radius = AppRadius.m, super.key});

  final String name;
  final double height;
  final Radius radius;

  @override
  Widget build(BuildContext context) {
    return ClipRRect(
      borderRadius: BorderRadius.all(radius),
      child: SizedBox(
        height: height,
        width: double.infinity,
        child: SvgPicture.asset('assets/illustrations/$name.svg', fit: BoxFit.cover),
      ),
    );
  }
}

/// Status chip of a booking with the design-system tone.
class BookingStatusChip extends StatelessWidget {
  /// Creates the chip.
  const BookingStatusChip(this.booking, {super.key});

  final Booking booking;

  @override
  Widget build(BuildContext context) {
    final tone = switch (booking.status) {
      BookingStatus.pendingPayment => AppChipTone.amber,
      BookingStatus.confirmed || BookingStatus.checkedIn => AppChipTone.brand,
      BookingStatus.checkedOut => AppChipTone.neutral,
      BookingStatus.cancelled || BookingStatus.noShow || BookingStatus.expired => AppChipTone.rose,
    };
    return AppChip(label: booking.statusLabel, tone: tone);
  }
}

/// Label / value row of a price breakdown.
class PriceRow extends StatelessWidget {
  /// Creates the row.
  const PriceRow({required this.label, required this.value, this.isTotal = false, super.key});

  final String label;
  final String value;
  final bool isTotal;

  @override
  Widget build(BuildContext context) {
    final style = isTotal ? AppText.m.copyWith(fontWeight: FontWeight.w700) : AppText.b.copyWith(color: AppColors.ink2);
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        children: [
          Expanded(child: Text(label, style: style)),
          Text(value, style: style.merge(AppText.price).copyWith(color: AppColors.ink)),
        ],
      ),
    );
  }
}

/// 3-step progress bar of the booking flow: Room · Details · Payment.
class BookingSteps extends StatelessWidget {
  /// Creates the bar; [current] is 0, 1 or 2.
  const BookingSteps({required this.current, super.key});

  final int current;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final labels = [l10n.bookingStepRoom, l10n.bookingStepGuest, l10n.bookingStepPay];

    return Row(
      children: [
        for (var index = 0; index < labels.length; index++)
          Expanded(
            child: Padding(
              padding: EdgeInsets.only(right: index < labels.length - 1 ? 6 : 0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    height: 4,
                    decoration: BoxDecoration(
                      color: index <= current ? AppColors.brand : AppColors.line,
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    labels[index],
                    style: AppText.xs.copyWith(
                      color: index <= current ? AppColors.brandInk : AppColors.ink3,
                      fontWeight: index == current ? FontWeight.w700 : FontWeight.w500,
                    ),
                  ),
                ],
              ),
            ),
          ),
      ],
    );
  }
}

/// Bottom bar holding the main action of a booking step.
class BookingBottomBar extends StatelessWidget {
  /// Creates the bar.
  const BookingBottomBar({required this.child, this.caption, super.key});

  final Widget child;
  final Widget? caption;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        color: Colors.white,
        border: Border(top: BorderSide(color: AppColors.line)),
      ),
      padding: const EdgeInsets.fromLTRB(AppSpace.l, AppSpace.m, AppSpace.l, AppSpace.m),
      child: SafeArea(
        top: false,
        child: Column(mainAxisSize: MainAxisSize.min, children: [if (caption != null) caption!, child]),
      ),
    );
  }
}

/// Error view with a retry button for failed loads.
class LoadError extends StatelessWidget {
  /// Creates the view.
  const LoadError({required this.error, required this.onRetry, super.key});

  final Object error;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final message = error is ApiException && (error as ApiException).code != ApiException.networkErrorCode
        ? (error as ApiException).message ?? l10n.errorUnknown
        : l10n.errorNetwork;

    return Center(
      child: Padding(
        padding: const EdgeInsets.all(AppSpace.xl),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.cloud_off_outlined, size: 44, color: AppColors.ink3),
            const SizedBox(height: AppSpace.m),
            Text(message, style: AppText.b.copyWith(color: AppColors.ink2), textAlign: TextAlign.center),
            const SizedBox(height: AppSpace.l),
            AppButton(label: l10n.retry, onPressed: onRetry, variant: AppButtonVariant.secondary, expand: false),
          ],
        ),
      ),
    );
  }
}
