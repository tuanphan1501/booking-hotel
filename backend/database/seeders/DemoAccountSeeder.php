<?php

namespace Database\Seeders;

use App\Enums\AdminStatus;
use App\Enums\MemberTierCode;
use App\Enums\RoleCode;
use App\Enums\UserStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds demo administrators (one per role) and demo customers for local development and demos.
 *
 * Every demo account uses the password from SEED_DEMO_PASSWORD (see .env.example).
 * Never run in production — DatabaseSeeder skips this seeder there.
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class DemoAccountSeeder extends Seeder
{
    /**
     * Run the seeder.
     *
     * @return void
     */
    public function run(): void
    {
        $passwordHash = Hash::make((string) config('booking.seed.demo_password'));

        $this->createAdministrators($passwordHash);
        $this->createCustomers($passwordHash);
    }

    /**
     * Create one administrator per role. Branch roles belong to Vivu Stay Đà Nẵng.
     *
     * @param  string  $passwordHash  Bcrypt hash shared by all demo accounts.
     *
     * @return void
     */
    private function createAdministrators(string $passwordHash): void
    {
        $roleIdsByCode = DB::table('roles')->pluck('id', 'code');
        $danangHotelId = DB::table('hotels')->where('code', 'DN')->value('id');

        $administrators = [
            [(string) config('booking.seed.super_admin_email'), 'PH.Tuan', RoleCode::SuperAdmin, null, 'vi'],
            ['le.hanh@vivustay.vn', 'Lê Hạnh', RoleCode::HotelManager, $danangHotelId, 'vi'],
            ['letan.danang@vivustay.vn', 'Phạm Thu Trang', RoleCode::Receptionist, $danangHotelId, 'vi'],
            ['ketoan@vivustay.vn', 'Sato Kenji', RoleCode::Accountant, null, 'ja'],
            ['marketing@vivustay.vn', 'Võ Minh Châu', RoleCode::Marketing, null, 'vi'],
        ];

        foreach ($administrators as [$email, $fullName, $roleCode, $hotelId, $language]) {
            DB::table('admin_users')->insert([
                'hotel_id' => $hotelId,
                'role_id' => $roleIdsByCode[$roleCode->value],
                'email' => $email,
                'password_hash' => $passwordHash,
                'full_name' => $fullName,
                'language' => $language,
                'status' => AdminStatus::Active->value,
            ]);
        }
    }

    /**
     * Create demo customers, including "Nguyễn Văn Minh" (Gold) used throughout the mobile mockups.
     *
     * @param  string  $passwordHash  Bcrypt hash shared by all demo accounts.
     *
     * @return void
     */
    private function createCustomers(string $passwordHash): void
    {
        $tierIdsByCode = DB::table('member_tiers')->pluck('id', 'code');

        $customers = [
            ['M00012845', 'minh.nguyen@gmail.com', '+84901234567', 'Nguyễn Văn Minh', MemberTierCode::Gold, 2_450, 41_200_000, 'vi', UserStatus::Active],
            ['M00013002', 'mai.anh@gmail.com', '+84912345678', 'Trần Mai Anh', MemberTierCode::Member, 120, 2_940_000, 'vi', UserStatus::Active],
            ['M00013117', 'yuki.tanaka@example.jp', '+819012345678', 'Tanaka Yuki', MemberTierCode::Gold, 1_800, 35_600_000, 'ja', UserStatus::Active],
            ['M00013250', 'locked.user@example.com', '+84987654321', 'Đỗ Văn Khóa', MemberTierCode::Member, 0, 0, 'vi', UserStatus::Locked],
        ];

        foreach ($customers as [$memberCode, $email, $phone, $fullName, $tierCode, $pointBalance, $spending12m, $language, $status]) {
            DB::table('users')->insert([
                'member_code' => $memberCode,
                'email' => $email,
                'phone' => $phone,
                'password_hash' => $passwordHash,
                'full_name' => $fullName,
                'language' => $language,
                'member_tier_id' => $tierIdsByCode[$tierCode->value],
                'point_balance' => $pointBalance,
                'spending_12m' => $spending12m,
                'status' => $status->value,
                'email_verified_at' => now(),
            ]);
        }

        $this->giveVouchersToMinh();
    }

    /**
     * Put the three mockup vouchers into the wallet of the main demo customer (MB-14).
     *
     * @return void
     */
    private function giveVouchersToMinh(): void
    {
        $minhUserId = DB::table('users')->where('member_code', 'M00012845')->value('id');

        DB::table('vouchers')->whereIn('code', ['WELCOME200', 'GOLD15', 'DANANG11'])->pluck('id')
            ->each(fn (int $voucherId) => DB::table('user_vouchers')->insert([
                'user_id' => $minhUserId,
                'voucher_id' => $voucherId,
                'source' => 'SAVED',
            ]));
    }
}
