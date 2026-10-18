import 'package:flutter/material.dart';

import '../app/theme/app_colors.dart';
import 'app_icon.dart';

/// Two or more mutually exclusive options on a grey track; the selected one is a white pill (design: .seg).
///
/// Author: PH.Tuan · Created: 2026-10-19
class AppSegmentedControl extends StatelessWidget {
  /// Creates a segmented control.
  const AppSegmentedControl({required this.labels, required this.selectedIndex, required this.onChanged, super.key});

  final List<String> labels;
  final int selectedIndex;
  final ValueChanged<int> onChanged;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(3),
      decoration: BoxDecoration(color: AppColors.line2, borderRadius: BorderRadius.circular(12)),
      child: Row(
        children: [
          for (var index = 0; index < labels.length; index++)
            Expanded(
              child: Semantics(
                selected: index == selectedIndex,
                button: true,
                child: GestureDetector(
                  onTap: () => onChanged(index),
                  child: AnimatedContainer(
                    duration: const Duration(milliseconds: 150),
                    height: 38,
                    alignment: Alignment.center,
                    decoration: BoxDecoration(
                      color: index == selectedIndex ? Colors.white : Colors.transparent,
                      borderRadius: BorderRadius.circular(10),
                      boxShadow: index == selectedIndex
                          ? const [BoxShadow(color: Color(0x1F2B1A16), blurRadius: 3, offset: Offset(0, 1))]
                          : null,
                    ),
                    child: Text(
                      labels[index],
                      style: TextStyle(
                        fontSize: 14,
                        fontWeight: index == selectedIndex ? FontWeight.w600 : FontWeight.w400,
                        color: index == selectedIndex ? AppColors.ink : AppColors.ink2,
                      ),
                    ),
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }
}

/// Square 22 px checkbox with 7 px radius; brand fill and white check when on (design: .ck).
///
/// Author: PH.Tuan · Created: 2026-10-19
class AppCheckbox extends StatelessWidget {
  /// Creates a checkbox; the tap target is 44 px even though the box is 22 px.
  const AppCheckbox({required this.value, required this.onChanged, super.key});

  final bool value;
  final ValueChanged<bool> onChanged;

  @override
  Widget build(BuildContext context) {
    return Semantics(
      checked: value,
      child: InkResponse(
        onTap: () => onChanged(!value),
        radius: 22,
        child: SizedBox(
          width: 44,
          height: 44,
          child: Center(
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 120),
              width: 22,
              height: 22,
              decoration: BoxDecoration(
                color: value ? AppColors.brand : Colors.white,
                borderRadius: BorderRadius.circular(7),
                border: value ? null : Border.all(color: AppColors.controlBorder, width: 1.75),
              ),
              child: value ? const AppIcon(AppIcons.check, size: 16, color: Colors.white) : null,
            ),
          ),
        ),
      ),
    );
  }
}

/// Round 22 px radio indicator: thick brand ring when selected (design: .rd).
///
/// Author: PH.Tuan · Created: 2026-10-19
class AppRadioIndicator extends StatelessWidget {
  /// Creates the indicator; place it inside a tappable row.
  const AppRadioIndicator({required this.isSelected, this.background = Colors.white, super.key});

  final bool isSelected;

  /// Fill inside the ring (white on light and dark backgrounds).
  final Color background;

  @override
  Widget build(BuildContext context) {
    return AnimatedContainer(
      duration: const Duration(milliseconds: 120),
      width: 22,
      height: 22,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        color: background,
        border: Border.all(
          color: isSelected ? AppColors.brand : AppColors.controlBorder,
          width: isSelected ? 7 : 1.75,
        ),
      ),
    );
  }
}
