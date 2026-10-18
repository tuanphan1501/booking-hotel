import 'package:shared_preferences/shared_preferences.dart';

/// Small, non-sensitive app settings stored on the device.
///
/// Author: PH.Tuan · Created: 2026-10-18
class AppPreferences {
  /// Wraps an already-loaded [SharedPreferences] instance.
  AppPreferences(this._preferences);

  static const String _onboardedKey = 'app.onboarded';
  static const String _languageCodeKey = 'app.language_code';
  static const String _rememberLoginKey = 'auth.remember_login';

  final SharedPreferences _preferences;

  /// Whether the welcome screen (MB-01) was completed.
  bool get isOnboarded => _preferences.getBool(_onboardedKey) ?? false;

  /// Marks the welcome screen as completed.
  Future<void> setOnboarded() => _preferences.setBool(_onboardedKey, true);

  /// Language chosen by the user, or null to follow the device language.
  String? get languageCode => _preferences.getString(_languageCodeKey);

  /// Saves the chosen language ("vi", "ja" or "en").
  Future<void> setLanguageCode(String languageCode) => _preferences.setString(_languageCodeKey, languageCode);

  /// Whether the user chose "keep me signed in" (default true).
  bool get rememberLogin => _preferences.getBool(_rememberLoginKey) ?? true;

  /// Saves the "keep me signed in" choice.
  Future<void> setRememberLogin(bool remember) => _preferences.setBool(_rememberLoginKey, remember);
}
