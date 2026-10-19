<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Enums\ErrorCode;
use App\Exceptions\BusinessException;
use App\Http\Controllers\Controller;
use App\Http\Middleware\SetAdminLocale;
use App\Http\Requests\Admin\Auth\LoginRequest;
use App\Http\Requests\Admin\Auth\VerifyLoginRequest;
use App\Services\Auth\AdminAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Web Admin sign-in (AD-01 password → AD-01b verification code) and sign-out.
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class LoginController extends Controller
{
    /**
     * @param  AdminAuthService  $adminAuthService  Password and verification-code checks.
     */
    public function __construct(private readonly AdminAuthService $adminAuthService) {}

    /**
     * Render the sign-in screen.
     *
     * - GET /admin/login (guests only)
     *
     * @return View
     */
    public function create(): View
    {
        return view('admin.auth.login');
    }

    /** Session key holding the id of the pending verification step. */
    public const CHALLENGE_SESSION_KEY = 'admin_login_challenge';

    /**
     * Check the email + password and continue to the verification step.
     *
     * - POST /admin/login
     * - On failure, redirects back with the email kept and the error shown under the email field.
     *
     * @param  LoginRequest  $request  Validated sign-in form.
     *
     * @return RedirectResponse Verification screen (AD-01b).
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        try {
            $challenge = $this->adminAuthService->startLogin(
                email: $request->string('email')->toString(),
                password: $request->string('password')->toString(),
                ipAddress: (string) $request->ip(),
            );
        } catch (BusinessException $exception) {
            return back()->withInput($request->only('email'))->withErrors(['email' => $exception->getMessage()]);
        }

        $request->session()->put(self::CHALLENGE_SESSION_KEY, $challenge->id);

        return redirect()->route('admin.login.verify');
    }

    /**
     * Render the verification code screen.
     *
     * - GET /admin/login/verify
     *
     * @param  Request  $request  Current request.
     *
     * @return View|RedirectResponse Sign-in screen when no verification step is pending.
     */
    public function verifyForm(Request $request): View|RedirectResponse
    {
        $challenge = $this->adminAuthService->pendingChallenge($request->session()->get(self::CHALLENGE_SESSION_KEY));

        if ($challenge === null) {
            return redirect()->route('admin.login')->withErrors(['email' => ErrorCode::VerificationExpired->message()]);
        }

        return view('admin.auth.verify', ['challenge' => $challenge]);
    }

    /**
     * Check the verification code and sign the administrator in.
     *
     * - POST /admin/login/verify
     * - Regenerates the session ID to prevent session fixation.
     *
     * @param  VerifyLoginRequest  $request  Validated code form.
     *
     * @return RedirectResponse Intended page, or the dashboard by default.
     */
    public function verify(VerifyLoginRequest $request): RedirectResponse
    {
        $challengeId = $request->session()->get(self::CHALLENGE_SESSION_KEY);

        try {
            $this->adminAuthService->verifyLogin($challengeId, $request->string('code')->toString());
        } catch (BusinessException $exception) {
            if (in_array($exception->errorCode, [ErrorCode::InvalidVerificationCode, ErrorCode::InvalidMfaCode], true)) {
                return back()->withErrors(['code' => $exception->getMessage()]);
            }

            $request->session()->forget(self::CHALLENGE_SESSION_KEY);

            return redirect()->route('admin.login')->withErrors(['email' => $exception->getMessage()]);
        }

        $request->session()->forget(self::CHALLENGE_SESSION_KEY);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Email a new verification code.
     *
     * - POST /admin/login/resend
     *
     * @param  Request  $request  Current request.
     *
     * @return RedirectResponse Back to the verification screen with a notice.
     */
    public function resend(Request $request): RedirectResponse
    {
        try {
            $this->adminAuthService->resendCode($request->session()->get(self::CHALLENGE_SESSION_KEY));
        } catch (BusinessException $exception) {
            if ($exception->errorCode === ErrorCode::ResendTooSoon) {
                return back()->withErrors(['code' => $exception->getMessage()]);
            }

            return redirect()->route('admin.login')->withErrors(['email' => $exception->getMessage()]);
        }

        return back()->with('status', __('admin.verify.resent'));
    }

    /**
     * Cancel the verification step and go back to the sign-in screen.
     *
     * - POST /admin/login/cancel
     *
     * @param  Request  $request  Current request.
     *
     * @return RedirectResponse
     */
    public function cancel(Request $request): RedirectResponse
    {
        $this->adminAuthService->cancelLogin($request->session()->pull(self::CHALLENGE_SESSION_KEY));

        return redirect()->route('admin.login');
    }

    /**
     * Sign the administrator out.
     *
     * - POST /admin/logout
     * - Invalidates the session and rotates the CSRF token.
     *
     * @param  Request  $request  Current request.
     *
     * @return RedirectResponse Sign-in screen with a "signed out" notice.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $this->adminAuthService->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('status', __('admin.auth.signed_out'));
    }

    /**
     * Change the language of the sign-in screen before the administrator is known.
     *
     * - POST /admin/language
     * - Stored in the session; after sign-in the saved admin_users.language takes over.
     *
     * @param  Request  $request  Request with a `locale` field ("vi" or "ja").
     *
     * @return RedirectResponse Back to the previous page.
     */
    public function switchLanguage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', Rule::in(config('booking.locales'))],
        ]);

        $request->session()->put(SetAdminLocale::SESSION_KEY, $validated['locale']);

        return back();
    }
}
