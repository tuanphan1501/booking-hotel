import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../app/l10n/generated/app_localizations.dart';
import '../../../app/providers.dart';
import '../../../app/theme/app_colors.dart';
import '../../../app/theme/app_spacing.dart';
import '../../../app/theme/app_typography.dart';
import '../../../core/format/date_formatter.dart';
import '../../../core/format/money_formatter.dart';
import '../../../core/network/api_exception.dart';
import '../../../widgets/app_button.dart';
import '../../../widgets/app_card.dart';
import '../../../widgets/app_text_field.dart';
import '../../auth/application/auth_controller.dart';
import '../data/booking_repository.dart';
import '../domain/booking_models.dart';
import 'booking_widgets.dart';

/// MB-07 Guest details: name, phone, email (prefilled from the profile), arrival time and request.
///
/// - "Continue" calls `POST /bookings` (room held for 15 minutes) and opens MB-08.
///
/// Author: PH.Tuan · Created: 2026-10-19
class GuestInfoScreen extends ConsumerStatefulWidget {
  /// Creates the screen for the chosen room.
  const GuestInfoScreen({required this.selection, super.key});

  final RoomSelection selection;

  @override
  ConsumerState<GuestInfoScreen> createState() => _GuestInfoScreenState();
}

class _GuestInfoScreenState extends ConsumerState<GuestInfoScreen> {
  /// Arrival times offered: every 30 minutes from 14:00 to 23:30.
  static final List<String> _arrivalTimes = [
    for (var minutes = 14 * 60; minutes <= 23 * 60 + 30; minutes += 30)
      '${(minutes ~/ 60).toString().padLeft(2, '0')}:${(minutes % 60).toString().padLeft(2, '0')}',
  ];

  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _nameController;
  late final TextEditingController _phoneController;
  late final TextEditingController _emailController;
  final _requestController = TextEditingController();
  String? _arrivalTime;
  bool _isSubmitting = false;

  @override
  void initState() {
    super.initState();
    final authState = ref.read(authControllerProvider);
    final customer = authState is AuthSignedIn ? authState.customer : null;
    _nameController = TextEditingController(text: customer?.fullName);
    _phoneController = TextEditingController(text: customer?.phone);
    _emailController = TextEditingController(text: customer?.email);
  }

  @override
  void dispose() {
    for (final controller in [_nameController, _phoneController, _emailController, _requestController]) {
      controller.dispose();
    }
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _isSubmitting = true);

    try {
      final booking = await ref.read(bookingRepositoryProvider).createBooking(
            selection: widget.selection,
            guestName: _nameController.text.trim(),
            guestPhone: _phoneController.text.trim(),
            guestEmail: _emailController.text.trim(),
            estimatedArrival: _arrivalTime,
            specialRequest: _requestController.text.trim(),
          );
      if (mounted) context.pushReplacement('/booking/${booking.code}/payment');
    } on ApiException catch (exception) {
      if (!mounted) return;
      final l10n = AppLocalizations.of(context);
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(exception.code == ApiException.networkErrorCode ? l10n.errorNetwork : exception.message ?? l10n.errorUnknown),
      ));
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final languageCode = ref.watch(localeProvider).languageCode;
    final money = MoneyFormatter(languageCode);
    final dates = DateFormatter(languageCode);
    final selection = widget.selection;
    String? required(String? value) => (value ?? '').trim().isEmpty ? l10n.guestRequired : null;

    return Scaffold(
      appBar: AppBar(title: Text(l10n.guestTitle, style: AppText.m)),
      bottomNavigationBar: BookingBottomBar(
        caption: Padding(
          padding: const EdgeInsets.only(bottom: 10),
          child: PriceRow(label: l10n.guestSubtotal, value: money.format(selection.ratePlan.quote.totalAmount), isTotal: true),
        ),
        child: AppButton(label: l10n.guestContinue, onPressed: _submit, isLoading: _isSubmitting),
      ),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(AppSpace.l),
          children: [
            const BookingSteps(current: 1),
            const SizedBox(height: AppSpace.l),
            AppCard(
              child: Row(
                children: [
                  SizedBox(width: 72, child: BookingIllustration(selection.roomType.illustration, height: 60)),
                  const SizedBox(width: AppSpace.m),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(selection.hotel.name, style: AppText.b.copyWith(fontWeight: FontWeight.w700)),
                        Text('${selection.roomType.name} · ${selection.ratePlan.name}', style: AppText.s.copyWith(color: AppColors.ink2)),
                        Text(
                          '${dates.short(selection.stay.checkIn)} – ${dates.short(selection.stay.checkOut)} · ${l10n.searchNights(selection.stay.nights)}',
                          style: AppText.s.copyWith(color: AppColors.ink3),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: AppSpace.l),
            AppTextField(
              label: l10n.guestName,
              controller: _nameController,
              textInputAction: TextInputAction.next,
              autofillHints: const [AutofillHints.name],
              validator: required,
            ),
            const SizedBox(height: AppSpace.l),
            AppTextField(
              label: l10n.guestPhone,
              controller: _phoneController,
              keyboardType: TextInputType.phone,
              textInputAction: TextInputAction.next,
              autofillHints: const [AutofillHints.telephoneNumber],
              validator: required,
            ),
            const SizedBox(height: AppSpace.l),
            AppTextField(
              label: l10n.guestEmail,
              controller: _emailController,
              keyboardType: TextInputType.emailAddress,
              textInputAction: TextInputAction.next,
              autofillHints: const [AutofillHints.email],
              validator: (value) => required(value) ?? (RegExp(r'^[^@\s]+@[^@\s]+\.[^@\s]+$').hasMatch(value!.trim()) ? null : l10n.guestInvalidEmail),
            ),
            const SizedBox(height: AppSpace.l),
            Text(l10n.guestArrival, style: AppText.s.copyWith(fontWeight: FontWeight.w600)),
            const SizedBox(height: 6),
            DropdownButtonFormField<String>(
              value: _arrivalTime,
              items: [for (final time in _arrivalTimes) DropdownMenuItem(value: time, child: Text(time))],
              onChanged: (value) => setState(() => _arrivalTime = value),
              decoration: const InputDecoration(),
            ),
            const SizedBox(height: AppSpace.l),
            AppTextField(label: l10n.guestRequest, controller: _requestController, textInputAction: TextInputAction.done),
          ],
        ),
      ),
    );
  }
}
