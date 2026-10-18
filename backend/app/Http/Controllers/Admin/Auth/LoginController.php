<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Enums\ErrorCode;
use App\Exceptions\BusinessException;
use App\Http\Controllers\Controller;
use App\Http\Middleware\SetAdminLocale;
use App\Http\Requests\Admin\Auth\LoginRequest;
use App\Services\Auth\AdminAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Web Admin sign-in and sign-out (AD-01).
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class LoginController extends Controller
{
    /**
     * @param  AdminAuthService  $adminAuthService  Credential and TOTP verification.
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

    /**
     * Sign the administrator in.
     *
     * - POST /admin/login
     * - Regenerates the session ID to prevent session fixation.
     * - On failure, redirects back with the email kept and the error shown under the related field
     *   (TOTP errors under the code field, every other error under the email field).
     *
     * @param  LoginRequest  $request  Validated sign-in form.
     *
     * @return RedirectResponse Intended page, or the dashboard by default.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        try {
            $this->adminAuthService->login(
                email: $request->string('email')->toString(),
                password: $request->string('password')->toString(),
                totpCode: $request->input('totp_code'),
                ipAddress: (string) $request->ip(),
            );
        } catch (BusinessException $exception) {
            $errorField = $exception->errorCode === ErrorCode::InvalidMfaCode ? 'totp_code' : 'email';

            return back()
                ->withInput($request->only('email'))
                ->withErrors([$errorField => $exception->getMessage()]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
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
