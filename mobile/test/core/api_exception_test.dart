import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:vivu_stay/core/network/api_exception.dart';

/// Parsing of the backend's standard error body `{ code, message, details }`.
///
/// Author: PH.Tuan · Created: 2026-10-18
void main() {
  final requestOptions = RequestOptions(path: '/auth/login');

  test('reads code, message and details from the server body', () {
    final exception = ApiException.fromDioException(
      DioException(
        requestOptions: requestOptions,
        response: Response(
          requestOptions: requestOptions,
          statusCode: 401,
          data: {
            'code': 'INVALID_CREDENTIALS',
            'message': 'Incorrect email/phone number or password. 4 attempts left.',
            'details': {'remaining_attempts': 4},
          },
        ),
      ),
    );

    expect(exception.code, 'INVALID_CREDENTIALS');
    expect(exception.message, contains('4 attempts left'));
    expect(exception.details['remaining_attempts'], 4);
    expect(exception.statusCode, 401);
  });

  test('exposes validation messages per field', () {
    final exception = ApiException.fromDioException(
      DioException(
        requestOptions: requestOptions,
        response: Response(
          requestOptions: requestOptions,
          statusCode: 422,
          data: {
            'code': 'VALIDATION_ERROR',
            'message': 'Some fields are invalid.',
            'details': {
              'fields': {
                'login': ['The email or phone number field is required.'],
              },
            },
          },
        ),
      ),
    );

    expect(exception.fieldErrors['login'], ['The email or phone number field is required.']);
  });

  test('maps connection failures to NETWORK_ERROR', () {
    final exception = ApiException.fromDioException(
      DioException(requestOptions: requestOptions, type: DioExceptionType.connectionError),
    );

    expect(exception.code, ApiException.networkErrorCode);
    expect(exception.message, isNull);
  });

  test('recognizes an expired session', () {
    expect(const ApiException(code: 'UNAUTHENTICATED').isUnauthenticated, isTrue);
    expect(const ApiException(code: 'INVALID_CREDENTIALS').isUnauthenticated, isFalse);
  });
}
