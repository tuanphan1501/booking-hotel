import 'customer.dart';

/// Contract of the authentication data source.
///
/// - The app depends on this interface only; [ApiAuthRepository] implements it over HTTP and tests
///   can provide an in-memory fake.
/// - Failures are reported with `ApiException` (machine-readable `code` + translated `message`).
///
/// Author: PH.Tuan · Created: 2026-10-18
abstract interface class AuthRepository {
  /// Signs in with an email or phone number and stores the session on the device.
  ///
  /// - [rememberLogin] = false keeps the session in memory only (lost when the app closes).
  Future<Customer> login({
    required String loginIdentifier,
    required String password,
    required String deviceName,
    required bool rememberLogin,
  });

  /// Returns the signed-in customer, or null when the device has no valid session.
  Future<Customer?> currentCustomer();

  /// Ends the session on this device (server revocation is best effort).
  Future<void> logout();
}
