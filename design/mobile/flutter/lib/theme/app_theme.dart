// ThemeData Material 3 dựng từ token. Dùng: MaterialApp(theme: AppTheme.light(locale))
import 'package:flutter/material.dart';
import 'app_colors.dart';
import 'app_spacing.dart';
import 'app_typography.dart';

abstract final class AppTheme {
  static ThemeData light(Locale locale) {
    final scheme = ColorScheme.fromSeed(seedColor: AppColors.brand).copyWith(
      primary: AppColors.brand,
      onPrimary: Colors.white,
      primaryContainer: AppColors.brandWash,
      onPrimaryContainer: AppColors.brandInk,
      secondary: AppColors.gold,
      error: AppColors.rose,
      surface: AppColors.surface,
      onSurface: AppColors.ink,
      onSurfaceVariant: AppColors.ink3,
      outline: AppColors.line,
      outlineVariant: AppColors.line2,
      scrim: AppColors.scrim,
    );
    const r12 = BorderRadius.all(AppRadius.m);
    OutlineInputBorder border(Color c, double w) => OutlineInputBorder(borderRadius: r12, borderSide: BorderSide(color: c, width: w));

    return ThemeData(
      useMaterial3: true,
      colorScheme: scheme,
      fontFamily: AppFonts.of(locale),
      scaffoldBackgroundColor: AppColors.paper,
      textTheme: const TextTheme(
        headlineSmall: AppText.xl,
        titleLarge: AppText.l,
        titleMedium: AppText.m,
        bodyLarge: AppText.b,
        bodyMedium: AppText.s,
        bodySmall: AppText.xs,
        labelLarge: TextStyle(fontSize: 15, fontWeight: FontWeight.w600),
      ),
      appBarTheme: const AppBarTheme(
        backgroundColor: AppColors.paper,
        foregroundColor: AppColors.ink,
        elevation: 0,
        scrolledUnderElevation: 0,
        centerTitle: true,
        toolbarHeight: AppSize.header,
        titleTextStyle: AppText.m,
      ),
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          backgroundColor: AppColors.brand,
          foregroundColor: Colors.white,
          disabledBackgroundColor: AppColors.disabledBg,
          disabledForegroundColor: AppColors.disabledFg,
          minimumSize: const Size(AppSize.touchMin, AppSize.button),
          padding: const EdgeInsets.symmetric(horizontal: 20),
          shape: const RoundedRectangleBorder(borderRadius: BorderRadius.all(AppRadius.button)),
          textStyle: const TextStyle(fontSize: 15, fontWeight: FontWeight.w600),
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: AppColors.ink,
          backgroundColor: Colors.white,
          minimumSize: const Size(AppSize.touchMin, AppSize.button),
          side: const BorderSide(color: AppColors.line, width: 1.5),
          shape: const RoundedRectangleBorder(borderRadius: BorderRadius.all(AppRadius.button)),
          textStyle: const TextStyle(fontSize: 15, fontWeight: FontWeight.w600),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: Colors.white,
        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 13),
        hintStyle: const TextStyle(color: AppColors.placeholder),
        border: border(AppColors.line, 1.5),
        enabledBorder: border(AppColors.line, 1.5),
        focusedBorder: border(AppColors.brand, 2),
        errorBorder: border(AppColors.rose, 1.5),
        focusedErrorBorder: border(AppColors.rose, 2),
        errorStyle: const TextStyle(color: AppColors.rose, fontSize: 12),
        floatingLabelBehavior: FloatingLabelBehavior.never, // nhãn đặt phía trên ô, không nổi
      ),
      cardTheme: const CardThemeData(
        color: Colors.white,
        elevation: 0,
        margin: EdgeInsets.zero,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.all(AppRadius.l)),
      ),
      chipTheme: const ChipThemeData(
        backgroundColor: AppColors.line2,
        labelStyle: TextStyle(fontSize: 12, fontWeight: FontWeight.w500, color: AppColors.ink2),
        shape: StadiumBorder(),
        side: BorderSide.none,
      ),
      bottomSheetTheme: const BottomSheetThemeData(
        backgroundColor: Colors.white,
        showDragHandle: true,
        dragHandleColor: Color(0xFFE2D3CE),
        dragHandleSize: Size(40, 5),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: AppRadius.xl)),
      ),
      navigationBarTheme: NavigationBarThemeData(
        backgroundColor: Colors.white,
        indicatorColor: Colors.transparent,
        height: 56,
        labelTextStyle: WidgetStateProperty.resolveWith((s) => AppText.tab.copyWith(
              color: s.contains(WidgetState.selected) ? AppColors.brand : AppColors.ink3,
              fontWeight: s.contains(WidgetState.selected) ? FontWeight.w600 : FontWeight.w500,
            )),
        iconTheme: WidgetStateProperty.resolveWith((s) => IconThemeData(
              size: AppSize.iconL,
              color: s.contains(WidgetState.selected) ? AppColors.brand : AppColors.ink3,
            )),
      ),
      switchTheme: SwitchThemeData(
        thumbColor: const WidgetStatePropertyAll(Colors.white),
        trackColor: WidgetStateProperty.resolveWith((s) => s.contains(WidgetState.selected) ? AppColors.brand : const Color(0xFFD8C8C3)),
        trackOutlineColor: const WidgetStatePropertyAll(Colors.transparent),
      ),
      dividerTheme: const DividerThemeData(color: AppColors.line, thickness: 1, space: 1),
      snackBarTheme: const SnackBarThemeData(backgroundColor: AppColors.ink, behavior: SnackBarBehavior.floating),
    );
  }
}
