<?php

// Validation messages (Vietnamese). Add a rule here when a FormRequest starts using it.
return [
    'required' => ':Attribute là bắt buộc.',
    'string' => ':Attribute phải là chuỗi ký tự.',
    'email' => ':Attribute không đúng định dạng email.',
    'digits' => ':Attribute phải gồm :digits chữ số.',
    'in' => ':Attribute không hợp lệ.',
    'image' => ':Attribute phải là hình ảnh.',
    'mimes' => ':Attribute phải có định dạng: :values.',
    'dimensions' => ':Attribute phải có kích thước tối thiểu 96 × 96 px.',
    'uploaded' => 'Tải :attribute lên không thành công.',
    'phone' => 'Số điện thoại không hợp lệ.',
    'file' => ':Attribute phải là một tệp.',
    'max' => [
        'string' => ':Attribute không được dài quá :max ký tự.',
        'file' => ':Attribute không được lớn hơn :max KB.',
        'numeric' => ':Attribute không được lớn hơn :max.',
    ],
    'min' => [
        'string' => ':Attribute phải có ít nhất :min ký tự.',
        'numeric' => ':Attribute phải lớn hơn hoặc bằng :min.',
    ],

    'attributes' => [
        'login' => 'email hoặc số điện thoại',
        'password' => 'mật khẩu',
        'device_name' => 'tên thiết bị',
        'refresh_token' => 'refresh token',
        'email' => 'email',
        'totp_code' => 'mã xác thực 2 bước',
        'avatar' => 'ảnh đại diện',
        'full_name' => 'họ tên',
        'phone' => 'số điện thoại',
        'language' => 'ngôn ngữ',
        'locale' => 'ngôn ngữ',
    ],
];
