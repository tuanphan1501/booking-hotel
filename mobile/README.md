# Vivu Stay — Mobile App (Flutter)

Guest app for iOS and Android. Design: `../design/mobile/` · API: `../backend/` (`/api/v1`).

## Requirements

- Flutter 3.47+ (Dart 3.13+)
- Android: Android SDK (Android Studio) + a phone with USB debugging, or an emulator
- iOS: a **Mac** with Xcode — iOS apps cannot be built or installed from Windows

## Run

```bash
flutter pub get                       # also generates the translations (lib/app/l10n/generated)

# Real Android phone over USB, backend running on this PC (php artisan serve)
adb reverse tcp:8000 tcp:8000
flutter run

# Other backend address (emulator: 10.0.2.2, Wi-Fi: the PC's LAN IP)
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api/v1
```

Demo customers are seeded by the backend (`minh.nguyen@gmail.com`, password = `SEED_DEMO_PASSWORD` in `backend/.env`).

## Quality

```bash
dart format lib test      # line width 120 (analysis_options.yaml)
flutter analyze           # must report "No issues found"
flutter test
```

## Architecture

```
lib/
  main.dart                 entry point (preferences, date formats, ProviderScope)
  app/                      app shell: VivuStayApp, router (go_router), providers (Riverpod), theme, l10n (ARB vi/ja/en)
  core/                     framework-agnostic building blocks
    config/                 AppConfig (--dart-define)
    network/                ApiClient (Dio: Accept-Language, Bearer token, refresh on 401), ApiException
    storage/                TokenStorage (secure storage), AppPreferences
    format/                 MoneyFormatter
  features/<feature>/
    domain/                 models + repository interfaces (no Flutter / HTTP imports)
    data/                   repository implementations (API)
    application/            controllers / state (Riverpod Notifiers)
    presentation/           screens and widgets
  widgets/                  shared UI components
test/                       unit, controller and widget tests (fake repositories, no network)
```

Conventions: English comments and dartdoc on every public class and member, `Author` / `Created` in class docs,
screens reference the design IDs (MB-01 …), errors from the API are shown with the server's translated message.

## Screens in this version

| ID | Screen | Status |
| --- | --- | --- |
| MB-01 | Welcome & language | ✅ |
| MB-02 | Sign in (email / phone) | ✅ real API |
| MB-03 | Home & search | ✅ UI (sample hotels until `GET /api/v1/home` exists) |
| MB-10 / MB-14 | Trips / Offers | empty states |
| MB-16 / MB-16b | Account / Language | ✅ profile from `/api/v1/me`, sign-out, instant language switch |
