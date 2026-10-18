<?php

// Validation messages (Japanese). Add a rule here when a FormRequest starts using it.
return [
    'required' => ':attributeは必須です。',
    'string' => ':attributeは文字列で入力してください。',
    'email' => ':attributeの形式が正しくありません。',
    'digits' => ':attributeは:digits桁の数字で入力してください。',
    'in' => ':attributeが正しくありません。',
    'image' => ':attributeには画像を指定してください。',
    'mimes' => ':attributeは:values形式のファイルを指定してください。',
    'dimensions' => ':attributeは96×96px以上の画像を指定してください。',
    'uploaded' => ':attributeのアップロードに失敗しました。',
    'phone' => '電話番号の形式が正しくありません。',
    'file' => ':attributeにはファイルを指定してください。',
    'max' => [
        'string' => ':attributeは:max文字以内で入力してください。',
        'file' => ':attributeは:maxKB以下のファイルを指定してください。',
        'numeric' => ':attributeは:max以下で入力してください。',
    ],
    'min' => [
        'string' => ':attributeは:min文字以上で入力してください。',
        'numeric' => ':attributeは:min以上で入力してください。',
    ],

    'attributes' => [
        'login' => 'メールアドレスまたは電話番号',
        'password' => 'パスワード',
        'device_name' => '端末名',
        'refresh_token' => 'リフレッシュトークン',
        'email' => 'メールアドレス',
        'totp_code' => '2段階認証コード',
        'avatar' => 'プロフィール画像',
        'full_name' => '氏名',
        'phone' => '電話番号',
        'language' => '言語',
        'locale' => '言語',
    ],
];
