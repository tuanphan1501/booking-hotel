import 'package:dio/dio.dart';

/// Error returned by the backend in the standard shape `{ code, message, details }`.
///
/// - [code] is machine-readable (e.g. `INVALID_CREDENTIALS`) — branch on it.
/// - [message] is already translated by the server using `Accept-Language` — safe to show.
/// - Network failures use the code [ApiException.networkErrorCode] and no server message.
///
/// Author: PH.Tuan · Created: 2026-10-18
class ApiException implements Exception {
  /// Creates an API error.
  const ApiException({
    required this.code,
    this.message,
    this.details = const {},
    this.statusCode,
  });

  /// Builds an [ApiException] from a failed Dio request.
  ///
  /// - Uses the server's `{ code, message, details }` body when present.
  /// - Falls back to [networkErrorCode] for timeouts and connection errors.
  factory ApiException.fromDioException(DioException exception) {
    final data = exception.response?.data;

    if (data is Map<String, dynamic> && data['code'] is String) {
      final details = data['details'];

      return ApiException(
        code: data['code'] as String,
        message: data['message'] as String?,
        details: details is Map<String, dynamic> ? details : const {},
        statusCode: exception.response?.statusCode,
      );
    }

    final isNetworkError = exception.type == DioExceptionType.connectionError ||
        exception.type == DioExceptionType.connectionTimeout ||
        exception.type == DioExceptionType.receiveTimeout ||
        exception.type == DioExceptionType.sendTimeout;

    return ApiException(
      code: isNetworkError ? networkErrorCode : unknownErrorCode,
      statusCode: exception.response?.statusCode,
    );
  }

  /// Code used when the server could not be reached.
  static const String networkErrorCode = 'NETWORK_ERROR';

  /// Code used when the response did not follow the standard error shape.
  static const String unknownErrorCode = 'UNKNOWN_ERROR';

  /// Machine-readable error code (see backend App\Enums\ErrorCode).
  final String code;

  /// Translated, user-facing message from the server, if any.
  final String? message;

  /// Extra data, e.g. `remaining_attempts` or validation `fields`.
  final Map<String, dynamic> details;

  /// HTTP status code, if a response was received.
  final int? statusCode;

  /// Whether the error means the session is no longer valid.
  bool get isUnauthenticated => code == 'UNAUTHENTICATED' || code == 'INVALID_REFRESH_TOKEN';

  /// Validation messages per field (`details.fields`), e.g. `{'login': ['…']}`.
  Map<String, List<String>> get fieldErrors {
    final fields = details['fields'];
    if (fields is! Map<String, dynamic>) return const {};

    return fields.map(
      (field, messages) => MapEntry(field, (messages as List<dynamic>).map((message) => message.toString()).toList()),
    );
  }

  @override
  String toString() => 'ApiException($code, $statusCode, $message)';
}
