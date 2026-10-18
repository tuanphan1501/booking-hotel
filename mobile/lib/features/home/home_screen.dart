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
import '../../core/format/money_formatter.dart';
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
/// - Search card: destination, dates (default tomorrow → +2 nights), guests & rooms.
/// - Offers and chain hotels use local sample data until `GET /api/v1/home` is implemented on the backend.
///
/// Author: PH.Tuan · Created: 2026-10-18
class HomeScreen extends ConsumerWidget {
  /// Creates the home screen.
  const HomeScreen({super.key});

  /// Sample hotels matching the seeded catalog (backend HotelCatalogSeeder).
  static const List<FeaturedHotel> _featuredHotels = [
    FeaturedHotel(name: 'Vivu Stay Đà Nẵng', illustration: 'sea', fromPrice: 1200000),
    FeaturedHotel(name: 'Vivu Stay Saigon', illustration: 'city', fromPrice: 1200000),
    FeaturedHotel(name: 'Vivu Stay Hội An', illustration: 'lantern', fromPrice: 980000),
  ];

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final languageCode = ref.watch(localeProvider).languageCode;
    final authState = ref.watch(authControllerProvider);
    final customer = authState is AuthSignedIn ? authState.customer : null;
    final moneyFormatter = MoneyFormatter(languageCode);

    final checkIn = DateUtils.dateOnly(DateTime.now()).add(const Duration(days: 1));
    final checkOut = checkIn.add(const Duration(days: 2));
    final dateFormat = DateFormat.MMMEd(languageCode);

    return Scaffold(
      body: SafeArea(
        child: ListView(
          padding: const EdgeInsets.fromLTRB(AppSpace.l, AppSpace.s, AppSpace.l, AppSpace.xl),
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(
                    customer == null ? l10n.homeGreeting : l10n.homeGreetingName(customer.greetingName),
                    style: AppText.xl,
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
                if (customer != null)
                  Chip(
                    avatar: const Icon(Icons.diamond_outlined, size: 18, color: AppColors.gold),
                    label:
                        Text(l10n.homePoints(NumberFormat.decimalPattern(languageCode).format(customer.pointBalance))),
                    backgroundColor: AppColors.goldWash,
                    labelStyle: AppText.s.copyWith(color: AppColors.gold, fontWeight: FontWeight.w600),
                  ),
              ],
            ),
            const SizedBox(height: AppSpace.l),
            Card(
              child: Padding(
                padding: const EdgeInsets.all(14),
                child: Column(
                  children: [
                    _SearchRow(icon: Icons.place_outlined, label: l10n.searchDestination, value: 'Đà Nẵng'),
                    const Divider(),
                    _SearchRow(
                      icon: Icons.calendar_month_outlined,
                      label: l10n.searchDates,
                      value: '${dateFormat.format(checkIn)} – ${dateFormat.format(checkOut)}',
                      trailing: Chip(label: Text(l10n.searchNights(2))),
                    ),
                    const Divider(),
                    _SearchRow(
                      icon: Icons.people_outline,
                      label: l10n.searchGuests,
                      value: l10n.searchGuestsValue(2, 1),
                    ),
                    const SizedBox(height: AppSpace.m),
                    SizedBox(
                      width: double.infinity,
                      child: FilledButton.icon(
                        onPressed: () {},
                        icon: const Icon(Icons.search),
                        label: Text(l10n.searchSubmit),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: AppSpace.xl),
            Text(l10n.homeOffers, style: AppText.l),
            const SizedBox(height: AppSpace.m),
            Card(
              color: AppColors.brandWash,
              child: ListTile(
                contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                leading: const CircleAvatar(
                  backgroundColor: Colors.white,
                  child: Icon(Icons.percent, color: AppColors.brand),
                ),
                title: Text(l10n.homeEarlyBirdTitle, style: AppText.b.copyWith(fontWeight: FontWeight.w600)),
                subtitle: Text(l10n.homeEarlyBirdBody),
                trailing: const Icon(Icons.chevron_right),
                onTap: () => context.go('/offers'),
              ),
            ),
            const SizedBox(height: AppSpace.xl),
            Row(
              children: [
                Expanded(child: Text(l10n.homeChainHotels, style: AppText.l)),
                TextButton(onPressed: () {}, child: Text(l10n.homeSeeAll)),
              ],
            ),
            const SizedBox(height: AppSpace.s),
            SizedBox(
              height: 190,
              child: ListView.separated(
                scrollDirection: Axis.horizontal,
                itemCount: _featuredHotels.length,
                separatorBuilder: (_, __) => const SizedBox(width: AppSpace.m),
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

/// One row of the search card (icon, small label, value).
class _SearchRow extends StatelessWidget {
  const _SearchRow({required this.icon, required this.label, required this.value, this.trailing});

  final IconData icon;
  final String label;
  final String value;
  final Widget? trailing;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: () {},
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 8),
        child: Row(
          children: [
            Icon(icon, color: AppColors.ink2),
            const SizedBox(width: AppSpace.m),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(label, style: AppText.xs.copyWith(color: AppColors.ink3)),
                  Text(value, style: AppText.b.copyWith(fontWeight: FontWeight.w600)),
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

/// Hotel card of the horizontal carousel (4:3 illustration, name, "from" price).
class _HotelCard extends StatelessWidget {
  const _HotelCard({required this.hotel, required this.priceLabel});

  final FeaturedHotel hotel;
  final String priceLabel;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: 168,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          ClipRRect(
            borderRadius: const BorderRadius.all(AppRadius.m),
            child: AspectRatio(
              aspectRatio: 4 / 3,
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
