<?php

// API / business error messages keyed by App\Enums\ErrorCode (Japanese).
return [
    'VALIDATION_ERROR' => '入力内容に誤りがあります。',
    'UNAUTHENTICATED' => 'ログインの有効期限が切れました。再度ログインしてください。',
    'FORBIDDEN' => 'この操作を行う権限がありません。',
    'NOT_FOUND' => 'データが見つかりません。',
    'METHOD_NOT_ALLOWED' => 'このメソッドはサポートされていません。',
    'TOO_MANY_REQUESTS' => '操作が多すぎます。しばらくしてから再度お試しください。',
    'SERVER_ERROR' => 'エラーが発生しました。しばらくしてから再度お試しください。',

    'INVALID_CREDENTIALS' => 'メールアドレス/電話番号またはパスワードが正しくありません。残り:remaining回です。',
    'INVALID_MFA_CODE' => '2段階認証コードが正しくありません。残り:remaining回です。',
    'ACCOUNT_LOCKED' => 'アカウントはロックされています。サポートにお問い合わせください。',
    'ACCOUNT_WITHDRAWN' => 'このアカウントは削除されています。',
    'LOGIN_THROTTLED' => 'ログインに続けて失敗しました。:minutes分後に再度お試しください。',
    'INVALID_REFRESH_TOKEN' => 'ログインの有効期限が切れました。再度ログインしてください。',
    'INVALID_VERIFICATION_CODE' => '確認コードが正しくありません。残り:remaining回です。',
    'VERIFICATION_EXPIRED' => '確認コードの有効期限が切れたか、無効になりました。もう一度ログインしてください。',
    'GOOGLE_ACCOUNT_NOT_ALLOWED' => 'このGoogleアカウントには管理者権限がありません。本部管理者へご連絡ください。',
    'RESEND_TOO_SOON' => ':seconds秒後にもう一度お試しください。',

    'ROOM_NOT_AVAILABLE' => '選択した日程ではこの客室タイプは満室です。',
    'INVALID_STATUS_TRANSITION' => '現在のステータスからは変更できません。',
    'BOOKING_EXPIRED' => '客室の確保時間が過ぎました。もう一度予約してください。',
    'CANCELLATION_NOT_ALLOWED' => 'この予約はキャンセルできません。',
];
