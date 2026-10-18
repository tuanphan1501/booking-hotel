<?php

// Enum labels used by App\Enums\Concerns\HasLabel (English).
return [
    'UserStatus' => ['ACTIVE' => 'Active', 'LOCKED' => 'Locked', 'WITHDRAWN' => 'Withdrawn'],
    'AdminStatus' => ['ACTIVE' => 'Active', 'LOCKED' => 'Locked'],
    'RoleCode' => [
        'SUPER_ADMIN' => 'Super Admin',
        'HOTEL_MANAGER' => 'Hotel manager',
        'RECEPTIONIST' => 'Receptionist',
        'ACCOUNTANT' => 'Accountant',
        'MARKETING' => 'Marketing',
    ],
    'MemberTierCode' => ['MEMBER' => 'Member', 'SILVER' => 'Silver', 'GOLD' => 'Gold', 'PLATINUM' => 'Platinum'],
    'Gender' => ['MALE' => 'Male', 'FEMALE' => 'Female', 'OTHER' => 'Other'],
    'BookingStatus' => [
        'PENDING_PAYMENT' => 'Awaiting payment',
        'CONFIRMED' => 'Confirmed',
        'CHECKED_IN' => 'In house',
        'CHECKED_OUT' => 'Checked out',
        'CANCELLED' => 'Cancelled',
        'NO_SHOW' => 'No-show',
        'EXPIRED' => 'Expired',
    ],
];
