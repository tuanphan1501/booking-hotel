import 'package:flutter_secure_storage/flutter_secure_storage.dart';

/// Access/refresh token pair stored on the device.
///
/// Author: PH.Tuan · Created: 2026-10-18
class StoredTokens {
  /// Creates a token pair.
  const StoredTokens({required this.accessToken, required this.refreshToken});

  /// Bearer token for API calls (short-lived).
  final String accessToken;

  /// Token exchanged for a new pair at `/auth/refresh` (long-lived, rotated on each use).
  final String refreshToken;
}

/// Keeps the API tokens in the platform's secure storage (Keychain / Android Keystore).
///
/// - When "keep me signed in" is off, tokens live in memory only and are lost when the app closes.
///
/// Author: PH.Tuan · Created: 2026-10-18
class TokenStorage {
  /// Creates the storage; [secureStorage] can be replaced in tests.
  TokenStorage({FlutterSecureStorage? secureStorage})
      : _secureStorage =
            secureStorage ?? const FlutterSecureStorage(aOptions: AndroidOptions(encryptedSharedPreferences: true));

  static const String _accessTokenKey = 'auth.access_token';
  static const String _refreshTokenKey = 'auth.refresh_token';

  final FlutterSecureStorage _secureStorage;

  /// Tokens kept for the current app run only.
  StoredTokens? _memoryTokens;

  /// Reads the saved tokens, if any.
  Future<StoredTokens?> read() async {
    if (_memoryTokens != null) return _memoryTokens;

    final accessToken = await _secureStorage.read(key: _accessTokenKey);
    final refreshToken = await _secureStorage.read(key: _refreshTokenKey);
    if (accessToken == null || refreshToken == null) return null;

    return _memoryTokens = StoredTokens(accessToken: accessToken, refreshToken: refreshToken);
  }

  /// Saves a new token pair.
  ///
  /// - [persist] = false keeps the tokens in memory only ("keep me signed in" unchecked).
  Future<void> save(StoredTokens tokens, {bool persist = true}) async {
    _memoryTokens = tokens;

    if (persist) {
      await _secureStorage.write(key: _accessTokenKey, value: tokens.accessToken);
      await _secureStorage.write(key: _refreshTokenKey, value: tokens.refreshToken);
    } else {
      await _deletePersisted();
    }
  }

  /// Removes every saved token (sign-out or expired session).
  Future<void> clear() async {
    _memoryTokens = null;
    await _deletePersisted();
  }

  Future<void> _deletePersisted() async {
    await _secureStorage.delete(key: _accessTokenKey);
    await _secureStorage.delete(key: _refreshTokenKey);
  }
}
