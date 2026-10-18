import 'package:flutter/foundation.dart' show defaultTargetPlatform, kIsWeb;

import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../app/providers.dart';
import '../domain/customer.dart';

/// Session state of the app user.
///
/// Author: PH.Tuan · Created: 2026-10-18
sealed class AuthState {
  const AuthState();
}

/// The saved session is still being checked at startup.
class AuthChecking extends AuthState {
  const AuthChecking();
}

/// Browsing as a guest (allowed up to MB-06; booking requires sign-in).
class AuthGuest extends AuthState {
  const AuthGuest();
}

/// Signed in as [customer].
class AuthSignedIn extends AuthState {
  const AuthSignedIn(this.customer);

  final Customer customer;
}

/// App-wide authentication state and actions (MB-02 sign-in, MB-16 sign-out).
final authControllerProvider = NotifierProvider<AuthController, AuthState>(AuthController.new);

/// Holds the current [AuthState] and performs sign-in / sign-out.
///
/// Author: PH.Tuan · Created: 2026-10-18
class AuthController extends Notifier<AuthState> {
  @override
  AuthState build() {
    Future.microtask(_restoreSession);
    return const AuthChecking();
  }

  /// Signs in and switches to [AuthSignedIn].
  ///
  /// Throws ApiException on failure so the screen can show the server's message.
  Future<void> login({required String loginIdentifier, required String password, required bool rememberLogin}) async {
    await ref.read(appPreferencesProvider).setRememberLogin(rememberLogin);

    final customer = await ref.read(authRepositoryProvider).login(
          loginIdentifier: loginIdentifier,
          password: password,
          deviceName: _deviceName(),
          rememberLogin: rememberLogin,
        );

    state = AuthSignedIn(customer);
  }

  /// Signs out on this device.
  Future<void> logout() async {
    await ref.read(authRepositoryProvider).logout();
    state = const AuthGuest();
  }

  /// Reloads the customer profile (e.g. after returning to the Account tab).
  Future<void> refreshProfile() => _restoreSession();

  /// Called by the API client when the refresh token is rejected.
  void onSessionExpired() => state = const AuthGuest();

  Future<void> _restoreSession() async {
    try {
      final customer = await ref.read(authRepositoryProvider).currentCustomer();
      state = customer == null ? const AuthGuest() : AuthSignedIn(customer);
    } on Exception {
      // Offline at startup: keep browsing as a guest; the session is retried on the next launch.
      state = const AuthGuest();
    }
  }

  /// Label grouping this device's tokens on the server, e.g. "Vivu Stay · android".
  String _deviceName() => 'Vivu Stay · ${kIsWeb ? 'web' : defaultTargetPlatform.name}';
}
