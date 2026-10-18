<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds runtime business settings (tax, service charge, points, booking limits).
 *
 * Values follow Basic Design §7 (BR-01 … BR-11) and can be changed later on AD-18 System settings.
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class SystemSettingSeeder extends Seeder
{
    /**
     * Setting key => [value, description].
     *
     * @var array<string, array{0: string, 1: string}>
     */
    private const SETTINGS = [
        'tax.vat_percent' => ['8', 'VAT % applied to (discounted total + service charge) — BR-03 ⑥'],
        'fee.service_charge_percent' => ['5', 'Service charge % of the discounted total — BR-03 ⑤'],
        'booking.hold_minutes' => ['15', 'Minutes a PENDING_PAYMENT booking holds inventory — BR-04'],
        'booking.max_nights' => ['30', 'Maximum nights per booking — BR-01'],
        'booking.max_rooms' => ['5', 'Maximum rooms per booking — BR-01'],
        'booking.max_advance_days' => ['365', 'Latest check-in date, in days from today — BR-01'],
        'point.earn_unit' => ['10000', 'VND spent (before tax) per earned point — BR-08'],
        'point.redeem_value' => ['100', 'VND value of one point when redeemed — BR-03 ⑧'],
        'point.max_redeem_percent' => ['50', 'Maximum share of total_amount payable with points — BR-03 ⑧'],
        'point.expiry_months' => ['12', 'Months before earned points expire — BR-08'],
        'point.review_bonus' => ['50', 'Points granted when a review is published — BR-08'],
        'review.max_days_after_checkout' => ['30', 'Days after check-out a review can be written — BR-11'],
        'review.max_images' => ['5', 'Maximum images per review — BR-11'],
    ];

    /**
     * Run the seeder.
     *
     * @return void
     */
    public function run(): void
    {
        foreach (self::SETTINGS as $key => [$value, $description]) {
            DB::table('system_settings')->insert([
                'key' => $key,
                'value' => $value,
                'description' => $description,
            ]);
        }
    }
}
