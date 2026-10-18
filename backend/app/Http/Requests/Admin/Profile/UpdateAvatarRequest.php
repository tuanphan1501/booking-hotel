<?php

namespace App\Http\Requests\Admin\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

/**
 * Validates POST /admin/profile/avatar (AD-19 Personal settings — profile picture).
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class UpdateAvatarRequest extends FormRequest
{
    /** Largest accepted file size, in kilobytes. */
    public const MAX_FILE_SIZE_KB = 2048;

    /** Smallest accepted width and height, in pixels (the avatar is shown at up to 96 px @2x). */
    public const MIN_DIMENSION_PX = 96;

    /**
     * Determine whether the request is authorized.
     *
     * - Every signed-in administrator may change their own picture ("auth:admin" route middleware).
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules.
     *
     * - JPEG, PNG or WebP only (no SVG: it can carry scripts), up to 2 MB, at least 96 × 96 px.
     *
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'avatar' => [
                'required',
                File::image()
                    ->types(['jpg', 'jpeg', 'png', 'webp'])
                    ->max(self::MAX_FILE_SIZE_KB)
                    ->dimensions(Rule::dimensions()->minWidth(self::MIN_DIMENSION_PX)->minHeight(self::MIN_DIMENSION_PX)),
            ],
        ];
    }

    /**
     * Get the display names of the fields used in validation messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['avatar' => __('admin.profile.avatar')];
    }
}
