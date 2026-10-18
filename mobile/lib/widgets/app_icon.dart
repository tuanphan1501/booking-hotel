import 'package:flutter/widgets.dart';
import 'package:flutter_svg/flutter_svg.dart';

/// Names of the icons in assets/icons — the 1.75 px line icon set of the design (same names as the Web Admin).
///
/// Author: PH.Tuan · Created: 2026-10-19
abstract final class AppIcons {
  static const apple = 'apple';
  static const back = 'back';
  static const bell = 'bell';
  static const building = 'building';
  static const calendar = 'cal';
  static const check = 'check';
  static const chevronRight = 'chev';
  static const close = 'close';
  static const document = 'doc';
  static const eye = 'eye';
  static const gem = 'gem';
  static const globe = 'globe';
  static const google = 'google';
  static const heart = 'heart';
  static const help = 'help';
  static const home = 'home';
  static const info = 'info';
  static const list = 'list';
  static const lock = 'lock';
  static const logout = 'logout';
  static const percent = 'percent';
  static const pin = 'pin';
  static const search = 'search';
  static const ticket = 'ticket';
  static const user = 'user';
  static const users = 'users';
  static const warning = 'warn';
}

/// Line icon from the design icon set, tinted with [color] (the SVGs use `currentColor`).
///
/// Usage: `AppIcon(AppIcons.search, size: 20, color: AppColors.ink2)`.
///
/// Author: PH.Tuan · Created: 2026-10-19
class AppIcon extends StatelessWidget {
  /// Creates an icon from its [name] in [AppIcons].
  const AppIcon(this.name, {this.size = 20, this.color, this.semanticLabel, super.key});

  /// Icon name, see [AppIcons].
  final String name;

  /// Width and height in logical pixels (16 small, 20 default, 24 large).
  final double size;

  /// Stroke color; defaults to the surrounding [IconTheme] color.
  final Color? color;

  /// Accessibility label; leave null for decorative icons next to a text label.
  final String? semanticLabel;

  @override
  Widget build(BuildContext context) {
    final iconColor = color ?? IconTheme.of(context).color ?? const Color(0xFF2B1A16);

    return SvgPicture.asset(
      'assets/icons/$name.svg',
      width: size,
      height: size,
      theme: SvgTheme(currentColor: iconColor),
      semanticsLabel: semanticLabel,
      excludeFromSemantics: semanticLabel == null,
    );
  }
}
