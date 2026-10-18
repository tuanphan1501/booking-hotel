<?php

// Enum labels used by App\Enums\Concerns\HasLabel (Japanese).
return [
    'UserStatus' => ['ACTIVE' => '有効', 'LOCKED' => 'ロック', 'WITHDRAWN' => '退会'],
    'AdminStatus' => ['ACTIVE' => '有効', 'LOCKED' => 'ロック'],
    'RoleCode' => [
        'SUPER_ADMIN' => '本部管理者',
        'HOTEL_MANAGER' => '支店マネージャー',
        'RECEPTIONIST' => 'フロント',
        'ACCOUNTANT' => '経理',
        'MARKETING' => 'マーケティング',
    ],
    'MemberTierCode' => ['MEMBER' => 'メンバー', 'SILVER' => 'シルバー', 'GOLD' => 'ゴールド', 'PLATINUM' => 'プラチナ'],
    'Gender' => ['MALE' => '男性', 'FEMALE' => '女性', 'OTHER' => 'その他'],
    'BookingStatus' => [
        'PENDING_PAYMENT' => '決済待ち',
        'CONFIRMED' => '予約確定',
        'CHECKED_IN' => '滞在中',
        'CHECKED_OUT' => 'チェックアウト済',
        'CANCELLED' => 'キャンセル',
        'NO_SHOW' => '不泊',
        'EXPIRED' => '期限切れ',
    ],
];
