<?php

namespace App\Enums;

/**
 * Machine-readable API error codes.
 *
 * Every API error response has the shape { "code": string, "message": string, "details": object }
 * (Basic Design §9). Clients branch on `code`; `message` is translated using Accept-Language and is
 * safe to show to end users.
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
enum ErrorCode: string
{
    // Generic
    case ValidationError = 'VALIDATION_ERROR';
    case Unauthenticated = 'UNAUTHENTICATED';
    case Forbidden = 'FORBIDDEN';
    case NotFound = 'NOT_FOUND';
    case MethodNotAllowed = 'METHOD_NOT_ALLOWED';
    case TooManyRequests = 'TOO_MANY_REQUESTS';
    case ServerError = 'SERVER_ERROR';

    // Authentication
    case InvalidCredentials = 'INVALID_CREDENTIALS';
    case InvalidMfaCode = 'INVALID_MFA_CODE';
    case AccountLocked = 'ACCOUNT_LOCKED';
    case AccountWithdrawn = 'ACCOUNT_WITHDRAWN';
    case LoginThrottled = 'LOGIN_THROTTLED';
    case InvalidRefreshToken = 'INVALID_REFRESH_TOKEN';
    case InvalidVerificationCode = 'INVALID_VERIFICATION_CODE';
    case VerificationExpired = 'VERIFICATION_EXPIRED';
    case ResendTooSoon = 'RESEND_TOO_SOON';
    case GoogleAccountNotAllowed = 'GOOGLE_ACCOUNT_NOT_ALLOWED';

    // Booking
    case RoomNotAvailable = 'ROOM_NOT_AVAILABLE';
    case InvalidStatusTransition = 'INVALID_STATUS_TRANSITION';
    case BookingExpired = 'BOOKING_EXPIRED';
    case CancellationNotAllowed = 'CANCELLATION_NOT_ALLOWED';

    /**
     * Get the HTTP status code returned together with this error.
     */
    public function httpStatus(): int
    {
        return match ($this) {
            self::ValidationError, self::InvalidMfaCode, self::InvalidVerificationCode, self::VerificationExpired => 422,
            self::RoomNotAvailable, self::InvalidStatusTransition, self::BookingExpired, self::CancellationNotAllowed => 409,
            self::Unauthenticated, self::InvalidCredentials, self::InvalidRefreshToken => 401,
            self::Forbidden, self::GoogleAccountNotAllowed, self::AccountLocked, self::AccountWithdrawn => 403,
            self::NotFound => 404,
            self::MethodNotAllowed => 405,
            self::TooManyRequests, self::LoginThrottled, self::ResendTooSoon => 429,
            self::ServerError => 500,
        };
    }

    /**
     * Get the translated, user-facing message for this error.
     *
     * @param  array<string, scalar>  $replacements  Placeholder values, e.g. ['remaining' => 3].
     */
    public function message(array $replacements = []): string
    {
        return __('errors.'.$this->value, $replacements);
    }
}
