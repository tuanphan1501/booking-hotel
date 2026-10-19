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
import '../../../widgets/app_button.dart';
import '../../../widgets/app_card.dart';
import '../../../widgets/app_chip.dart';
import '../../../widgets/app_icon.dart';
import '../../auth/application/auth_controller.dart';
import '../data/booking_repository.dart';
import '../domain/booking_models.dart';
import 'booking_widgets.dart';

/// MB-05 Hotel detail + MB-06 Room types & rate plans for the stay.
///
/// - Each rate plan shows breakfast / cancellation terms, price per night and the total incl. tax.
/// - "Select" opens MB-07; guests are sent to sign-in first and come back to this hotel.
///
/// Author: PH.Tuan · Created: 2026-10-19
class HotelRoomsScreen extends ConsumerWidget {
  /// Creates the screen.
  const HotelRoomsScreen({required this.hotelId, required this.stay, super.key});

  final int hotelId;
  final StayQuery stay;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final languageCode = ref.watch(localeProvider).languageCode;
    final rooms = ref.watch(hotelRoomsProvider((hotelId, stay)));

    return Scaffold(
      body: rooms.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, _) => SafeArea(child: LoadError(error: error, onRetry: () => ref.invalidate(hotelRoomsProvider((hotelId, stay))))),
        data: (data) => CustomScrollView(
          slivers: [
            SliverAppBar(
              pinned: true,
              expandedHeight: 220,
              backgroundColor: Colors.white,
              flexibleSpace: FlexibleSpaceBar(
                background: BookingIllustration(data.hotel.illustration, height: 260, radius: Radius.zero),
              ),
            ),
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(AppSpace.l, AppSpace.l, AppSpace.l, AppSpace.s),
              sliver: SliverToBoxAdapter(child: _HotelHeader(hotel: data.hotel, stay: stay, languageCode: languageCode)),
            ),
            SliverPadding(
              padding: const EdgeInsets.symmetric(horizontal: AppSpace.l),
              sliver: SliverToBoxAdapter(child: Text(l10n.roomsTitle, style: AppText.l)),
            ),
            SliverPadding(
              padding: const EdgeInsets.all(AppSpace.l),
              sliver: SliverList.separated(
                itemCount: data.roomTypes.length,
                separatorBuilder: (context, index) => const SizedBox(height: AppSpace.l),
                itemBuilder: (context, index) => _RoomTypeCard(
                  roomType: data.roomTypes[index],
                  money: MoneyFormatter(languageCode),
                  onSelect: (ratePlan) => _select(
                    context,
                    ref,
                    RoomSelection(hotel: data.hotel, roomType: data.roomTypes[index], ratePlan: ratePlan, stay: stay),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _select(BuildContext context, WidgetRef ref, RoomSelection selection) {
    if (ref.read(authControllerProvider) is! AuthSignedIn) {
      final returnTo = Uri.encodeComponent('/hotels/$hotelId?${stay.routeQuery}');
      context.push('/login?returnTo=$returnTo');
      return;
    }
    context.push('/booking/guest', extra: selection);
  }
}

class _HotelHeader extends StatelessWidget {
  const _HotelHeader({required this.hotel, required this.stay, required this.languageCode});

  final HotelSummary hotel;
  final StayQuery stay;
  final String languageCode;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final dates = DateFormatter(languageCode);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(hotel.name, style: AppText.xl),
        const SizedBox(height: 6),
        Row(
          children: [
            AppChip(label: l10n.reviewsCount(hotel.ratingAvg.toStringAsFixed(1), hotel.reviewCount), tone: AppChipTone.brand),
            const SizedBox(width: 8),
            AppChip(label: '${dates.short(stay.checkIn)} – ${dates.short(stay.checkOut)} · ${l10n.searchNights(stay.nights)}'),
          ],
        ),
        const SizedBox(height: 10),
        Row(
          children: [
            const AppIcon(AppIcons.pin, size: 16, color: AppColors.ink3),
            const SizedBox(width: 4),
            Expanded(child: Text(hotel.address, style: AppText.s.copyWith(color: AppColors.ink2))),
          ],
        ),
        if (hotel.description != null) ...[
          const SizedBox(height: 10),
          Text(hotel.description!, style: AppText.b.copyWith(color: AppColors.ink2)),
        ],
        const SizedBox(height: AppSpace.l),
      ],
    );
  }
}

class _RoomTypeCard extends StatelessWidget {
  const _RoomTypeCard({required this.roomType, required this.money, required this.onSelect});

  final RoomTypeOffer roomType;
  final MoneyFormatter money;
  final ValueChanged<RatePlanOffer> onSelect;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final isSoldOut = roomType.roomsLeft == 0;

    return AppCard(
      padding: EdgeInsets.zero,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          BookingIllustration(roomType.illustration, height: 130, radius: AppRadius.l),
          Padding(
            padding: const EdgeInsets.fromLTRB(14, 12, 14, 4),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(roomType.name, style: AppText.m),
                const SizedBox(height: 4),
                Wrap(
                  spacing: 6,
                  runSpacing: 6,
                  children: [
                    if (roomType.areaM2 != null) AppChip(label: l10n.roomsArea(roomType.areaM2!.toStringAsFixed(0))),
                    AppChip(label: l10n.roomsMaxGuests(roomType.maxAdults)),
                    if (isSoldOut)
                      AppChip(label: l10n.searchSoldOut, tone: AppChipTone.rose)
                    else if (roomType.roomsLeft <= 3)
                      AppChip(label: l10n.searchRoomsLeft(roomType.roomsLeft), tone: AppChipTone.amber),
                  ],
                ),
              ],
            ),
          ),
          for (final ratePlan in roomType.ratePlans)
            Container(
              margin: const EdgeInsets.fromLTRB(14, 8, 14, 0),
              padding: const EdgeInsets.only(top: 10),
              decoration: const BoxDecoration(border: Border(top: BorderSide(color: AppColors.line2))),
              child: Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(ratePlan.name, style: AppText.b.copyWith(fontWeight: FontWeight.w600)),
                        Text(
                          ratePlan.breakfastIncluded ? l10n.roomsBreakfast : l10n.roomsNoBreakfast,
                          style: AppText.xs.copyWith(color: AppColors.ink2),
                        ),
                        Text(
                          ratePlan.refundable ? l10n.roomsRefundable : l10n.roomsNonRefundable,
                          style: AppText.xs.copyWith(color: ratePlan.refundable ? AppColors.brandInk : AppColors.amber),
                        ),
                        const SizedBox(height: 4),
                        Text.rich(TextSpan(children: [
                          TextSpan(text: money.format(ratePlan.pricePerNight), style: AppText.m.merge(AppText.price)),
                          TextSpan(text: ' ${l10n.perNight}', style: AppText.xs.copyWith(color: AppColors.ink3)),
                        ])),
                        Text(l10n.roomsTotal(money.format(ratePlan.quote.totalAmount)), style: AppText.xs.copyWith(color: AppColors.ink3)),
                      ],
                    ),
                  ),
                  AppButton(label: l10n.roomsSelect, expand: false, onPressed: isSoldOut ? null : () => onSelect(ratePlan)),
                ],
              ),
            ),
          const SizedBox(height: 14),
        ],
      ),
    );
  }
}
