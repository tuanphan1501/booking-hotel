import '../../../core/network/api_client.dart';
import '../../../core/network/api_exception.dart';
import '../../../core/storage/token_storage.dart';
import '../domain/auth_repository.dart';
import '../domain/customer.dart';

/// [AuthRepository] backed by the Vivu Stay API (`/api/v1/auth/*`, `/api/v1/me`).
///
/// - Keeps the device's tokens in [TokenStorage] in sync with the server session.
///
/// Author: PH.Tuan · Created: 2026-10-18
class ApiAuthRepository implements AuthRepository {
  /// Creates the repository.
  ApiAuthRepository({required ApiClient apiClient, required TokenStorage tokenStorage})
      : _apiClient = apiClient,
        _tokenStorage = tokenStorage;

  final ApiClient _apiClient;
  final TokenStorage _tokenStorage;

  /// Signs in with an email or phone number and stores the returned tokens.
  ///
  /// - [rememberLogin] = false keeps the tokens in memory only.
  /// - Throws [ApiException] (e.g. INVALID_CREDENTIALS with `remaining_attempts`, LOGIN_THROTTLED).
  @override
  Future<Customer> login({
    required String loginIdentifier,
    required String password,
    required String deviceName,
    required bool rememberLogin,
  }) async {
    final response = await _apiClient.post(
      '/auth/login',
      body: {'login': loginIdentifier, 'password': password, 'device_name': deviceName},
    );
    final data = response['data'] as Map<String, dynamic>;

    await _tokenStorage.save(
      StoredTokens(accessToken: data['access_token'] as String, refreshToken: data['refresh_token'] as String),
      persist: rememberLogin,
    );

    return Customer.fromJson(data['user'] as Map<String, dynamic>);
  }

  /// Loads the signed-in customer, or returns null when the device has no session.
  ///
  /// - Expired access tokens are refreshed automatically by [ApiClient].
  @override
  Future<Customer?> currentCustomer() async {
    if (await _tokenStorage.read() == null) return null;

    try {
      final response = await _apiClient.get('/me');
      return Customer.fromJson(response['data'] as Map<String, dynamic>);
    } on ApiException catch (exception) {
      if (exception.isUnauthenticated) {
        await _tokenStorage.clear();
        return null;
      }
      rethrow;
    }
  }

  /// Signs out on this device: revokes the tokens on the server (best effort) and deletes them locally.
  @override
  Future<void> logout() async {
    try {
      await _apiClient.post('/auth/logout');
    } on ApiException {
      // The local session is removed even if the server cannot be reached.
    } finally {
      await _tokenStorage.clear();
    }
  }
}
