import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../features/account/account_screen.dart';
import '../features/account/language_screen.dart';
import '../features/auth/presentation/login_screen.dart';
import '../features/home/home_screen.dart';
import '../features/offers/offers_screen.dart';
import '../features/onboarding/welcome_screen.dart';
import '../features/shell/main_shell.dart';
import '../features/trips/trips_screen.dart';
import 'providers.dart';

/// App navigation (Basic Design §5.1).
///
/// - First launch → `/welcome` (MB-01); afterwards the tab shell starts at `/home`.
/// - Tabs: `/home` · `/trips` · `/offers` · `/account`, each with its own stack.
/// - `/login?returnTo=…` (MB-02) opens above the tabs and returns to the caller after sign-in.
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
