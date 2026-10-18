<?php

namespace App\Data\Admin;

/**
 * Editable fields of an administrator's own profile (AD-19 Personal settings).
 *
 * - Email, role and hotel are managed by a Super Admin on AD-17 and are not part of this data.
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
final readonly class AdminProfileData
{
    /**
     * @param  string  $fullName  Display name, shown in the top bar and audit logs.
     * @param  string|null  $phoneNumber  Contact phone in E.164, or null to clear it.
     * @param  string  $language  UI and notification language ("vi" or "ja").
     */
    public function __construct(
        public string $fullName,
        public ?string $phoneNumber,
        public string $language,
    ) {}
}
