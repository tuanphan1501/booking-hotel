<?php

/*
|--------------------------------------------------------------------------
| Vivu Stay business settings
|--------------------------------------------------------------------------
|
| Values referenced by the Basic Design (§7 business rules, §12 non-functional requirements).
| Rates that change at runtime (tax, service charge, point rules) live in the system_settings table.
|
*/

return [

    'auth' => [
        // Mobile App: short-lived access token + rotating refresh token.
        'access_token_minutes' => (int) env('AUTH_ACCESS_TOKEN_MINUTES', 15),
        'refresh_token_days' => (int) env('AUTH_REFRESH_TOKEN_DAYS', 30),

        // Temporary lockout after too many failed sign-ins (AD-01: 5 failures → 15 minutes).
        'max_attempts' => (int) env('AUTH_MAX_ATTEMPTS', 5),
        'lockout_minutes' => (int) env('AUTH_LOCKOUT_MINUTES', 15),
    ],

    // Shared password of the demo accounts created by DemoAccountSeeder (never used in production).
    'seed' => [
        'demo_password' => env('SEED_DEMO_PASSWORD', 'VivuStay@2026'),

        // Email of the demo Super Admin (head office, every hotel).
        'super_admin_email' => env('SEED_SUPER_ADMIN_EMAIL', 'phtuancntt@gmail.com'),
    ],

    // Local test mailbox (Mailpit) linked from the sign-in code screen in APP_ENV=local.
    'dev_mailbox_url' => env('DEV_MAILBOX_URL', 'http://localhost:8025'),

    // Locales with UI translations (lang/vi, lang/ja, lang/en).
    'locales' => ['vi', 'ja', 'en'],

];
