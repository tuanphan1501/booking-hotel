import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../app/l10n/generated/app_localizations.dart';
import '../../../app/providers.dart';
import '../../../app/theme/app_colors.dart';
import '../../../app/theme/app_spacing.dart';
import '../../../app/theme/app_typography.dart';
import '../../../core/format/date_formatter.dart';
import '../../../core/format/money_formatter.dart';
import '../../../widgets/app_card.dart';
import '../../../widgets/app_chip.dart';
import '../../../widgets/app_icon.dart';
import '../../../widgets/empty_state.dart';
import '../data/booking_repository.dart';
import '../domain/booking_models.dart';
import 'booking_widgets.dart';

/// MB-04 Search results: hotels of the chain with availability and the lowest nightly price.
///
/// - Hotels with rooms come first, cheapest first; sold-out hotels are shown greyed out (MB-04e when none is free).
/// - Tapping a hotel opens MB-05 with the same stay.
///
/// Author: PH.Tuan · Created: 2026-10-19
class SearchResultsScreen extends ConsumerWidget {
  /// Creates the screen for [stay].
  const SearchResultsScreen({required this.stay, super.key});

  final StayQuery stay;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final languageCode = ref.watch(localeProvider).languageCode;
    final dates = DateFormatter(languageCode);
    final hotels = ref.watch(hotelSearchProvider(stay));

    return Scaffold(
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(l10n.searchTitle, style: AppText.m),
            Text(
              l10n.searchSummary('${dates.short(stay.checkIn)} – ${dates.short(stay.checkOut)}', stay.adults),
              style: AppText.xs.copyWith(color: AppColors.ink3),
            ),
          ],
        ),
      ),
      body: hotels.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, _) => LoadError(error: error, onRetry: () => ref.invalidate(hotelSearchProvider(stay))),
        data: (items) {
          if (items.every((hotel) => hotel.roomsLeft == 0)) {
            return EmptyState(
              icon: Icons.event_busy_outlined,
              title: l10n.searchEmptyTitle,
              message: l10n.searchEmptyBody,
              actionLabel: l10n.searchChangeDates,
              onAction: () => context.pop(),
            );
          }

          return ListView.separated(
            padding: const EdgeInsets.all(AppSpace.l),
            itemCount: items.length,
            separatorBuilder: (context, index) => const SizedBox(height: AppSpace.l),
            itemBuilder: (context, index) => _HotelCard(
              hotel: items[index],
              money: MoneyFormatter(languageCode),
              onTap: items[index].roomsLeft == 0 ? null : () => context.push('/hotels/${items[index].id}?${stay.routeQuery}'),
            ),
          );
        },
      ),
    );
  }
}

class _HotelCard extends StatelessWidget {
  const _HotelCard({required this.hotel, required this.money, required this.onTap});

  final HotelSummary hotel;
  final MoneyFormatter money;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final isSoldOut = hotel.roomsLeft == 0;

    return Opacity(
      opacity: isSoldOut ? 0.55 : 1,
      child: AppCard(
        padding: EdgeInsets.zero,
        child: InkWell(
          borderRadius: const BorderRadius.all(AppRadius.l),
          onTap: onTap,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              BookingIllustration(hotel.illustration, height: 150, radius: AppRadius.l),
              Padding(
                padding: const EdgeInsets.all(14),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Expanded(child: Text(hotel.name, style: AppText.m)),
                        if (hotel.starRating != null)
                          Text('★' * hotel.starRating!, style: AppText.s.copyWith(color: AppColors.goldHi)),
                      ],
                    ),
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        const AppIcon(AppIcons.pin, size: 16, color: AppColors.ink3),
                        const SizedBox(width: 4),
                        Expanded(
                          child: Text(hotel.address, style: AppText.s.copyWith(color: AppColors.ink3), maxLines: 1, overflow: TextOverflow.ellipsis),
                        ),
                      ],
                    ),
                    const SizedBox(height: 10),
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        AppChip(label: l10n.reviewsCount(hotel.ratingAvg.toStringAsFixed(1), hotel.reviewCount), tone: AppChipTone.brand),
                        const Spacer(),
                        if (isSoldOut)
                          Text(l10n.searchSoldOut, style: AppText.s.copyWith(color: AppColors.rose, fontWeight: FontWeight.w600))
                        else
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.end,
                            children: [
                              Text(l10n.searchRoomsLeft(hotel.roomsLeft), style: AppText.xs.copyWith(color: AppColors.amber)),
                              Text.rich(TextSpan(children: [
                                TextSpan(text: money.format(hotel.fromPrice ?? 0), style: AppText.l.merge(AppText.price)),
                                TextSpan(text: ' ${l10n.perNight}', style: AppText.s.copyWith(color: AppColors.ink3)),
                              ])),
                            ],
                          ),
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
