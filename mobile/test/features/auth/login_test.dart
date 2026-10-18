import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:vivu_stay/app/l10n/generated/app_localizations.dart';
import 'package:vivu_stay/app/providers.dart';
import 'package:vivu_stay/core/network/api_exception.dart';
import 'package:vivu_stay/features/auth/application/auth_controller.dart';
import 'package:vivu_stay/features/auth/presentation/login_screen.dart';
import 'package:vivu_stay/widgets/app_button.dart';

import 'fake_auth_repository.dart';

/// MB-02 sign-in: controller state changes and the form's error handling.
///
/// Author: PH.Tuan · Created: 2026-10-18
void main() {
  late FakeAuthRepository fakeAuthRepository;
  late ProviderContainer container;

  setUp(() async {
    SharedPreferences.setMockInitialValues({'app.language_code': 'en'});
    fakeAuthRepository = FakeAuthRepository();
    container = ProviderContainer(
      overrides: [
        sharedPreferencesProvider.overrideWithValue(await SharedPreferences.getInstance()),
        authRepositoryProvider.overrideWithValue(fakeAuthRepository),
      ],
    );
    addTearDown(container.dispose);
  });

  group('AuthController', () {
    test('starts as guest when the device has no session', () async {
      container.read(authControllerProvider);
      await Future<void>.delayed(Duration.zero);

      expect(container.read(authControllerProvider), isA<AuthGuest>());
    });

    test('signs in and exposes the customer', () async {
      await container.read(authControllerProvider.notifier).login(
            loginIdentifier: FakeAuthRepository.validLogin,
            password: FakeAuthRepository.validPassword,
            rememberLogin: true,
          );

      final state = container.read(authControllerProvider);
      expect(state, isA<AuthSignedIn>());
      expect((state as AuthSignedIn).customer.greetingName, 'Minh');
    });

    test('rethrows the API error for wrong credentials', () async {
      expect(
        () => container.read(authControllerProvider.notifier).login(
              loginIdentifier: FakeAuthRepository.validLogin,
              password: 'wrong',
              rememberLogin: true,
            ),
        throwsA(isA<ApiException>().having((error) => error.code, 'code', 'INVALID_CREDENTIALS')),
      );
    });

    test('signs out back to guest', () async {
      final controller = container.read(authControllerProvider.notifier);
      await controller.login(
        loginIdentifier: FakeAuthRepository.validLogin,
        password: FakeAuthRepository.validPassword,
        rememberLogin: false,
      );

      await controller.logout();

      expect(container.read(authControllerProvider), isA<AuthGuest>());
    });
  });

  group('LoginScreen', () {
    Future<void> pumpLoginScreen(WidgetTester tester) => tester.pumpWidget(
          UncontrolledProviderScope(
            container: container,
            child: const MaterialApp(
              locale: Locale('en'),
              supportedLocales: AppLocalizations.supportedLocales,
              localizationsDelegates: [
                AppLocalizations.delegate,
                GlobalMaterialLocalizations.delegate,
                GlobalWidgetsLocalizations.delegate,
                GlobalCupertinoLocalizations.delegate,
              ],
              home: LoginScreen(),
            ),
          ),
        );

    testWidgets('requires both fields before calling the API', (tester) async {
      await pumpLoginScreen(tester);

      await tester.tap(find.widgetWithText(AppButton, 'Sign in'));
      await tester.pump();

      expect(find.text('Enter your email or phone number'), findsOneWidget);
      expect(find.text('Enter your password'), findsOneWidget);
      expect(fakeAuthRepository.loginAttempts, 0);
    });

    testWidgets('shows the server message under the password field', (tester) async {
      await pumpLoginScreen(tester);

      await tester.enterText(find.byType(TextField).at(0), FakeAuthRepository.validLogin);
      await tester.enterText(find.byType(TextField).at(1), 'wrong-password');
      await tester.tap(find.widgetWithText(AppButton, 'Sign in'));
      await tester.pumpAndSettle();

      expect(find.text('Incorrect email/phone number or password. 4 attempts left.'), findsOneWidget);
      expect(fakeAuthRepository.loginAttempts, 1);
    });
  });
}
