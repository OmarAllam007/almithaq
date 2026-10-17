<!DOCTYPE html>
<html lang="{{ $lang }}" dir="{{ $lang === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $lang === 'ar' ? 'تذكير من خطوبة' : 'Reminder from Khotobah' }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #fef0f7;
            font-family:
                {{ $lang === 'ar' ? "'Segoe UI', Tahoma, Arial, sans-serif" : "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" }}
            ;
            color: #374151;
            -webkit-text-size-adjust: 100%;
        }

        .wrapper {
            width: 100%;
            padding: 40px 16px;
            background-color: #fef0f7;
        }

        .container {
            max-width: 580px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(208, 46, 121, 0.10);
        }

        /* ── Header ─────────────────────────────────────── */
        .header {
            background: linear-gradient(135deg, #d02e79 0%, #a8205e 100%);
            padding: 40px 40px 32px;
            text-align: center;
        }

        .header-logo {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .header-logo-icon {
            width: 44px;
            height: 44px;
            background: rgba(255, 255, 255, 1);
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .header-logo-text {
            font-size: 22px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: 0.5px;
        }

        .header-logo-text span {
            color: #fde8f3;
        }

        .header-icon-wrap {
            margin-bottom: 16px;
        }

        .header-icon-wrap span {
            font-size: 48px;
            display: inline-block;
        }

        .header-title {
            font-size: 26px;
            font-weight: 700;
            color: #ffffff;
            line-height: 1.3;
            margin-bottom: 8px;
        }

        .header-subtitle {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.75);
            line-height: 1.5;
        }

        /* ── Body ────────────────────────────────────────── */
        .body {
            padding: 36px 40px;
        }

        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 16px;
        }

        .text {
            font-size: 15px;
            color: #4b5563;
            line-height: 1.75;
            margin-bottom: 20px;
        }

        /* ── Steps ───────────────────────────────────────── */
        .steps-label {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #d02e79;
            margin-bottom: 14px;
        }

        .steps {
            margin-bottom: 28px;
        }

        .step {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 14px;
        }

        .step:last-child {
            margin-bottom: 0;
        }

        .step-num {
            flex-shrink: 0;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #d02e79;
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .step-num--done {
            background: #d02e79;
            opacity: 0.35;
        }

        .step-num--next {
            background: #d02e79;
            box-shadow: 0 0 0 4px #fde8f3;
        }

        .step-body {
            padding-top: 5px;
        }

        .step-title {
            font-size: 14px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 2px;
        }

        .step-title--muted {
            color: #9ca3af;
        }

        .step-desc {
            font-size: 13px;
            color: #6b7280;
            line-height: 1.5;
        }

        /* ── CTA Button ──────────────────────────────────── */
        .cta-wrap {
            text-align: center;
            margin-bottom: 28px;
        }

        .cta-btn {
            display: inline-block;
            padding: 15px 44px;
            background: #d02e79;
            color: #ffffff !important;
            font-size: 15px;
            font-weight: 700;
            border-radius: 10px;
            text-decoration: none;
            letter-spacing: 0.3px;
            box-shadow: 0 4px 16px rgba(208, 46, 121, 0.35);
        }

        .cta-note {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 10px;
            word-break: break-all;
        }

        /* ── Info card ───────────────────────────────────── */
        .info-card {
            background: #fef0f7;
            border: 1px solid #f9a8d4;
            border-radius: 12px;
            padding: 18px 22px;
            font-size: 13px;
            color: #9d174d;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .info-card strong {
            color: #831843;
        }

        /* ── Divider ─────────────────────────────────────── */
        .divider {
            border: none;
            border-top: 1px solid #f3f4f6;
            margin: 0 0 24px;
        }

        /* ── Footer ──────────────────────────────────────── */
        .footer {
            background: #fef0f7;
            border-top: 1px solid #f9a8d4;
            padding: 24px 40px;
            text-align: center;
        }

        .footer-text {
            font-size: 12px;
            color: #9ca3af;
            line-height: 1.6;
        }

        .footer-text a {
            color: #d02e79;
            text-decoration: none;
        }

        @media only screen and (max-width: 600px) {

            .header,
            .body {
                padding: 28px 24px;
            }

            .footer {
                padding: 20px 24px;
            }

            .header-title {
                font-size: 22px;
            }
        }

        .brand__mark {
            color: #d02e79;
            display: inline-flex;
            flex-shrink: 0;
        }

        .brand__mark svg {
            width: 36px;
            height: 25px;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="container">

            {{-- ── Header ──────────────────────────────────────── --}}
            <div class="header">
                <div class="header-logo">
                    <div class="header-logo-icon">
                        <span class="brand__mark" aria-hidden="true">
                            <svg viewBox="0 0 40 28" fill="none">
                                <circle cx="14" cy="14" r="9" stroke="currentColor" stroke-width="2.2" />
                                <circle cx="26" cy="14" r="9" stroke="currentColor" stroke-width="2.2" opacity="0.55" />
                            </svg>
                        </span>
                    </div>
                    <div class="header-logo-text">
                        @if ($lang === 'ar')
                            <span>خطوبة</span>
                        @else
                            <span>Khotobah</span>
                        @endif
                    </div>
                </div>

                {{-- <div class="header-icon-wrap"><span>📧</span></div> --}}

                @if ($lang === 'ar')
                    <div class="header-title">{{ $stage === 'email' ? 'تحقق من بريدك الإلكتروني' : 'أكمل ملفك الشخصي' }}</div>
                    <div class="header-subtitle">
                        {{ $stage === 'email' ? 'خطوتان فقط تفصلانك عن العضوية الكاملة' : 'خطوة واحدة فقط تفصلك عن العضوية الكاملة' }}
                    </div>
                @else
                    <div class="header-title">{{ $stage === 'email' ? 'Verify Your Email Address' : 'Complete Your Profile' }}</div>
                    <div class="header-subtitle">
                        {{ $stage === 'email' ? 'Just two steps away from full membership' : 'Just one step away from full membership' }}
                    </div>
                @endif
            </div>

            {{-- ── Body ────────────────────────────────────────── --}}
            <div class="body">

                @if ($lang === 'ar')
                    <div class="greeting">السلام عليكم، {{ $userName }} 👋</div>

                    @if ($stage === 'email')
                        <p class="text">
                            شكراً لتسجيلك في منصة <strong>خطوبة</strong>. لاحظنا أن بريدك الإلكتروني لم يُفعَّل بعد.
                            تفعيل البريد الإلكتروني هو أول خطوة لفتح ملفك الشخصي أمام الأعضاء الآخرين.
                        </p>
                    @else
                        <p class="text">
                            شكراً لتفعيل بريدك الإلكتروني في منصة <strong>خطوبة</strong>. لاحظنا أن ملفك الشخصي لم يكتمل بعد،
                            ولن يظهر ملفك للأعضاء الآخرين حتى تُكمل بياناتك.
                        </p>
                    @endif

                    {{-- Steps --}}
                    <div class="steps-label">خارطة طريقك للعضوية الكاملة</div>
                    <div class="steps">
                        <div class="step">
                            <div class="step-num {{ $stage === 'email' ? 'step-num--next' : 'step-num--done' }}">١</div>
                            <div class="step-body">
                                <div class="step-title {{ $stage === 'email' ? '' : 'step-title--muted' }}">
                                    تفعيل البريد الإلكتروني @if ($stage === 'email') ← أنت هنا @else ✓ تم @endif
                                </div>
                                <div class="step-desc">تأكيد ملكيتك للبريد الإلكتروني المسجَّل في حسابك.</div>
                            </div>
                        </div>
                        <div class="step">
                            <div class="step-num {{ $stage === 'profile' ? 'step-num--next' : 'step-num--done' }}">٢</div>
                            <div class="step-body">
                                <div class="step-title {{ $stage === 'profile' ? '' : 'step-title--muted' }}">
                                    إكمال الملف الشخصي @if ($stage === 'profile') ← أنت هنا @endif
                                </div>
                                <div class="step-desc">بياناتك الأساسية ومواصفات شريك الحياة الذي تبحث عنه.</div>
                            </div>
                        </div>
                    </div>

                    {{-- CTA --}}
                    <div class="cta-wrap">
                        <a href="{{ $actionUrl }}" class="cta-btn">
                            {{ $stage === 'email' ? 'تفعيل البريد الإلكتروني الآن' : 'إكمال ملفي الشخصي الآن' }}
                        </a>
                        <div class="cta-note">أو انسخ هذا الرابط في متصفحك:<br>{{ $actionUrl }}</div>
                    </div>

                    {{-- Info --}}
                    <div class="info-card">
                        @if ($stage === 'email')
                            💡 <strong>لم يصلك رابط التفعيل؟</strong>
                            بعد الضغط على الزر أعلاه، يمكنك طلب إعادة إرسال رابط التفعيل من الصفحة التي ستظهر لك.
                            تحقق أيضاً من مجلد الرسائل غير المرغوب فيها (Spam).
                        @else
                            💡 <strong>لن يستغرق الأمر سوى دقائق.</strong>
                            كلما اكتمل ملفك الشخصي، زادت فرص ظهورك للأعضاء المناسبين.
                        @endif
                    </div>

                @else
                    <div class="greeting">Hello, {{ $userName }} 👋</div>

                    @if ($stage === 'email')
                        <p class="text">
                            Thank you for registering on <strong>Khotobah</strong>. We noticed your email address hasn't been
                            verified yet. Verifying your email is the very first step to making your profile visible to
                            other members.
                        </p>
                    @else
                        <p class="text">
                            Thank you for verifying your email on <strong>Khotobah</strong>. We noticed your profile isn't
                            complete yet — until it is, other members won't be able to find you.
                        </p>
                    @endif

                    {{-- Steps --}}
                    <div class="steps-label">Your path to full membership</div>
                    <div class="steps">
                        <div class="step">
                            <div class="step-num {{ $stage === 'email' ? 'step-num--next' : 'step-num--done' }}">1</div>
                            <div class="step-body">
                                <div class="step-title {{ $stage === 'email' ? '' : 'step-title--muted' }}">
                                    Verify your email address @if ($stage === 'email') ← You are here @else ✓ Done @endif
                                </div>
                                <div class="step-desc">Confirm the email address registered to your account.</div>
                            </div>
                        </div>
                        <div class="step">
                            <div class="step-num {{ $stage === 'profile' ? 'step-num--next' : 'step-num--done' }}">2</div>
                            <div class="step-body">
                                <div class="step-title {{ $stage === 'profile' ? '' : 'step-title--muted' }}">
                                    Complete your profile @if ($stage === 'profile') ← You are here @endif
                                </div>
                                <div class="step-desc">Your essential details and what you're looking for in a partner.</div>
                            </div>
                        </div>
                    </div>

                    {{-- CTA --}}
                    <div class="cta-wrap">
                        <a href="{{ $actionUrl }}" class="cta-btn">
                            {{ $stage === 'email' ? 'Verify My Email Now' : 'Complete My Profile Now' }}
                        </a>
                        <div class="cta-note">Or copy this link into your browser:<br>{{ $actionUrl }}</div>
                    </div>

                    {{-- Info --}}
                    <div class="info-card">
                        @if ($stage === 'email')
                            💡 <strong>Didn't receive the verification email?</strong>
                            After clicking the button above, you can request a new verification link from the page that
                            appears. Also check your spam or junk folder.
                        @else
                            💡 <strong>It only takes a few minutes.</strong>
                            The more complete your profile, the more likely the right members are to find you.
                        @endif
                    </div>
                @endif

                <hr class="divider" />

                @if ($lang === 'ar')
                    <p class="text" style="font-size:13px; color:#9ca3af;">
                        إذا لم تقم بإنشاء حساب على منصة خطوبة يمكنك أن تشرفنا بالتسجيل أو يمكنك تجاهل هذا البريد الإلكتروني بأمان.
                    </p>
                @else
                    <p class="text" style="font-size:13px; color:#9ca3af;">
                        If you did not create an account on Khotobah, you can join us or safely ignore this email.
                    </p>
                @endif

            </div>

            {{-- ── Footer ───────────────────────────────────────── --}}
            <div class="footer">
                <p class="footer-text">
                    {{ $appName }} &mdash;
                    @if ($lang === 'ar')
                        منصة الزواج الإسلامي الموثوقة
                    @else
                        The Trusted Islamic Marriage Platform
                    @endif
                </p>
                <p class="footer-text" style="margin-top:6px;">
                    <a href="{{ $appUrl }}">{{ $appUrl }}</a>
                </p>
            </div>

        </div>
    </div>
</body>

</html>