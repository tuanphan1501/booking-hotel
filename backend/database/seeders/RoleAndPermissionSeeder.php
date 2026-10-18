<?php

namespace Database\Seeders;

use App\Enums\RoleCode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds administrator roles, permissions and the role-permission matrix (Basic Design §10).
 *
 * In the design matrix "○" (full access) maps to every permission of a module and "△" (read only)
 * maps to the module's ".view" permission only.
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Permissions grouped by module: code => [Vietnamese name, Japanese name].
     *
     * @var array<string, array<string, array{0: string, 1: string}>>
     */
    private const PERMISSIONS = [
        'dashboard' => [
            'dashboard.view' => ['Xem tổng quan', 'ダッシュボード閲覧'],
        ],
        'booking' => [
            'booking.view' => ['Xem đặt phòng', '予約閲覧'],
            'booking.edit' => ['Sửa đặt phòng, tạo đặt tại quầy', '予約編集・フロント予約'],
            'booking.checkin' => ['Gán phòng, check-in, check-out', '客室割当・チェックイン/アウト'],
            'booking.cancel' => ['Hủy đặt phòng', '予約キャンセル'],
        ],
        'payment' => [
            'payment.view' => ['Xem thanh toán', '決済閲覧'],
            'payment.collect' => ['Thu tiền tại quầy', 'フロント入金'],
            'refund.approve' => ['Duyệt hoàn tiền', '返金承認'],
        ],
        'inventory' => [
            'inventory.view' => ['Xem lịch giá & tồn kho', '料金・在庫閲覧'],
            'inventory.edit' => ['Sửa giá, tồn kho, ngừng bán', '料金・在庫編集'],
        ],
        'room' => [
            'room.view' => ['Xem phòng, loại phòng, gói giá', '客室・タイプ・プラン閲覧'],
            'room.edit' => ['Quản lý phòng, loại phòng, gói giá', '客室・タイプ・プラン管理'],
        ],
        'housekeeping' => [
            'housekeeping.view' => ['Xem tình trạng buồng', '客室状態閲覧'],
            'housekeeping.update' => ['Đổi tình trạng buồng', '客室状態更新'],
        ],
        'service' => [
            'service.view' => ['Xem dịch vụ thêm', 'オプション閲覧'],
            'service.edit' => ['Quản lý dịch vụ thêm', 'オプション管理'],
        ],
        'promotion' => [
            'promotion.view' => ['Xem khuyến mãi & voucher', 'プロモーション閲覧'],
            'promotion.edit' => ['Quản lý khuyến mãi & voucher', 'プロモーション管理'],
        ],
        'review' => [
            'review.view' => ['Xem đánh giá', 'レビュー閲覧'],
            'review.moderate' => ['Duyệt, ẩn, phản hồi đánh giá', 'レビュー承認・返信'],
        ],
        'customer' => [
            'customer.view' => ['Xem khách hàng & điểm', '顧客・ポイント閲覧'],
            'customer.edit' => ['Khóa tài khoản, điều chỉnh điểm', '顧客ロック・ポイント調整'],
        ],
        'report' => [
            'report.view' => ['Xem báo cáo', 'レポート閲覧'],
            'report.export' => ['Xuất báo cáo', 'レポート出力'],
        ],
        'system' => [
            'system.manage' => ['Chi nhánh, admin, phân quyền, cấu hình', '支店・管理者・権限・設定'],
        ],
    ];

    /**
     * Access level per role and module: "full" (○) or "view" (△). Missing modules mean no access.
     *
     * @var array<string, array<string, 'full'|'view'>>
     */
    private const MATRIX = [
        'SUPER_ADMIN' => [
            'dashboard' => 'full', 'booking' => 'full', 'payment' => 'full', 'inventory' => 'full',
            'room' => 'full', 'housekeeping' => 'full', 'service' => 'full', 'promotion' => 'full',
            'review' => 'full', 'customer' => 'full', 'report' => 'full', 'system' => 'full',
        ],
        'HOTEL_MANAGER' => [
            'dashboard' => 'full', 'booking' => 'full', 'payment' => 'full', 'inventory' => 'full',
            'room' => 'full', 'housekeeping' => 'full', 'service' => 'full', 'promotion' => 'view',
            'review' => 'full', 'customer' => 'view', 'report' => 'full',
        ],
        'RECEPTIONIST' => [
            'dashboard' => 'full', 'booking' => 'full', 'inventory' => 'view', 'room' => 'view',
            'housekeeping' => 'full', 'service' => 'view', 'review' => 'view', 'customer' => 'view',
        ],
        'ACCOUNTANT' => [
            'dashboard' => 'full', 'booking' => 'view', 'inventory' => 'view', 'promotion' => 'view',
            'customer' => 'view', 'report' => 'full',
        ],
        'MARKETING' => [
            'dashboard' => 'full', 'booking' => 'view', 'inventory' => 'view', 'room' => 'view',
            'service' => 'view', 'promotion' => 'full', 'review' => 'full', 'customer' => 'full',
            'report' => 'view',
        ],
    ];

    /**
     * Permissions granted outside the module matrix (single cells of Basic Design §10).
     *
     * @var array<string, list<string>>
     */
    private const EXTRA_PERMISSIONS = [
        'RECEPTIONIST' => ['payment.view', 'payment.collect'],
        'ACCOUNTANT' => ['payment.view', 'payment.collect', 'refund.approve'],
    ];

    /**
     * Run the seeder.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            $this->role(RoleCode::SuperAdmin, 'Super Admin (trụ sở)', '本部管理者'),
            $this->role(RoleCode::HotelManager, 'Quản lý chi nhánh', '支店マネージャー'),
            $this->role(RoleCode::Receptionist, 'Lễ tân', 'フロント'),
            $this->role(RoleCode::Accountant, 'Kế toán', '経理'),
            $this->role(RoleCode::Marketing, 'Marketing', 'マーケティング'),
        ]);

        foreach (self::PERMISSIONS as $module => $permissions) {
            foreach ($permissions as $code => [$nameVi, $nameJa]) {
                DB::table('permissions')->insert([
                    'code' => $code,
                    'module' => $module,
                    'name_vi' => $nameVi,
                    'name_ja' => $nameJa,
                ]);
            }
        }

        $roleIdsByCode = DB::table('roles')->pluck('id', 'code');
        $permissionIdsByCode = DB::table('permissions')->pluck('id', 'code');

        foreach (self::MATRIX as $roleCode => $accessByModule) {
            $grantedCodes = array_merge(
                $this->permissionCodesFor($accessByModule),
                self::EXTRA_PERMISSIONS[$roleCode] ?? [],
            );

            DB::table('role_permissions')->insert(array_map(
                fn (string $permissionCode): array => [
                    'role_id' => $roleIdsByCode[$roleCode],
                    'permission_id' => $permissionIdsByCode[$permissionCode],
                ],
                array_values(array_unique($grantedCodes)),
            ));
        }
    }

    /**
     * Build one roles row. Seeded roles are system roles and cannot be deleted.
     *
     * @return array<string, mixed>
     */
    private function role(RoleCode $code, string $nameVi, string $nameJa): array
    {
        return [
            'code' => $code->value,
            'name_vi' => $nameVi,
            'name_ja' => $nameJa,
            'description' => $code->name,
            'is_system' => true,
        ];
    }

    /**
     * Resolve the permission codes for a role's module access levels.
     *
     * @param  array<string, 'full'|'view'>  $accessByModule  Access level per module.
     *
     * @return list<string>
     */
    private function permissionCodesFor(array $accessByModule): array
    {
        $codes = [];

        foreach ($accessByModule as $module => $accessLevel) {
            $moduleCodes = array_keys(self::PERMISSIONS[$module]);

            $codes = array_merge($codes, $accessLevel === 'full'
                ? $moduleCodes
                : array_values(array_filter($moduleCodes, fn (string $code): bool => str_ends_with($code, '.view'))));
        }

        return $codes;
    }
}
