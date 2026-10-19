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
    'INVALID_VERIFICATION_CODE' => 'Mã xác nhận không đúng. Còn :remaining lần thử.',
    'VERIFICATION_EXPIRED' => 'Mã xác nhận đã hết hạn hoặc bị hủy. Vui lòng đăng nhập lại.',
    'GOOGLE_ACCOUNT_NOT_ALLOWED' => 'Tài khoản Google này chưa được cấp quyền quản trị. Liên hệ Super Admin.',
    'RESEND_TOO_SOON' => 'Vui lòng chờ :seconds giây trước khi gửi lại mã.',

    'ROOM_NOT_AVAILABLE' => 'Loại phòng này đã hết phòng trong ngày bạn chọn.',
    'INVALID_STATUS_TRANSITION' => 'Không thể chuyển đặt phòng từ trạng thái hiện tại.',
    'BOOKING_EXPIRED' => 'Đã hết thời gian giữ phòng. Vui lòng đặt lại.',
    'CANCELLATION_NOT_ALLOWED' => 'Đặt phòng này không thể hủy.',
];
