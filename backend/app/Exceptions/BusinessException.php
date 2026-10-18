<?php

namespace App\Exceptions;

use App\Enums\ErrorCode;
use RuntimeException;

/**
 * A domain rule was violated (e.g. wrong password, account locked, room sold out).
 *
 * Thrown from the service layer. The API renders it as { code, message, details } with the HTTP
 * status of its error code; Web Admin controllers turn it into form errors.
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class BusinessException extends RuntimeException
{
    /**
     * @param  ErrorCode  $errorCode  Machine-readable error code returned to the client.
     * @param  string|null  $userMessage  Translated message; defaults to the error code's message.
     * @param  array<string, mixed>  $details  Extra data for the client, e.g. ['remaining_attempts' => 3].
     */
    public function __construct(
        public readonly ErrorCode $errorCode,
        ?string $userMessage = null,
        public readonly array $details = [],
    ) {
        parent::__construct($userMessage ?? $errorCode->message());
    }

    /**
     * Get the HTTP status code for this exception.
     */
    public function httpStatus(): int
    {
        return $this->errorCode->httpStatus();
    }
}
