import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../app/l10n/generated/app_localizations.dart';
import '../../../app/providers.dart';
import '../../../app/theme/app_colors.dart';
import '../../../app/theme/app_spacing.dart';
import '../../../app/theme/app_typography.dart';
import '../../../core/format/date_formatter.dart';
import '../../../widgets/app_button.dart';
import '../../../widgets/app_card.dart';
import '../data/booking_repository.dart';
import 'booking_widgets.dart';

/// MB-09 Booking confirmed: code, stay summary and links to the trip or home.
///
/// Author: PH.Tuan · Created: 2026-10-19
class BookingSuccessScreen extends ConsumerWidget {
  /// Creates the screen.
  const BookingSuccessScreen({required this.bookingCode, super.key});

  final String bookingCode;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final dates = DateFormatter(ref.watch(localeProvider).languageCode);
    final booking = ref.watch(bookingDetailProvider(bookingCode));

    return Scaffold(
      body: SafeArea(
        child: booking.when(
          loading: () => const Center(child: CircularProgressIndicator()),
          error: (error, _) => LoadError(error: error, onRetry: () => ref.invalidate(bookingDetailProvider(bookingCode))),
          data: (data) => ListView(
            padding: const EdgeInsets.all(AppSpace.xl),
            children: [
              const SizedBox(height: AppSpace.xl),
              Center(
                child: Container(
                  width: 84,
                  height: 84,
                  decoration: const BoxDecoration(color: AppColors.brandWash, shape: BoxShape.circle),
                  child: const Icon(Icons.check_rounded, size: 48, color: AppColors.brand),
                ),
              ),
              const SizedBox(height: AppSpace.l),
              Text(l10n.successTitle, style: AppText.xl, textAlign: TextAlign.center),
              const SizedBox(height: 6),
              Text(l10n.successBody(data.guestEmail), style: AppText.b.copyWith(color: AppColors.ink2), textAlign: TextAlign.center),
              const SizedBox(height: AppSpace.xl),
              AppCard(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(l10n.successCode, style: AppText.xs.copyWith(color: AppColors.ink3)),
                    Text(data.code, style: AppText.xl.copyWith(letterSpacing: 1.5, color: AppColors.brandInk)),
                    const Divider(height: 24, color: AppColors.line),
                    Text(data.hotelName, style: AppText.m),
                    Text('${data.roomTypeName} · ${data.ratePlanName ?? ''}', style: AppText.s.copyWith(color: AppColors.ink2)),
                    Text(
                      '${dates.long(data.checkIn)} → ${dates.long(data.checkOut)}',
                      style: AppText.s.copyWith(color: AppColors.ink2),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: AppSpace.xl),
              AppButton(label: l10n.successViewTrip, onPressed: () => context.go('/trips/${data.code}')),
              const SizedBox(height: AppSpace.m),
              AppButton(label: l10n.successHome, variant: AppButtonVariant.secondary, onPressed: () => context.go('/home')),
            ],
          ),
        ),
      ),
    );
  }
}
