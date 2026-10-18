<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sets the application locale for API requests from the Accept-Language header.
 *
 * Supported locales come from config('booking.locales'); anything else falls back to the default
 * locale. Error messages in API responses are translated with this locale.
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class SetApiLocale
{
    /**
     * @param  Request  $request  Incoming API request.
     * @param  Closure(Request): Response  $next  Next middleware in the pipeline.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $preferredLocale = $request->getPreferredLanguage(config('booking.locales'));

        app()->setLocale($preferredLocale ?? config('app.locale'));

        return $next($request);
    }
}
