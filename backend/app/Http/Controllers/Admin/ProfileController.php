<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Profile\UpdateAvatarRequest;
use App\Http\Requests\Admin\Profile\UpdateProfileRequest;
use App\Models\AdminUser;
use App\Services\Admin\AdminProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * AD-19 Personal settings: the signed-in administrator's own profile and profile picture.
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class ProfileController extends Controller
{
    /**
     * @param  AdminProfileService  $adminProfileService  Saves profile fields and pictures.
     */
    public function __construct(private readonly AdminProfileService $adminProfileService) {}

    /**
     * Render the personal settings screen.
     *
     * - GET /admin/profile
     *
     * @param  Request  $request  Authenticated request.
     *
     * @return View
     */
    public function edit(Request $request): View
    {
        return view('admin.profile.edit', [
            'administrator' => $this->administrator($request)->load(['role', 'hotel']),
        ]);
    }

    /**
     * Save the profile form.
     *
     * - PUT /admin/profile
     * - A language change applies immediately on the next page.
     *
     * @param  UpdateProfileRequest  $request  Validated profile form.
     *
     * @return RedirectResponse Back to the settings screen with a success notice.
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $updatedAdministrator = $this->adminProfileService->updateProfile($this->administrator($request), $request->toProfileData());

        app()->setLocale($updatedAdministrator->language);

        return redirect()->route('admin.profile.edit')->with('status', __('admin.profile.saved'));
    }

    /**
     * Upload a new profile picture.
     *
     * - POST /admin/profile/avatar (multipart/form-data, field "avatar")
     *
     * @param  UpdateAvatarRequest  $request  Validated upload.
     *
     * @return RedirectResponse Back to the settings screen with a success notice.
     */
    public function updateAvatar(UpdateAvatarRequest $request): RedirectResponse
    {
        $this->adminProfileService->replaceAvatar($this->administrator($request), $request->file('avatar'));

        return redirect()->route('admin.profile.edit')->with('status', __('admin.profile.avatar_saved'));
    }

    /**
     * Remove the profile picture.
     *
     * - DELETE /admin/profile/avatar
     *
     * @param  Request  $request  Authenticated request.
     *
     * @return RedirectResponse Back to the settings screen with a success notice.
     */
    public function destroyAvatar(Request $request): RedirectResponse
    {
        $this->adminProfileService->removeAvatar($this->administrator($request));

        return redirect()->route('admin.profile.edit')->with('status', __('admin.profile.avatar_removed'));
    }

    /**
     * Get the signed-in administrator.
     *
     * @param  Request  $request  Authenticated request.
     *
     * @return AdminUser
     */
    private function administrator(Request $request): AdminUser
    {
        /** @var AdminUser $administrator */
        $administrator = $request->user('admin');

        return $administrator;
    }
}
