<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Administrator permission stored in the `permissions` table, e.g. "booking.view".
 *
 * - Granted to roles through the `role_permissions` pivot table.
 * - Codes are checked with Gate abilities: `@can('booking.view')`, `->middleware('can:booking.view')`.
 *
 * @property int $id
 * @property string $code
 * @property string $module
 * @property string $name_vi
 * @property string $name_ja
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class Permission extends Model
{
    /**
     * Attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['code', 'module', 'name_vi', 'name_ja'];
}
