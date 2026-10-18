<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $isArabic ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; text-align: {{ $isArabic ? 'right' : 'left' }};">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.06); margin: 20px auto; border: 1px solid #eef2f5;">
        <!-- Header -->
        <tr>
            <td style="background-color: #1e1e2d; padding: 28px 30px; text-align: center;">
                <h1 style="color: #ffffff; font-size: 22px; margin: 0; font-weight: 700; letter-spacing: 0.5px;">
                    {{ config('app.name', 'Wasel Canada') }}
                </h1>
            </td>
        </tr>
        <!-- Content -->
        <tr>
            <td style="padding: 35px 30px 25px 30px;">
                <!-- Status Badge -->
                <div style="text-align: center; margin-bottom: 22px;">
                    <span style="display: inline-block; background-color: #fff5f8; color: #f1416c; font-weight: 700; font-size: 13px; padding: 8px 18px; border-radius: 30px; text-transform: uppercase; letter-spacing: 0.5px;">
                        ✕ {{ $isArabic ? 'تم الرفض' : 'Listing Rejected' }}
                    </span>
                </div>

                <h2 style="color: #181c32; font-size: 20px; font-weight: 700; margin: 0 0 14px 0; text-align: center;">
                    {{ $title }}
                </h2>

                @if(!empty($userName))
                <p style="color: #181c32; font-size: 15px; font-weight: 600; margin: 0 0 12px 0; text-align: {{ $isArabic ? 'right' : 'left' }};">
                    {{ $isArabic ? 'مرحباً ' . $userName . '،' : 'Hello ' . $userName . ',' }}
                </p>
                @endif

                <p style="color: #5e6278; font-size: 15px; line-height: 1.6; margin: 0 0 25px 0; text-align: {{ $isArabic ? 'right' : 'left' }};">
                    {{ $notificationMessage }}
                </p>

                <!-- Listing Card -->
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f9f9fc; border-radius: 10px; border: 1px solid #ebedf2; margin-bottom: 25px;">
                    <tr>
                        <td style="padding: 20px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td style="color: #7e8299; font-size: 13px; padding: 6px 0; width: 35%; font-weight: 500;">
                                        {{ $isArabic ? 'اسم الإعلان:' : 'Listing Title:' }}
                                    </td>
                                    <td style="color: #181c32; font-size: 14px; font-weight: 600; padding: 6px 0;">
                                        {{ $postTitle }}
                                    </td>
                                </tr>
                                @if($categoryName)
                                <tr>
                                    <td style="color: #7e8299; font-size: 13px; padding: 6px 0; font-weight: 500;">
                                        {{ $isArabic ? 'التصنيف:' : 'Category:' }}
                                    </td>
                                    <td style="color: #181c32; font-size: 14px; font-weight: 600; padding: 6px 0;">
                                        {{ $categoryName }}
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <td style="color: #7e8299; font-size: 13px; padding: 6px 0; font-weight: 500;">
                                        {{ $isArabic ? 'الحالة:' : 'Status:' }}
                                    </td>
                                    <td style="color: #f1416c; font-size: 14px; font-weight: 700; padding: 6px 0;">
                                        {{ $isArabic ? 'مرفوض' : 'Rejected' }}
                                    </td>
                                </tr>
                                @if(!empty($reason))
                                <tr>
                                    <td style="color: #7e8299; font-size: 13px; padding: 6px 0; font-weight: 500;">
                                        {{ $isArabic ? 'سبب الرفض:' : 'Rejection Reason:' }}
                                    </td>
                                    <td style="color: #d9214e; font-size: 14px; font-weight: 600; padding: 6px 0;">
                                        {{ $reason }}
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </td>
                    </tr>
                </table>

                <p style="color: #7e8299; font-size: 13px; line-height: 1.6; margin: 0; text-align: center;">
                    {{ $isArabic
                        ? 'يمكنك تعديل إعلانك للتأكد من موافقته لإرشادات المجتمع وإعادة تقديمه مرة أخرى. شكراً لتفهمك!'
                        : 'You can review our community guidelines, update your listing, and submit it again. Thank you for understanding!'
                    }}
                </p>
            </td>
        </tr>
        <!-- Footer -->
        <tr>
            <td style="background-color: #f9f9fc; padding: 22px 30px; text-align: center; border-top: 1px solid #ebedf2;">
                <p style="color: #a1a5b7; font-size: 12px; margin: 0;">
                    © {{ date('Y') }} {{ config('app.name', 'Wasel Canada') }}. {{ $isArabic ? 'جميع الحقوق محفوظة.' : 'All rights reserved.' }}
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
