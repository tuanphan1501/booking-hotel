/// Build-time configuration, passed with `--dart-define`.
///
/// - `API_BASE_URL`: backend API root. Default `http://127.0.0.1:8000/api/v1` works on a real
///   Android phone over USB after `adb reverse tcp:8000 tcp:8000`.
///
/// Author: PH.Tuan · Created: 2026-10-18
abstract final class AppConfig {
  /// Root URL of the Mobile App API (Laravel backend, routes/api_v1.php).
  static const String apiBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://127.0.0.1:8000/api/v1',
  );

  /// Locales with translations, in display order.
  static const List<String> supportedLanguageCodes = ['vi', 'ja', 'en'];

  /// Network timeouts (NFR: API p95 < 500 ms; generous limits for mobile networks).
  static const Duration connectTimeout = Duration(seconds: 10);
  static const Duration receiveTimeout = Duration(seconds: 20);
}
