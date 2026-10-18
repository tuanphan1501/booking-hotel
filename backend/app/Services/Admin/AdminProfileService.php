<?php

namespace App\Services\Admin;

use App\Data\Admin\AdminProfileData;
use App\Models\AdminUser;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Http\UploadedFile;

/**
 * Updates an administrator's own profile and profile picture (AD-19 Personal settings).
 *
 * - Pictures are stored on the "public" disk under avatars/admins/{id}/ with a random file name,
 *   so a new upload never serves a cached old picture.
 * - The previous picture file is deleted after a replacement or removal.
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class AdminProfileService
{
    /** Directory of administrator pictures on the public disk. */
    private const AVATAR_DIRECTORY = 'avatars/admins';

    /** Disk holding the pictures; must be web-accessible (php artisan storage:link). */
    private Filesystem $publicDisk;

    /**
     * @param  FilesystemManager  $filesystemManager  Resolves the "public" disk.
     */
    public function __construct(FilesystemManager $filesystemManager)
    {
        $this->publicDisk = $filesystemManager->disk('public');
    }

    /**
     * Save the editable profile fields.
     *
     * @param  AdminUser  $administrator  Administrator editing their own profile.
     * @param  AdminProfileData  $profileData  New values.
     *
     * @return AdminUser The updated administrator.
     */
    public function updateProfile(AdminUser $administrator, AdminProfileData $profileData): AdminUser
    {
        $administrator->forceFill([
            'full_name' => $profileData->fullName,
            'phone' => $profileData->phoneNumber,
            'language' => $profileData->language,
        ])->save();

        return $administrator;
    }

    /**
     * Store a new profile picture and delete the previous one.
     *
     * @param  AdminUser  $administrator  Administrator uploading the picture.
     * @param  UploadedFile  $avatarFile  Validated image file (JPEG, PNG or WebP).
     *
     * @return AdminUser The updated administrator.
     */
    public function replaceAvatar(AdminUser $administrator, UploadedFile $avatarFile): AdminUser
    {
        $previousAvatarPath = $administrator->avatar_path;
        $newAvatarPath = $avatarFile->store(self::AVATAR_DIRECTORY.'/'.$administrator->id, 'public');

        $administrator->forceFill(['avatar_path' => $newAvatarPath])->save();
        $this->deleteFile($previousAvatarPath);

        return $administrator;
    }

    /**
     * Remove the profile picture; the initials badge is shown again.
     *
     * @param  AdminUser  $administrator  Administrator removing the picture.
     *
     * @return AdminUser The updated administrator.
     */
    public function removeAvatar(AdminUser $administrator): AdminUser
    {
        $previousAvatarPath = $administrator->avatar_path;

        $administrator->forceFill(['avatar_path' => null])->save();
        $this->deleteFile($previousAvatarPath);

        return $administrator;
    }

    /**
     * Delete a picture file if it exists.
     *
     * @param  string|null  $avatarPath  Path on the public disk, or null.
     *
     * @return void
     */
    private function deleteFile(?string $avatarPath): void
    {
        if ($avatarPath !== null && $this->publicDisk->exists($avatarPath)) {
            $this->publicDisk->delete($avatarPath);
        }
    }
}
