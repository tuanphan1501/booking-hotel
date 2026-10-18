<?php

namespace Database\Seeders;

use App\Enums\MemberTierCode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the four membership tiers and their thresholds (BR-09).
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class MemberTierSeeder extends Seeder
{
    /**
     * Run the seeder.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('member_tiers')->insert([
            $this->tier(MemberTierCode::Member, 'Thành viên', 'メンバー', 0, 1.00, 1),
            $this->tier(MemberTierCode::Silver, 'Bạc', 'シルバー', 10_000_000, 1.10, 2),
            $this->tier(MemberTierCode::Gold, 'Vàng', 'ゴールド', 30_000_000, 1.25, 3),
            $this->tier(MemberTierCode::Platinum, 'Bạch kim', 'プラチナ', 80_000_000, 1.50, 4),
        ]);
    }

    /**
     * Build one member_tiers row.
     *
     * @param  MemberTierCode  $code  Tier code.
     * @param  string  $nameVi  Vietnamese display name.
     * @param  string  $nameJa  Japanese display name.
     * @param  int  $minSpending12m  Minimum spending over the last 12 months (VND) to reach the tier.
     * @param  float  $pointMultiplier  Multiplier applied when earning points.
     * @param  int  $sortOrder  Display order, lowest tier first.
     *
     * @return array<string, mixed>
     */
    private function tier(
        MemberTierCode $code,
        string $nameVi,
        string $nameJa,
        int $minSpending12m,
        float $pointMultiplier,
        int $sortOrder,
    ): array {
        return [
            'code' => $code->value,
            'name_vi' => $nameVi,
            'name_ja' => $nameJa,
            'min_spending_12m' => $minSpending12m,
            'point_multiplier' => $pointMultiplier,
            'sort_order' => $sortOrder,
        ];
    }
}
