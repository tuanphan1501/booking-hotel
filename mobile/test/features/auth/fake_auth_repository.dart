import 'package:vivu_stay/core/network/api_exception.dart';
import 'package:vivu_stay/features/auth/domain/auth_repository.dart';
import 'package:vivu_stay/features/auth/domain/customer.dart';

/// In-memory [AuthRepository] for tests: accepts one demo account and remembers the session.
///
/// Author: PH.Tuan · Created: 2026-10-18
class FakeAuthRepository implements AuthRepository {
  /// Login identifier accepted by the fake.
  static const validLogin = 'minh.nguyen@gmail.com';

  /// Password accepted by the fake.
  static const validPassword = 'secret-password';

  /// Customer returned after a successful sign-in.
  static const demoCustomer = Customer(
    id: 1,
    memberCode: 'M00012845',
    fullName: 'Nguyễn Văn Minh',
    email: validLogin,
    languageCode: 'vi',
    pointBalance: 2450,
    memberTierCode: 'GOLD',
    memberTierName: 'Vàng',
  );

  Customer? _signedInCustomer;

  /// Number of sign-in attempts received (for assertions).
  int loginAttempts = 0;

  @override
  Future<Customer> login({
    required String loginIdentifier,
    required String password,
    required String deviceName,
    required bool rememberLogin,
  }) async {
    loginAttempts++;

    if (loginIdentifier != validLogin || password != validPassword) {
      throw const ApiException(
        code: 'INVALID_CREDENTIALS',
        message: 'Incorrect email/phone number or password. 4 attempts left.',
        details: {'remaining_attempts': 4},
        statusCode: 401,
      );
    }

    return _signedInCustomer = demoCustomer;
  }

  @override
  Future<Customer?> currentCustomer() async => _signedInCustomer;

  @override
  Future<void> logout() async => _signedInCustomer = null;
}
