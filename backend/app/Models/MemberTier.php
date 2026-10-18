<?php

namespace App\Models;

use App\Enums\MemberTierCode;
use Illuminate\Database\Eloquent\Model;

/**
 * Membership tier stored in the `member_tiers` table (BR-09).
 *
 * - A customer's tier is re-evaluated monthly from spending over the last 12 months (batch BAT04).
 * - `point_multiplier` scales the points earned at check-out (BR-08).
 *
 * @property int $id
 * @property MemberTierCode $code
 * @property string $name_vi
 * @property string $name_ja
 * @property int $min_spending_12m
 * @property string $point_multiplier
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class MemberTier extends Model
{
    /**
     * Attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['code', 'name_vi', 'name_ja', 'min_spending_12m', 'point_multiplier', 'benefits', 'sort_order'];

    /**
     * Get the attribute casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'code' => MemberTierCode::class,
            'min_spending_12m' => 'integer',
            'point_multiplier' => 'decimal:2',
        ];
    }

    /**
     * Get the tier name in the current locale.
     *
     * - Vietnamese and Japanese names come from the table (editable by administrators).
     * - Other locales (e.g. English) use the translated label of the tier code.
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
