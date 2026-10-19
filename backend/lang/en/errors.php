<?php

// API / business error messages keyed by App\Enums\ErrorCode (English).
return [
    'VALIDATION_ERROR' => 'Some fields are invalid.',
    'UNAUTHENTICATED' => 'Your session has expired. Please sign in again.',
    'FORBIDDEN' => 'You do not have permission to perform this action.',
    'NOT_FOUND' => 'The requested data was not found.',
    'METHOD_NOT_ALLOWED' => 'This method is not supported.',
    'TOO_MANY_REQUESTS' => 'Too many requests. Please try again later.',
    'SERVER_ERROR' => 'Something went wrong. Please try again later.',

    'INVALID_CREDENTIALS' => 'Incorrect email/phone number or password. :remaining attempts left.',
    'INVALID_MFA_CODE' => 'Incorrect two-factor code. :remaining attempts left.',
    'ACCOUNT_LOCKED' => 'This account is locked. Please contact support.',
    'ACCOUNT_WITHDRAWN' => 'This account has been deleted.',
    'LOGIN_THROTTLED' => 'Too many failed sign-in attempts. Try again in :minutes minutes.',
    'INVALID_REFRESH_TOKEN' => 'Your session has expired. Please sign in again.',
    'INVALID_VERIFICATION_CODE' => 'The verification code is incorrect. :remaining attempts left.',
    'VERIFICATION_EXPIRED' => 'The verification code has expired or was cancelled. Please sign in again.',
    'GOOGLE_ACCOUNT_NOT_ALLOWED' => 'This Google account has no admin access. Contact a Super Admin.',
    'RESEND_TOO_SOON' => 'Please wait :seconds seconds before requesting a new code.',

    'ROOM_NOT_AVAILABLE' => 'This room type is sold out for the selected dates.',
    'INVALID_STATUS_TRANSITION' => 'The booking cannot be changed from its current status.',
    'BOOKING_EXPIRED' => 'The room hold has expired. Please book again.',
    'CANCELLATION_NOT_ALLOWED' => 'This booking cannot be cancelled.',
];
