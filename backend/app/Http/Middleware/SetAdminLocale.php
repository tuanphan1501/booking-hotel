<?php

namespace App\Http\Middleware;

use App\Models\AdminUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sets the application locale for Web Admin pages.
 *
 * Signed-in administrators use their saved preference (admin_users.language, changed on AD-19
 * Personal settings). Before sign-in, the language picked on the login screen (AD-01) is read from
 * the session.
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class SetAdminLocale
{
    /** Session key holding the language picked on the login screen. */
    public const SESSION_KEY = 'admin_locale';

    /**
     * @param  Request  $request  Incoming Web Admin request.
     * @param  Closure(Request): Response  $next  Next middleware in the pipeline.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $administrator = $request->user('admin');

        $locale = $administrator instanceof AdminUser
            ? $administrator->language
            : $request->session()->get(self::SESSION_KEY);

        if (in_array($locale, config('booking.locales'), true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
