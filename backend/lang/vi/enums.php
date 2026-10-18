<?php

// Enum labels used by App\Enums\Concerns\HasLabel (Vietnamese).
return [
    'UserStatus' => ['ACTIVE' => 'Hoạt động', 'LOCKED' => 'Bị khóa', 'WITHDRAWN' => 'Đã rút khỏi'],
    'AdminStatus' => ['ACTIVE' => 'Hoạt động', 'LOCKED' => 'Bị khóa'],
    'RoleCode' => [
        'SUPER_ADMIN' => 'Super Admin',
        'HOTEL_MANAGER' => 'Quản lý chi nhánh',
        'RECEPTIONIST' => 'Lễ tân',
        'ACCOUNTANT' => 'Kế toán',
        'MARKETING' => 'Marketing',
    ],
    'MemberTierCode' => ['MEMBER' => 'Thành viên', 'SILVER' => 'Bạc', 'GOLD' => 'Vàng', 'PLATINUM' => 'Bạch kim'],
    'Gender' => ['MALE' => 'Nam', 'FEMALE' => 'Nữ', 'OTHER' => 'Khác'],
    'BookingStatus' => [
        'PENDING_PAYMENT' => 'Chờ thanh toán',
        'CONFIRMED' => 'Đã xác nhận',
        'CHECKED_IN' => 'Đang ở',
        'CHECKED_OUT' => 'Đã trả phòng',
        'CANCELLED' => 'Đã hủy',
        'NO_SHOW' => 'Không đến',
        'EXPIRED' => 'Hết hạn',
    ],
];
