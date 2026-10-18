import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../app/l10n/generated/app_localizations.dart';
import '../../../app/providers.dart';
import '../../../app/theme/app_colors.dart';
import '../../../app/theme/app_typography.dart';
import '../../../core/network/api_exception.dart';
import '../../../widgets/app_button.dart';
import '../../../widgets/app_controls.dart';
import '../../../widgets/app_icon.dart';
import '../../../widgets/app_text_field.dart';
import '../application/auth_controller.dart';

/// MB-02 Sign in with email/phone and password.
///
/// - Layout follows design/mobile/screens/MB-02.png: close button, title, segmented Sign in / Sign up,
///   labeled fields, remember-me + forgot password, primary button, social buttons.
/// - Server errors (wrong password with remaining attempts, lockout) appear under the password field;
///   network errors appear as a snackbar.
/// - Sign up, forgot password and Google / Apple sign-in are not available yet ("coming soon").
/// - After success, returns to [returnTo] (e.g. the booking flow) or the home tab.
///
/// Author: PH.Tuan · Created: 2026-10-18
class LoginScreen extends ConsumerStatefulWidget {
  /// Creates the sign-in screen.
  const LoginScreen({this.returnTo, super.key});

  /// Location to open after signing in.
  final String? returnTo;

  @override
  ConsumerState<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends ConsumerState<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _loginController = TextEditingController();
  final _passwordController = TextEditingController();

  bool _isPasswordVisible = false;
  late bool _rememberLogin = ref.read(appPreferencesProvider).rememberLogin;
  bool _isSubmitting = false;

  /// Server error shown under the password field (credentials, lockout, account status).
  String? _serverError;

  @override
  void dispose() {
    _loginController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    final l10n = AppLocalizations.of(context);
    setState(() => _serverError = null);
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isSubmitting = true);
    try {
      await ref.read(authControllerProvider.notifier).login(
            loginIdentifier: _loginController.text.trim(),
            password: _passwordController.text,
            rememberLogin: _rememberLogin,
          );
      if (mounted) context.go(widget.returnTo ?? '/home');
    } on ApiException catch (exception) {
      if (!mounted) return;

      if (exception.code == ApiException.networkErrorCode) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(l10n.errorNetwork)));
      } else {
        setState(() => _serverError = exception.message ?? l10n.errorUnknown);
      }
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  void _showComingSoon() {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(AppLocalizations.of(context).authComingSoon)));
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);

    return Scaffold(
      body: SafeArea(
        child: Form(
          key: _formKey,
          child: AutofillGroup(
            child: ListView(
              padding: const EdgeInsets.fromLTRB(24, 4, 24, 24),
              children: [
                Align(
                  alignment: Alignment.centerLeft,
                  child: IconButton(
                    onPressed: () => context.canPop() ? context.pop() : context.go('/home'),
                    tooltip: MaterialLocalizations.of(context).closeButtonTooltip,
                    padding: EdgeInsets.zero,
                    icon: const AppIcon(AppIcons.close, size: 24, color: AppColors.ink),
                  ),
                ),
                const SizedBox(height: 12),
                Text(l10n.authLoginTitle, style: AppText.xl),
                const SizedBox(height: 8),
                Text(l10n.authLoginSubtitle, style: AppText.b.copyWith(color: AppColors.ink2)),
                const SizedBox(height: 20),
                AppSegmentedControl(
                  labels: [l10n.authSubmit, l10n.authSignUpTab],
                  selectedIndex: 0,
                  onChanged: (index) => index == 1 ? _showComingSoon() : null,
                ),
                const SizedBox(height: 16),
                AppTextField(
                  label: l10n.authLoginId,
                  controller: _loginController,
                  keyboardType: TextInputType.emailAddress,
                  textInputAction: TextInputAction.next,
                  autofillHints: const [AutofillHints.username, AutofillHints.email, AutofillHints.telephoneNumber],
                  validator: (value) => (value ?? '').trim().isEmpty ? l10n.authLoginIdRequired : null,
                ),
                const SizedBox(height: 16),
                AppTextField(
                  label: l10n.authPassword,
                  controller: _passwordController,
                  obscureText: !_isPasswordVisible,
                  textInputAction: TextInputAction.done,
                  autofillHints: const [AutofillHints.password],
                  errorText: _serverError,
                  validator: (value) => (value ?? '').isEmpty ? l10n.authPasswordRequired : null,
                  onSubmitted: (_) => _submit(),
                  suffix: IconButton(
                    tooltip: _isPasswordVisible ? l10n.authHidePassword : l10n.authShowPassword,
                    icon: AppIcon(AppIcons.eye, color: _isPasswordVisible ? AppColors.brand : AppColors.ink),
                    onPressed: () => setState(() => _isPasswordVisible = !_isPasswordVisible),
                  ),
                ),
                const SizedBox(height: 6),
                Row(
                  children: [
                    Transform.translate(
                      offset: const Offset(-11, 0),
                      child: AppCheckbox(
                        value: _rememberLogin,
                        onChanged: (value) => setState(() => _rememberLogin = value),
                      ),
                    ),
                    Expanded(
                      child: Transform.translate(
                        offset: const Offset(-11, 0),
                        child: Text(l10n.authRemember, style: AppText.s.copyWith(color: AppColors.ink)),
                      ),
                    ),
                    TextButton(
                      onPressed: _showComingSoon,
                      style: TextButton.styleFrom(foregroundColor: AppColors.brand, padding: EdgeInsets.zero),
                      child: Text(
                        l10n.authForgot,
                        style: AppText.b.copyWith(color: AppColors.brand, fontWeight: FontWeight.w700),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                AppButton(label: l10n.authSubmit, onPressed: _submit, isLoading: _isSubmitting),
                const SizedBox(height: 20),
                Row(
                  children: [
                    const Expanded(child: Divider(color: AppColors.line)),
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 14),
                      child: Text(l10n.authOr, style: AppText.s.copyWith(color: AppColors.ink3)),
                    ),
                    const Expanded(child: Divider(color: AppColors.line)),
                  ],
                ),
                const SizedBox(height: 20),
                AppButton(
                  label: l10n.authContinueGoogle,
                  variant: AppButtonVariant.secondary,
                  leading: const AppIcon(AppIcons.google, size: 22),
                  onPressed: _showComingSoon,
                ),
                const SizedBox(height: 12),
                AppButton(
                  label: l10n.authContinueApple,
                  variant: AppButtonVariant.dark,
                  leading: const AppIcon(AppIcons.apple, size: 22, color: Colors.white),
                  onPressed: _showComingSoon,
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
