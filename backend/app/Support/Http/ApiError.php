<?php

namespace App\Support\Http;

use App\Enums\ErrorCode;
use App\Exceptions\BusinessException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Builds the standard API error response: { "code", "message", "details" }.
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
final class ApiError
{
    /**
     * Create an error response for the given error code.
     *
     * @param  ErrorCode  $errorCode  Machine-readable error code; also decides the HTTP status.
     * @param  string|null  $userMessage  Translated message; defaults to the error code's message.
     * @param  array<string, mixed>  $details  Extra data for the client.
     * @param  array<string, string>  $headers  Additional HTTP headers, e.g. Retry-After.
     */
    public static function response(
        ErrorCode $errorCode,
        ?string $userMessage = null,
        array $details = [],
        array $headers = [],
    ): JsonResponse {
        return new JsonResponse([
            'code' => $errorCode->value,
            'message' => $userMessage ?? $errorCode->message(),
            'details' => (object) $details,
        ], $errorCode->httpStatus(), $headers);
    }

    /**
     * Convert any exception thrown while handling an API request into the standard error response.
     *
     * @param  Throwable  $exception  The exception that reached the exception handler.
     */
    public static function fromThrowable(Throwable $exception): JsonResponse
    {
        return match (true) {
            $exception instanceof BusinessException => self::response(
                $exception->errorCode,
                $exception->getMessage(),
                $exception->details,
            ),
            $exception instanceof ValidationException => self::response(
                ErrorCode::ValidationError,
                details: ['fields' => $exception->errors()],
            ),
            $exception instanceof AuthenticationException => self::response(ErrorCode::Unauthenticated),
            $exception instanceof AuthorizationException,
            $exception instanceof AccessDeniedHttpException => self::response(ErrorCode::Forbidden),
            $exception instanceof ModelNotFoundException,
            $exception instanceof NotFoundHttpException => self::response(ErrorCode::NotFound),
            $exception instanceof MethodNotAllowedHttpException => self::response(ErrorCode::MethodNotAllowed),
            $exception instanceof ThrottleRequestsException => self::response(
                ErrorCode::TooManyRequests,
                headers: $exception->getHeaders(),
            ),
            $exception instanceof HttpExceptionInterface && $exception->getStatusCode() < 500 => new JsonResponse([
                'code' => 'HTTP_'.$exception->getStatusCode(),
                'message' => $exception->getMessage() ?: ErrorCode::ServerError->message(),
                'details' => (object) [],
            ], $exception->getStatusCode(), $exception->getHeaders()),
            default => self::response(
                ErrorCode::ServerError,
                details: config('app.debug')
                    ? ['exception' => $exception::class, 'error' => $exception->getMessage()]
                    : [],
            ),
        };
    }
}
