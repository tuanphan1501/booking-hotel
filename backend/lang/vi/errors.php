<?php

// API / business error messages keyed by App\Enums\ErrorCode (Vietnamese).
return [
    'VALIDATION_ERROR' => 'Dữ liệu chưa hợp lệ.',
    'UNAUTHENTICATED' => 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.',
    'FORBIDDEN' => 'Bạn không có quyền thực hiện thao tác này.',
    'NOT_FOUND' => 'Không tìm thấy dữ liệu.',
    'METHOD_NOT_ALLOWED' => 'Phương thức không được hỗ trợ.',
    'TOO_MANY_REQUESTS' => 'Bạn thao tác quá nhanh. Vui lòng thử lại sau.',
    'SERVER_ERROR' => 'Đã có lỗi xảy ra. Vui lòng thử lại sau.',

    'INVALID_CREDENTIALS' => 'Email/số điện thoại hoặc mật khẩu chưa đúng. Còn :remaining lần thử.',
    'INVALID_MFA_CODE' => 'Mã xác thực 2 bước không đúng. Còn :remaining lần thử.',
    'ACCOUNT_LOCKED' => 'Tài khoản đã bị khóa. Vui lòng liên hệ hỗ trợ.',
    'ACCOUNT_WITHDRAWN' => 'Tài khoản đã bị xóa.',
    'LOGIN_THROTTLED' => 'Bạn đã nhập sai quá nhiều lần. Thử lại sau :minutes phút.',
    'INVALID_REFRESH_TOKEN' => 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.',
];
