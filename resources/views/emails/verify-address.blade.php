<!doctype html>
<html lang="{{ $isArabic ? 'ar' : 'en' }}" dir="{{ $isArabic ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ $isArabic ? 'تأكيد بريدك الإلكتروني' : 'Verify Your Email Address' }}</title>
</head>
<body style="margin:0;padding:0;background:#f8f3f1;color:#432b39;font-family:Tahoma,Arial,sans-serif;-webkit-text-size-adjust:100%;">
    <div style="display:none;font-size:1px;color:#f8f3f1;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;">
        {{ $isArabic ? 'خطوة واحدة لتبدأ قصتك في خطوبة. أكّد بريدك الإلكتروني الآن.' : 'One small step before your story begins. Confirm your email address.' }}
    </div>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;background:#f8f3f1;">
        <tr><td align="center" style="padding:34px 14px 44px;">
            <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="width:100%;max-width:600px;border-collapse:separate;border-spacing:0;background:#ffffff;border:1px solid #eadce1;border-radius:22px;overflow:hidden;">
                <tr><td style="padding:25px 34px;background:#45263a;color:#fff7f8;">
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;"><tr>
                        <td>
                            <table role="presentation" cellspacing="0" cellpadding="0" style="border-collapse:collapse;"><tr>
                                <td width="44" valign="middle"><img src="{{ asset('assets/media/email/khotobah-mark.png') }}" width="44" height="36" alt="" style="display:block;width:44px;height:36px;border:0;"></td>
                                <td valign="middle" style="padding-{{ $isArabic ? 'right' : 'left' }}:9px;color:#fff7f8;font-size:18px;font-weight:800;line-height:1.2;">{{ $isArabic ? 'خطوبة' : 'Khotobah' }}<br><span style="color:#e6b8c9;font-size:10px;font-weight:500;letter-spacing:1px;">{{ $isArabic ? 'Khotobah' : 'خطوبة' }}</span></td>
                            </tr></table>
                        </td>
                        <td align="{{ $isArabic ? 'left' : 'right' }}" style="font-size:10px;letter-spacing:2px;color:#e8c3d0;">✦ &nbsp; {{ $isArabic ? 'بداية الحكاية' : 'A NEW BEGINNING' }}</td>
                    </tr></table>
                </td></tr>
                <tr><td style="height:8px;background:#a14b6e;font-size:0;line-height:0;">&nbsp;</td></tr>
                <tr><td style="padding:38px 34px 14px;">
                    <table role="presentation" cellspacing="0" cellpadding="0" style="border-collapse:collapse;"><tr>
                        <td align="center" valign="middle" width="68" height="68" style="width:68px;height:68px;border-radius:19px;background:#f6e7ed;border:1px solid #e9cfda;color:#9b4369;font-size:32px;line-height:68px;">✉</td>
                        <td style="padding-{{ $isArabic ? 'right' : 'left' }}:15px;color:#a35071;font-size:11px;font-weight:800;letter-spacing:1px;">{{ $isArabic ? 'رسالة خاصة لك' : 'A NOTE FOR YOU' }}<br><span style="color:#d6a5b8;font-size:20px;line-height:24px;">✦ ───</span></td>
                    </tr></table>
                    <h1 style="margin:30px 0 12px;color:#3d2535;font-size:29px;line-height:1.4;font-weight:800;">{{ $isArabic ? 'خطوة واحدة، وتبدأ الحكاية.' : 'One step, then your story begins.' }}</h1>
                    <p style="margin:0 0 13px;color:#765d6b;font-size:15px;line-height:1.9;">{{ $isArabic ? 'مرحباً' : 'Hello' }} {{ $userName }},</p>
                    <p style="margin:0;color:#765d6b;font-size:15px;line-height:1.9;">{{ $isArabic ? 'يسعدنا انضمامك إلى خطوبة. أكّد بريدك الإلكتروني لنطمئن أن هذا العنوان يخصك، ثم أكمل ملفك وابدأ اكتشاف من يشاركك ما يهمك.' : 'We’re glad you joined Khotobah. Confirm your email address, complete your profile, and begin discovering people who share what matters to you.' }}</p>
                </td></tr>
                <tr><td style="padding:21px 34px 34px;">
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;background:#fbf6f6;border:1px solid #efdee4;border-radius:14px;"><tr><td style="padding:19px 21px;">
                        <p style="margin:0 0 8px;color:#a14b6e;font-size:11px;font-weight:800;letter-spacing:1px;">01 &nbsp; {{ $isArabic ? 'تأكيد العنوان' : 'CONFIRM YOUR ADDRESS' }}</p>
                        <p style="margin:0;color:#614858;font-size:13px;line-height:1.7;">{{ $isArabic ? 'اضغط الزر أدناه للتحقق من بريدك الإلكتروني والمتابعة.' : 'Use the button below to verify your email and continue.' }}</p>
                    </td></tr></table>
                    <table role="presentation" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin-top:25px;"><tr>
                        <td bgcolor="#963e62" style="border-radius:10px;background:#963e62;">
                            <a href="{{ $verificationUrl }}" style="display:inline-block;padding:15px 28px;border:1px solid #963e62;border-radius:10px;color:#ffffff;text-decoration:none;font-size:14px;font-weight:800;line-height:1.4;">{{ $isArabic ? 'تأكيد بريدي الإلكتروني' : 'Verify Email Address' }} &nbsp; ↗</a>
                        </td>
                    </tr></table>
                    <p style="margin:22px 0 0;color:#907985;font-size:12px;line-height:1.8;">{{ $isArabic ? 'إذا لم تنشئ حساباً في خطوبة، يمكنك تجاهل هذه الرسالة.' : 'If you did not create a Khotobah account, you can safely ignore this email.' }}</p>
                </td></tr>
                <tr><td style="padding:25px 34px;border-top:1px solid #f0e5e8;background:#fffafa;">
                    <p style="margin:0 0 8px;color:#6f5965;font-size:12px;font-weight:700;">{{ $isArabic ? 'هل تواجه مشكلة في فتح الزر؟' : 'Having trouble with the button?' }}</p>
                    <p style="margin:0;color:#907985;font-size:11px;line-height:1.7;">{{ $isArabic ? 'انسخ الرابط التالي والصقه في متصفحك:' : 'Copy and paste this link into your browser:' }}</p>
                    <p style="margin:8px 0 0;font-size:11px;line-height:1.7;word-break:break-all;"><a href="{{ $verificationUrl }}" style="color:#984465;text-decoration:underline;">{{ $verificationUrl }}</a></p>
                </td></tr>
            </table>
            <p style="max-width:600px;margin:20px auto 0;color:#a28b96;font-size:11px;line-height:1.7;text-align:center;">خطوبة · Khotobah &nbsp; ✦ &nbsp; {{ $isArabic ? 'حيث تبدأ الحكاية' : 'Where stories begin' }}</p>
        </td></tr>
    </table>
</body>
</html>
