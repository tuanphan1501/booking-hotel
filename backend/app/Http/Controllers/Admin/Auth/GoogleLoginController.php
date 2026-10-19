<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Exceptions\BusinessException;
use App\Http\Controllers\Controller;
use App\Services\Auth\AdminAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Throwable;

/**
 * "Sign in with Google" for the Web Admin (AD-01).
 *
 * - Enabled when GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET are set (Google Cloud Console → OAuth client,
 *   authorized redirect URI = {admin URL}/admin/auth/google/callback).
 * - Only Gmail / Google Workspace addresses that already belong to an administrator are accepted.
 *
 * @author PH.Tuan
 * @created 2026-10-19
 */
class GoogleLoginController extends Controller
{
    /**
     * @param  AdminAuthService  $adminAuthService  Signs the matching administrator in.
     */
    public function __construct(private readonly AdminAuthService $adminAuthService) {}

    /**
     * Determine whether Google sign-in is configured.
     *
     * @return bool
     */
    public static function isEnabled(): bool
    {
        return filled(config('services.google.client_id')) && filled(config('services.google.client_secret'));
    }

    /**
     * Send the administrator to Google's consent screen.
     *
     * - GET /admin/auth/google
     *
     * @return RedirectResponse|SymfonyRedirectResponse
     */
    public function redirect(): RedirectResponse|SymfonyRedirectResponse
    {
        abort_unless(self::isEnabled(), 404);

        return Socialite::driver('google')->redirectUrl(url(config('services.google.redirect')))->scopes(['email'])->redirect();
    }

    /**
     * Handle Google's answer and sign the administrator in.
     *
     * - GET /admin/auth/google/callback
     *
     * @param  Request  $request  Callback request with the authorization code.
     *
     * @return RedirectResponse Dashboard, TOTP step, or sign-in screen with an error.
     */
    public function callback(Request $request): RedirectResponse
    {
        abort_unless(self::isEnabled(), 404);

        try {
            $googleUser = Socialite::driver('google')->redirectUrl(url(config('services.google.redirect')))->user();
        } catch (Throwable) {
            return redirect()->route('admin.login')->withErrors(['email' => __('admin.auth.google_failed')]);
        }

        if (($googleUser->getRaw()['email_verified'] ?? false) !== true || blank($googleUser->getEmail())) {
            return redirect()->route('admin.login')->withErrors(['email' => __('admin.auth.google_failed')]);
        }

        try {
            $challenge = $this->adminAuthService->loginWithGoogle((string) $googleUser->getEmail());
        } catch (BusinessException $exception) {
            return redirect()->route('admin.login')->withErrors(['email' => $exception->getMessage()]);
        }

        if ($challenge !== null) {
            $request->session()->put(LoginController::CHALLENGE_SESSION_KEY, $challenge->id);

            return redirect()->route('admin.login.verify');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }
}
