import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../app/l10n/generated/app_localizations.dart';
import '../../../app/providers.dart';
import '../../../app/theme/app_colors.dart';
import '../../../app/theme/app_typography.dart';
import '../../../core/network/api_exception.dart';
import '../../../widgets/app_text_field.dart';
import '../application/auth_controller.dart';

/// MB-02 Sign in with email/phone and password.
///
/// - Field errors from the server (wrong password with remaining attempts, lockout) appear under
///   the related field; network errors appear as a snackbar.
/// - Google / Apple sign-in and the Sign-up tab are shown but not available yet.
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
      appBar: AppBar(
        leading: IconButton(
          icon: const Icon(Icons.close),
          tooltip: MaterialLocalizations.of(context).closeButtonTooltip,
          onPressed: () => context.canPop() ? context.pop() : context.go('/home'),
        ),
      ),
      body: SafeArea(
        child: Form(
          key: _formKey,
          child: AutofillGroup(
            child: ListView(
              padding: const EdgeInsets.fromLTRB(24, 0, 24, 24),
              children: [
                Text(l10n.authLoginTitle, style: AppText.xl),
                const SizedBox(height: 6),
                Text(l10n.authLoginSubtitle, style: AppText.b.copyWith(color: AppColors.ink2)),
                const SizedBox(height: 20),
                SegmentedButton<int>(
                  segments: [
                    ButtonSegment(value: 0, label: Text(l10n.authSubmit)),
                    ButtonSegment(value: 1, label: Text(l10n.authSignUpTab)),
                  ],
                  selected: const {0},
                  showSelectedIcon: false,
                  onSelectionChanged: (_) => _showComingSoon(),
                ),
                const SizedBox(height: 20),
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
                    icon: Icon(_isPasswordVisible ? Icons.visibility_off_outlined : Icons.visibility_outlined),
                    onPressed: () => setState(() => _isPasswordVisible = !_isPasswordVisible),
                  ),
                ),
                const SizedBox(height: 8),
                Row(
                  children: [
                    Checkbox(
                      value: _rememberLogin,
                      onChanged: (value) => setState(() => _rememberLogin = value ?? true),
                    ),
                    Expanded(child: Text(l10n.authRemember, style: AppText.s)),
                    TextButton(onPressed: _showComingSoon, child: Text(l10n.authForgot)),
                  ],
                ),
                const SizedBox(height: 12),
                FilledButton(
                  onPressed: _isSubmitting ? null : _submit,
                  child: _isSubmitting
                      ? const SizedBox(
                          width: 22,
                          height: 22,
                          child: CircularProgressIndicator(strokeWidth: 2.5, color: Colors.white),
                        )
                      : Text(l10n.authSubmit),
                ),
                const SizedBox(height: 20),
                Row(
                  children: [
                    const Expanded(child: Divider()),
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 12),
                      child: Text(l10n.authOr, style: AppText.s.copyWith(color: AppColors.ink3)),
                    ),
                    const Expanded(child: Divider()),
                  ],
                ),
                const SizedBox(height: 20),
                OutlinedButton.icon(
                  onPressed: _showComingSoon,
                  icon: const Icon(Icons.g_mobiledata, size: 28),
                  label: Text(l10n.authContinueGoogle),
                ),
                const SizedBox(height: 12),
                FilledButton.icon(
                  style: FilledButton.styleFrom(backgroundColor: const Color(0xFF111111)),
                  onPressed: _showComingSoon,
                  icon: const Icon(Icons.apple),
                  label: Text(l10n.authContinueApple),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
