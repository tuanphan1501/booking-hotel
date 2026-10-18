<?php

namespace App\Models;

use App\Enums\RoleCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Administrator role stored in the `roles` table.
 *
 * - Permissions are linked through the `role_permissions` table.
 * - System roles (`is_system` = true) are seeded and cannot be deleted.
 *
 * @property int $id
 * @property RoleCode $code
 * @property string $name_vi
 * @property string $name_ja
 * @property bool $is_system
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class Role extends Model
{
    /**
     * Attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['code', 'name_vi', 'name_ja', 'description', 'is_system'];

    /**
     * Get the attribute casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'code' => RoleCode::class,
            'is_system' => 'boolean',
        ];
    }

    /**
     * Get the permissions granted to the role.
     *
     * - The `role_permissions` pivot table has no timestamps.
     *
     * @return BelongsToMany<Permission, $this>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    /**
     * Get the role name in the current locale.
     *
     * - Vietnamese and Japanese names come from the table (editable by administrators).
     * - Other locales (e.g. English) use the translated label of the role code.
     *
     * @return string
     */
    public function localizedName(): string
    {
        return match (app()->getLocale()) {
            'vi' => $this->name_vi,
            'ja' => $this->name_ja,
            default => $this->code->label(),
        };
    }
}
