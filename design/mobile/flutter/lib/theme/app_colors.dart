// Màu Vivu Stay — nguồn: design/mobile/tokens/design-tokens.json
import 'package:flutter/material.dart';

abstract final class AppColors {
  // chữ & nền
  static const ink = Color(0xFF2B1A16); // chữ chính
  static const ink2 = Color(0xFF5C4640); // chữ thứ cấp (8,6:1)
  static const ink3 = Color(0xFF7A615A); // chữ phụ (5,7:1 – AA)
  static const line = Color(0xFFEFE3DF);
  static const line2 = Color(0xFFF6EEEB);
  static const paper = Color(0xFFFBF6F4); // nền màn hình
  static const surface = Color(0xFFFFFFFF);

  // hành động chính duy nhất
  static const brand = Color(0xFFB42318);
  static const brandInk = Color(0xFF8A1A12);
  static const brandWash = Color(0xFFFDECE9);
  static const brandSoft = Color(0xFFF3B4AA);

  // chỉ dùng cho hạng thành viên & điểm
  static const gold = Color(0xFF9A6516);
  static const goldWash = Color(0xFFF8EDDA);
  static const goldHi = Color(0xFFC98A2E);

  // trạng thái
  static const rose = Color(0xFF9F1239); // lỗi, hủy — luôn kèm icon
  static const roseWash = Color(0xFFFCE7EE);
  static const amber = Color(0xFFB4540A); // cảnh báo, chờ
  static const amberWash = Color(0xFFFDF0E1);
  static const amberText = Color(0xFF6E4505);
  static const sky = Color(0xFF6E4B3E); // thông tin, đang ở
  static const skyWash = Color(0xFFF3EAE6);

  // điều khiển
  static const disabledBg = Color(0xFFE8DCD8);
  static const disabledFg = Color(0xFF9C8781);
  static const placeholder = Color(0xFF9C8781);
  static const controlBorder = Color(0xFFC9B6B0);
  static const dangerBorder = Color(0xFFEFC5C1);
  static const scrim = Color(0x801E120F);

  static const keyCardGradient = LinearGradient(
    begin: Alignment(-0.34, -0.94), // ~160deg
    end: Alignment(0.34, 0.94),
    colors: [Color(0xFF6A1D14), Color(0xFF2B1A16)],
  );
  static const progressGold = LinearGradient(colors: [goldHi, Color(0xFFE2C27E)]);
  static const progressTrack = Color(0xFFEFE4DA);
}
