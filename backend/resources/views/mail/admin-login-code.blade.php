{{-- Sign-in verification code email (AD-01b). Data: $administrator, $code, $validMinutes. --}}
<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ __('admin.verify.mail_subject', ['code' => $code]) }}</title></head>
<body style="margin:0;background:#fbf6f4;font-family:'Be Vietnam Pro','Segoe UI',Arial,sans-serif;color:#2b1a16">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:32px 16px">
    <tr><td align="center">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;background:#fff;border-radius:18px;box-shadow:0 0 0 1px #efe3df">
            <tr><td style="padding:28px 32px 0">
                <table role="presentation" cellpadding="0" cellspacing="0"><tr>
                    <td style="width:40px;height:40px;background:#b42318;border-radius:11px;text-align:center;color:#fff;font-weight:700;font-size:22px;line-height:40px">V</td>
                    <td style="padding-left:12px;font-size:20px;font-weight:700">Vivu <span style="color:#b42318">Stay</span> <span style="color:#7a615a;font-weight:500;font-size:14px">Admin</span></td>
                </tr></table>
            </td></tr>
            <tr><td style="padding:24px 32px 8px">
                <h1 style="margin:0 0 8px;font-size:22px">{{ __('admin.verify.mail_title') }}</h1>
                <p style="margin:0;font-size:15px;line-height:22px;color:#5c4640">{{ __('admin.verify.mail_intro', ['name' => $administrator->full_name]) }}</p>
            </td></tr>
            <tr><td align="center" style="padding:20px 32px">
                <div style="display:inline-block;padding:14px 22px;border-radius:14px;background:#fdece9;color:#8a1a12;font-size:34px;font-weight:700;letter-spacing:10px;font-variant-numeric:tabular-nums">{{ $code }}</div>
                <p style="margin:12px 0 0;font-size:13px;color:#7a615a">{{ __('admin.verify.mail_expires', ['minutes' => $validMinutes]) }}</p>
            </td></tr>
            <tr><td style="padding:8px 32px 28px">
                <p style="margin:0;padding:12px 14px;border-radius:12px;background:#fdf0e1;color:#6e4505;font-size:13px;line-height:19px">{{ __('admin.verify.mail_warning') }}</p>
            </td></tr>
        </table>
        <p style="margin:16px 0 0;font-size:12px;color:#9c8781">© {{ date('Y') }} Vivu Stay</p>
    </td></tr>
</table>
</body>
</html>
