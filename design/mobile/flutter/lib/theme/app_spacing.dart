// Khoảng cách, bo góc, kích thước — lưới 4px
import 'package:flutter/material.dart';

abstract final class AppSpace {
  static const xs = 4.0;
  static const s = 8.0;
  static const m = 12.0;
  static const l = 16.0; // lề màn hình
  static const xl = 24.0;
}

abstract final class AppRadius {
  static const s = Radius.circular(8); // ô nhỏ
  static const m = Radius.circular(12); // ô nhập, ảnh
  static const l = Radius.circular(18); // thẻ
  static const xl = Radius.circular(24); // bottom sheet
  static const button = Radius.circular(14);
  static const buttonSm = Radius.circular(10);
  static const buttonXs = Radius.circular(8);
}

abstract final class AppSize {
  static const touchMin = 44.0;
  static const button = 48.0;
  static const buttonSm = 36.0;
  static const buttonXs = 30.0;
  static const input = 48.0;
  static const header = 52.0;
  static const iconButton = 44.0;
  static const icon = 20.0;
  static const iconS = 16.0;
  static const iconL = 24.0;
  static const checkbox = 22.0;
}

abstract final class AppShadow {
  static const card = [
    BoxShadow(color: Color(0x0F2B1A16), offset: Offset(0, 1), blurRadius: 2),
    BoxShadow(color: Color(0x0F2B1A16), offset: Offset(0, 4), blurRadius: 14),
  ];
  static const sheet = [BoxShadow(color: Color(0x2E2B1A16), offset: Offset(0, -10), blurRadius: 30)];
}
