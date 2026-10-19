import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../features/account/account_screen.dart';
import '../features/account/language_screen.dart';
import '../features/auth/presentation/login_screen.dart';
import '../features/booking/domain/booking_models.dart';
import '../features/booking/presentation/booking_success_screen.dart';
import '../features/booking/presentation/guest_info_screen.dart';
import '../features/booking/presentation/hotel_rooms_screen.dart';
import '../features/booking/presentation/payment_screen.dart';
import '../features/booking/presentation/search_results_screen.dart';
import '../features/home/home_screen.dart';
import '../features/offers/offers_screen.dart';
import '../features/onboarding/welcome_screen.dart';
import '../features/shell/main_shell.dart';
import '../features/trips/trip_detail_screen.dart';
import '../features/trips/trips_screen.dart';
import 'providers.dart';

/// App navigation (Basic Design §5.1).
///
/// - First launch → `/welcome` (MB-01); afterwards the tab shell starts at `/home`.
/// - Tabs: `/home` · `/trips` · `/offers` · `/account`, each with its own stack.
/// - `/login?returnTo=…` (MB-02) opens above the tabs and returns to the caller after sign-in.
/// - Booking flow above the tabs: `/search?in&out&adults` (MB-04) → `/hotels/:id?in&out&adults` (MB-05/06)
///   → `/booking/guest` (MB-07, selection passed as `extra`) → `/booking/:code/payment` (MB-08)
///   → `/booking/:code/success` (MB-09). Trip detail: `/trips/:code` (MB-11).
///
/// Author: PH.Tuan · Created: 2026-10-18
final routerProvider = Provider<GoRouter>((ref) {
  final preferences = ref.watch(appPreferencesProvider);

  return GoRouter(
    initialLocation: preferences.isOnboarded ? '/home' : '/welcome',
    routes: [
      GoRoute(path: '/welcome', builder: (context, state) => const WelcomeScreen()),
      GoRoute(
        path: '/login',
        builder: (context, state) => LoginScreen(returnTo: state.uri.queryParameters['returnTo']),
      ),
      GoRoute(
        path: '/search',
        builder: (context, state) => SearchResultsScreen(stay: StayQuery.fromParameters(state.uri.queryParameters)),
      ),
      GoRoute(
        path: '/hotels/:id',
        builder: (context, state) => HotelRoomsScreen(
          hotelId: int.parse(state.pathParameters['id']!),
          stay: StayQuery.fromParameters(state.uri.queryParameters),
        ),
      ),
      GoRoute(
        path: '/booking/guest',
        redirect: (context, state) => state.extra is RoomSelection ? null : '/home',
        builder: (context, state) => GuestInfoScreen(selection: state.extra! as RoomSelection),
      ),
      GoRoute(
        path: '/booking/:code/payment',
        builder: (context, state) => PaymentScreen(bookingCode: state.pathParameters['code']!),
      ),
      GoRoute(
        path: '/booking/:code/success',
        builder: (context, state) => BookingSuccessScreen(bookingCode: state.pathParameters['code']!),
      ),
      GoRoute(
        path: '/trips/:code',
        builder: (context, state) => TripDetailScreen(bookingCode: state.pathParameters['code']!),
      ),
      StatefulShellRoute.indexedStack(
        builder: (context, state, navigationShell) => MainShell(navigationShell: navigationShell),
        branches: [
          StatefulShellBranch(routes: [GoRoute(path: '/home', builder: (context, state) => const HomeScreen())]),
          StatefulShellBranch(routes: [GoRoute(path: '/trips', builder: (context, state) => const TripsScreen())]),
          StatefulShellBranch(routes: [GoRoute(path: '/offers', builder: (context, state) => const OffersScreen())]),
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: '/account',
                builder: (context, state) => const AccountScreen(),
                routes: [GoRoute(path: 'language', builder: (context, state) => const LanguageScreen())],
              ),
            ],
          ),
        ],
      ),
    ],
  );
});
