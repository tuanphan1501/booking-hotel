import 'dart:async';

import 'package:dio/dio.dart';

import '../config/app_config.dart';
import '../storage/token_storage.dart';
import 'api_exception.dart';

/// HTTP client for the Vivu Stay API.
///
/// - Sends `Accept-Language` so server messages come back in the user's language.
/// - Adds the Bearer access token to every request.
/// - On 401, exchanges the refresh token once (rotation) and retries the request; concurrent
///   401s share the same refresh call.
/// - Converts every failure into [ApiException].
///
/// Author: PH.Tuan · Created: 2026-10-18
class ApiClient {
  /// Creates the client.
  ///
  /// - [languageCode] returns the current UI language for `Accept-Language`.
  /// - [onSessionExpired] is called when refreshing fails, so the app can sign the user out.
  ApiClient({
    required TokenStorage tokenStorage,
    required String Function() languageCode,
    required void Function() onSessionExpired,
    Dio? dio,
  })  : _tokenStorage = tokenStorage,
        _languageCode = languageCode,
        _onSessionExpired = onSessionExpired,
        _dio = dio ??
            Dio(
              BaseOptions(
                baseUrl: AppConfig.apiBaseUrl,
                connectTimeout: AppConfig.connectTimeout,
                receiveTimeout: AppConfig.receiveTimeout,
                headers: {'Accept': 'application/json'},
              ),
            ) {
    _dio.interceptors.add(
      InterceptorsWrapper(onRequest: _attachHeaders, onError: _refreshOnUnauthorized),
    );
  }

  final Dio _dio;
  final TokenStorage _tokenStorage;
  final String Function() _languageCode;
  final void Function() _onSessionExpired;

  /// In-flight refresh shared by concurrent requests.
  Future<bool>? _refreshInFlight;

  /// Sends a GET request and returns the decoded JSON body.
  Future<Map<String, dynamic>> get(String path, {Map<String, dynamic>? query}) =>
      _send(() => _dio.get<Map<String, dynamic>>(path, queryParameters: query));

  /// Sends a POST request with a JSON body and returns the decoded JSON body (empty for 204).
  Future<Map<String, dynamic>> post(String path, {Object? body}) =>
      _send(() => _dio.post<Map<String, dynamic>>(path, data: body));

  Future<Map<String, dynamic>> _send(Future<Response<Map<String, dynamic>>> Function() request) async {
    try {
      final response = await request();
      return response.data ?? const {};
    } on DioException catch (exception) {
      throw ApiException.fromDioException(exception);
    }
  }

  Future<void> _attachHeaders(RequestOptions options, RequestInterceptorHandler handler) async {
    options.headers['Accept-Language'] = _languageCode();

    final tokens = await _tokenStorage.read();
    if (tokens != null && options.extra['skipAuth'] != true) {
      options.headers['Authorization'] = 'Bearer ${tokens.accessToken}';
    }

    handler.next(options);
  }

  Future<void> _refreshOnUnauthorized(DioException exception, ErrorInterceptorHandler handler) async {
    final request = exception.requestOptions;
    final isRetryable =
        exception.response?.statusCode == 401 && request.extra['retried'] != true && request.extra['skipAuth'] != true;

    if (!isRetryable) {
      handler.next(exception);
      return;
    }

    final refreshed = await (_refreshInFlight ??= _refreshTokens().whenComplete(() => _refreshInFlight = null));
    if (!refreshed) {
      _onSessionExpired();
      handler.next(exception);
      return;
    }

    try {
      request.extra['retried'] = true;
      handler.resolve(await _dio.fetch<dynamic>(request));
    } on DioException catch (retryException) {
      handler.next(retryException);
    }
  }

  /// Exchanges the stored refresh token for a new pair.
  ///
  /// Returns false when there is no refresh token or the server rejects it.
  Future<bool> _refreshTokens() async {
    final tokens = await _tokenStorage.read();
    if (tokens == null) return false;

    try {
      final response = await _dio.post<Map<String, dynamic>>(
        '/auth/refresh',
        data: {'refresh_token': tokens.refreshToken},
        options: Options(extra: {'skipAuth': true}),
      );
      final data = response.data!['data'] as Map<String, dynamic>;

      await _tokenStorage.save(
        StoredTokens(accessToken: data['access_token'] as String, refreshToken: data['refresh_token'] as String),
      );
      return true;
    } on DioException {
      await _tokenStorage.clear();
      return false;
    }
  }
}
