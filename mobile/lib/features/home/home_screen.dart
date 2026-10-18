import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

import '../../app/l10n/generated/app_localizations.dart';
import '../../app/providers.dart';
import '../../app/theme/app_colors.dart';
import '../../app/theme/app_spacing.dart';
import '../../app/theme/app_typography.dart';
import '../../core/format/date_formatter.dart';
import '../../core/format/money_formatter.dart';
import '../../widgets/app_button.dart';
import '../../widgets/app_card.dart';
import '../../widgets/app_chip.dart';
import '../../widgets/app_icon.dart';
import '../auth/application/auth_controller.dart';

/// Hotel shown in the "Hotels in our chain" carousel.
///
/// Author: PH.Tuan · Created: 2026-10-18
class FeaturedHotel {
  /// Creates a featured hotel card.
  const FeaturedHotel({required this.name, required this.illustration, required this.fromPrice});

  final String name;

  /// Illustration file under assets/illustrations (placeholder until real photos are served).
  final String illustration;

  /// Lowest nightly price in VND.
  final int fromPrice;
}

/// MB-03 Home & search.
///
/// - Layout follows design/mobile/screens/MB-03.png: greeting + points chip + bell, search card,
///   "Offers for you" card, horizontal carousel of chain hotels.
/// - Dates default to tomorrow → +2 nights, 2 adults · 1 room.
/// - Offers and hotels use local sample data until `GET /api/v1/home` is implemented on the backend.
///
/// Author: PH.Tuan · Created: 2026-10-18
class HomeScreen extends ConsumerWidget {
  /// Creates the home screen.
  const HomeScreen({super.key});

  /// Sample hotels matching the seeded catalog (backend HotelCatalogSeeder).
  static const List<FeaturedHotel> _featuredHotels = [
    FeaturedHotel(name: 'Vivu Stay Đà Nẵng', illustration: 'sea', fromPrice: 1350000),
    FeaturedHotel(name: 'Vivu Stay Saigon', illustration: 'city', fromPrice: 1200000),
    FeaturedHotel(name: 'Vivu Stay Hội An', illustration: 'lantern', fromPrice: 980000),
  ];

  /// Default stay length shown in the search card.
  static const int _defaultNights = 2;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final languageCode = ref.watch(localeProvider).languageCode;
    final authState = ref.watch(authControllerProvider);
    final customer = authState is AuthSignedIn ? authState.customer : null;
    final moneyFormatter = MoneyFormatter(languageCode);
    final dateFormatter = DateFormatter(languageCode);

    final checkIn = DateUtils.dateOnly(DateTime.now()).add(const Duration(days: 1));
    final checkOut = checkIn.add(const Duration(days: _defaultNights));

    return Scaffold(
      body: SafeArea(
        child: ListView(
          padding: const EdgeInsets.fromLTRB(AppSpace.l, AppSpace.m, AppSpace.l, AppSpace.xl),
          children: [
            Row(
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(l10n.homeGreeting, style: AppText.b.copyWith(color: AppColors.ink3)),
                      if (customer != null)
                        Text(customer.displayName, style: AppText.l, overflow: TextOverflow.ellipsis),
                    ],
                  ),
                ),
                if (customer != null)
                  AppChip(
                    icon: AppIcons.gem,
                    tone: AppChipTone.gold,
                    label: l10n.homePoints(NumberFormat.decimalPattern(languageCode).format(customer.pointBalance)),
                    onTap: () => context.go('/account'),
                  )
                else
                  AppChip(
                    icon: AppIcons.user,
                    tone: AppChipTone.brand,
                    label: l10n.homeSignIn,
                    onTap: () => context.push('/login?returnTo=/home'),
                  ),
                IconButton(onPressed: () {}, icon: const AppIcon(AppIcons.bell, size: 24, color: AppColors.ink)),
              ],
            ),
            const SizedBox(height: AppSpace.l),
            AppCard(
              padding: const EdgeInsets.fromLTRB(14, 6, 14, 14),
              child: Column(
                children: [
                  _SearchRow(icon: AppIcons.pin, label: l10n.searchDestination, value: 'Đà Nẵng'),
                  _SearchRow(
                    icon: AppIcons.calendar,
                    label: l10n.searchDates,
                    value: '${dateFormatter.short(checkIn)} – ${dateFormatter.short(checkOut)}',
                    trailing: AppChip(label: l10n.searchNights(_defaultNights)),
                  ),
                  _SearchRow(
                    icon: AppIcons.users,
                    label: l10n.searchGuests,
                    value: l10n.searchGuestsValue(2, 1),
                  ),
                  const SizedBox(height: AppSpace.m),
                  AppButton(label: l10n.searchSubmit, icon: AppIcons.search, onPressed: () {}),
                ],
              ),
            ),
            const SizedBox(height: AppSpace.xl),
            Text(l10n.homeOffers, style: AppText.l),
            const SizedBox(height: AppSpace.m),
            _OfferCard(
              title: l10n.homeEarlyBirdTitle,
              body: l10n.homeEarlyBirdBody,
              onTap: () => context.go('/offers'),
            ),
            const SizedBox(height: AppSpace.xl),
            Row(
              children: [
                Expanded(child: Text(l10n.homeChainHotels, style: AppText.l)),
                GestureDetector(
                  onTap: () {},
                  child: Text(
                    l10n.homeSeeAll,
                    style: AppText.b.copyWith(color: AppColors.brand, fontWeight: FontWeight.w600),
                  ),
                ),
              ],
            ),
            const SizedBox(height: AppSpace.m),
            SizedBox(
              height: 140,
              child: ListView.separated(
                scrollDirection: Axis.horizontal,
                itemCount: _featuredHotels.length,
                separatorBuilder: (context, index) => const SizedBox(width: AppSpace.m),
                itemBuilder: (context, index) {
                  final hotel = _featuredHotels[index];
                  return _HotelCard(hotel: hotel, priceLabel: l10n.priceFrom(moneyFormatter.format(hotel.fromPrice)));
                },
              ),
            ),
          ],
        ),
      ),
    );
  }
}

/// One row of the search card: line icon, small grey label, bold value, optional trailing chip.
class _SearchRow extends StatelessWidget {
  const _SearchRow({required this.icon, required this.label, required this.value, this.trailing});

  final String icon;
  final String label;
  final String value;
  final Widget? trailing;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: () {},
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 10),
        decoration: const BoxDecoration(border: Border(bottom: BorderSide(color: AppColors.line2))),
        child: Row(
          children: [
            AppIcon(icon, size: 22, color: AppColors.ink),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(label, style: AppText.s.copyWith(color: AppColors.ink3)),
                  const SizedBox(height: 2),
                  Text(value, style: AppText.m.copyWith(fontWeight: FontWeight.w700)),
                ],
              ),
            ),
            if (trailing != null) trailing!,
          ],
        ),
      ),
    );
  }
}

/// Promotion card on a brand-wash background with a round icon (design: offer card of MB-03).
class _OfferCard extends StatelessWidget {
  const _OfferCard({required this.title, required this.body, required this.onTap});

  final String title;
  final String body;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: AppColors.brandWash,
      borderRadius: const BorderRadius.all(AppRadius.l),
      child: InkWell(
        borderRadius: const BorderRadius.all(AppRadius.l),
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Row(
            children: [
              Container(
                width: 44,
                height: 44,
                decoration: const BoxDecoration(color: Colors.white, shape: BoxShape.circle),
                alignment: Alignment.center,
                child: const AppIcon(AppIcons.percent, color: AppColors.brand),
              ),
              const SizedBox(width: AppSpace.m),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(title, style: AppText.b.copyWith(fontWeight: FontWeight.w500)),
                    Text(body, style: AppText.s.copyWith(color: AppColors.ink2)),
                  ],
                ),
              ),
              const AppIcon(AppIcons.chevronRight, color: AppColors.ink),
            ],
          ),
        ),
      ),
    );
  }
}

/// Hotel card of the horizontal carousel (illustration, name, "from" price).
class _HotelCard extends StatelessWidget {
  const _HotelCard({required this.hotel, required this.priceLabel});

  final FeaturedHotel hotel;
  final String priceLabel;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: 150,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          ClipRRect(
            borderRadius: const BorderRadius.all(AppRadius.m),
            child: SizedBox(
              height: 96,
              width: 150,
              child: SvgPicture.asset('assets/illustrations/${hotel.illustration}.svg', fit: BoxFit.cover),
            ),
          ),
          const SizedBox(height: 8),
          Text(
            hotel.name,
            style: AppText.s.copyWith(fontWeight: FontWeight.w600),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
          Text(priceLabel, style: AppText.xs.copyWith(color: AppColors.ink3)),
        ],
      ),
    );
  }
}
