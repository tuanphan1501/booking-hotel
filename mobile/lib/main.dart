import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/date_symbol_data_local.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'app/app.dart';
import 'app/providers.dart';

/// Entry point of the Vivu Stay app.
///
/// - Loads device preferences and date formats before the first frame.
/// - Backend URL: `flutter run --dart-define=API_BASE_URL=http://<host>:8000/api/v1`
///   (default `http://127.0.0.1:8000/api/v1` with `adb reverse tcp:8000 tcp:8000`).
///
/// Author: PH.Tuan · Created: 2026-10-18
Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();

  final sharedPreferences = await SharedPreferences.getInstance();
  await Future.wait([initializeDateFormatting('vi'), initializeDateFormatting('ja'), initializeDateFormatting('en')]);

  runApp(
    ProviderScope(
      overrides: [sharedPreferencesProvider.overrideWithValue(sharedPreferences)],
      child: const VivuStayApp(),
    ),
  );
}
