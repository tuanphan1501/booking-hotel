/// Signed-in customer, as returned by `GET /api/v1/me` and the sign-in response.
///
/// Author: PH.Tuan · Created: 2026-10-18
class Customer {
  /// Creates a customer.
  const Customer({
    required this.id,
    required this.memberCode,
    required this.fullName,
    required this.languageCode,
    required this.pointBalance,
    this.email,
    this.phone,
    this.memberTierCode,
    this.memberTierName,
  });

  /// Builds a customer from the API JSON (backend CustomerResource).
  factory Customer.fromJson(Map<String, dynamic> json) {
    final memberTier = json['member_tier'] as Map<String, dynamic>?;

    return Customer(
      id: json['id'] as int,
      memberCode: json['member_code'] as String,
      fullName: json['full_name'] as String,
      email: json['email'] as String?,
      phone: json['phone'] as String?,
      languageCode: json['language'] as String? ?? 'vi',
      pointBalance: json['point_balance'] as int? ?? 0,
      memberTierCode: memberTier?['code'] as String?,
      memberTierName: memberTier?['name'] as String?,
    );
  }

  final int id;

  /// Member number shown on the membership card, e.g. "M00012845".
  final String memberCode;
  final String fullName;
  final String? email;
  final String? phone;

  /// Preferred language saved on the server ("vi", "ja", "en").
  final String languageCode;

  /// Redeemable points (1 point = 100 VND, BR-03 ⑧).
  final int pointBalance;

  /// Tier code (MEMBER, SILVER, GOLD, PLATINUM) and its translated name.
  final String? memberTierCode;
  final String? memberTierName;

  /// Short name used in greetings: the last word of a Vietnamese full name ("Nguyễn Văn Minh" → "Minh").
  String get greetingName {
    final words = fullName.trim().split(RegExp(r'\s+'));
    return languageCode == 'vi' ? words.last : fullName;
  }
}
