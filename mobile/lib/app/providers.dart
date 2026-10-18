import 'package:flutter/widgets.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../core/config/app_config.dart';
import '../core/network/api_client.dart';
import '../core/storage/app_preferences.dart';
import '../core/storage/token_storage.dart';
import '../features/auth/application/auth_controller.dart';
import '../features/auth/data/api_auth_repository.dart';
import '../features/auth/domain/auth_repository.dart';

/// Shared dependencies of the app, wired with Riverpod.
///
/// [sharedPreferencesProvider] is overridden in `main()` with the loaded instance.
///
/// Author: PH.Tuan · Created: 2026-10-18
final sharedPreferencesProvider = Provider<SharedPreferences>(
  (ref) => throw UnimplementedError('Override sharedPreferencesProvider in main()'),
);

/// Small, non-sensitive app settings.
final appPreferencesProvider = Provider<AppPreferences>((ref) => AppPreferences(ref.watch(sharedPreferencesProvider)));

/// Secure token storage (one instance for the app).
final tokenStorageProvider = Provider<TokenStorage>((ref) => TokenStorage());

/// Current UI locale: the saved choice, otherwise the device language if supported, otherwise Vietnamese.
final localeProvider = NotifierProvider<LocaleController, Locale>(LocaleController.new);

/// Changes and remembers the UI language (MB-01, MB-16b). Applies immediately, no restart needed.
class LocaleController extends Notifier<Locale> {
  @override
  Locale build() {
    final savedLanguageCode = ref.watch(appPreferencesProvider).languageCode;
    final deviceLanguageCode = WidgetsBinding.instance.platformDispatcher.locale.languageCode;

    final languageCode = savedLanguageCode ??
        (AppConfig.supportedLanguageCodes.contains(deviceLanguageCode) ? deviceLanguageCode : 'vi');

    return Locale(languageCode);
  }

  /// Switches the UI language and saves the choice.
  Future<void> setLanguage(String languageCode) async {
    await ref.read(appPreferencesProvider).setLanguageCode(languageCode);
    state = Locale(languageCode);
  }
}

/// API client using the current language and signing out when the session cannot be refreshed.
final apiClientProvider = Provider<ApiClient>(
  (ref) => ApiClient(
    tokenStorage: ref.watch(tokenStorageProvider),
    languageCode: () => ref.read(localeProvider).languageCode,
    onSessionExpired: () => ref.read(authControllerProvider.notifier).onSessionExpired(),
  ),
);

/// Authentication endpoints.
final authRepositoryProvider = Provider<AuthRepository>(
  (ref) => ApiAuthRepository(apiClient: ref.watch(apiClientProvider), tokenStorage: ref.watch(tokenStorageProvider)),
);
