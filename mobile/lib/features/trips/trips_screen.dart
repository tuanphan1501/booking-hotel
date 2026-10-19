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
import '../../widgets/app_card.dart';
import '../../widgets/empty_state.dart';
import '../auth/application/auth_controller.dart';
import '../booking/data/booking_repository.dart';
import '../booking/domain/booking_models.dart';
import '../booking/presentation/booking_widgets.dart';

/// MB-10 My trips: "Upcoming" and "Past & cancelled" tabs from `GET /api/v1/bookings`.
///
/// - Guests see a sign-in prompt; an empty tab offers to find a room.
/// - Pull to refresh; tapping a trip opens MB-11.
///
/// Author: PH.Tuan · Created: 2026-10-18
class TripsScreen extends ConsumerWidget {
  /// Creates the trips tab.
  const TripsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final isSignedIn = ref.watch(authControllerProvider) is AuthSignedIn;

    if (!isSignedIn) {
      return Scaffold(
        appBar: AppBar(title: Text(l10n.tabTrips, style: AppText.xl), centerTitle: false),
        body: EmptyState(
          icon: Icons.luggage_outlined,
          title: l10n.tripsSignIn,
          message: l10n.tripsEmptyBody,
          actionLabel: l10n.homeSignIn,
          onAction: () => context.push('/login?returnTo=/trips'),
        ),
      );
    }

    return DefaultTabController(
      length: 2,
      child: Scaffold(
        appBar: AppBar(
          title: Text(l10n.tabTrips, style: AppText.xl),
          centerTitle: false,
          bottom: TabBar(
            labelColor: AppColors.brand,
            unselectedLabelColor: AppColors.ink3,
            indicatorColor: AppColors.brand,
            tabs: [Tab(text: l10n.tripsUpcoming), Tab(text: l10n.tripsPast)],
          ),
        ),
        body: const TabBarView(children: [_TripList(upcoming: true), _TripList(upcoming: false)]),
      ),
    );
  }
}

class _TripList extends ConsumerWidget {
  const _TripList({required this.upcoming});

  final bool upcoming;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final trips = ref.watch(myBookingsProvider(upcoming));

    return trips.when(
      loading: () => const Center(child: CircularProgressIndicator()),
      error: (error, _) => LoadError(error: error, onRetry: () => ref.invalidate(myBookingsProvider(upcoming))),
      data: (items) => RefreshIndicator(
        onRefresh: () => ref.refresh(myBookingsProvider(upcoming).future),
        child: items.isEmpty
            ? ListView(children: [
                SizedBox(
                  height: 420,
                  child: EmptyState(
                    icon: Icons.luggage_outlined,
                    title: l10n.tripsEmptyTitle,
                    message: l10n.tripsEmptyBody,
                    actionLabel: l10n.tripsFindRoom,
                    onAction: () => context.go('/home'),
                  ),
                ),
              ],)
            : ListView.separated(
                padding: const EdgeInsets.all(AppSpace.l),
                itemCount: items.length,
                separatorBuilder: (context, index) => const SizedBox(height: AppSpace.m),
                itemBuilder: (context, index) => _TripCard(booking: items[index]),
              ),
      ),
    );
  }
}

class _TripCard extends ConsumerWidget {
  const _TripCard({required this.booking});

  final Booking booking;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final languageCode = ref.watch(localeProvider).languageCode;
    final dates = DateFormatter(languageCode);

    return AppCard(
      padding: EdgeInsets.zero,
      child: InkWell(
        borderRadius: const BorderRadius.all(AppRadius.l),
        onTap: () => context.push('/trips/${booking.code}'),
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Row(
            children: [
              SizedBox(width: 84, child: BookingIllustration(booking.illustration, height: 84)),
              const SizedBox(width: AppSpace.m),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(booking.hotelName, style: AppText.b.copyWith(fontWeight: FontWeight.w700)),
                    const SizedBox(height: 2),
                    Text(
                      '${dates.short(booking.checkIn)} – ${dates.short(booking.checkOut)} · ${booking.roomTypeName}',
                      style: AppText.s.copyWith(color: AppColors.ink2),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 6),
                    Row(
                      children: [
                        BookingStatusChip(booking),
                        const Spacer(),
                        Text(MoneyFormatter(languageCode).format(booking.amounts.totalAmount), style: AppText.s.merge(AppText.price)),
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
