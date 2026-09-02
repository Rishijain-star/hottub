<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email Address</title>
</head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:'Segoe UI',system-ui,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:520px;background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                    <tr>
                        <td style="background:#0d9488;padding:24px 28px;">
                            <p style="margin:0;font-size:18px;font-weight:700;color:#ffffff;">Hot Tub Buyer</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            <h1 style="margin:0 0 12px;font-size:22px;line-height:1.3;">Verify your email address</h1>
                            <p style="margin:0 0 20px;font-size:15px;line-height:1.6;color:#475569;">
                                Your verification code is:
                            </p>
                            <p style="margin:0 0 24px;font-size:32px;font-weight:700;letter-spacing:6px;color:#0d9488;">{{ $otpCode }}</p>
                            <p style="margin:0 0 8px;font-size:14px;line-height:1.6;color:#475569;">
                                This code will expire in 10 minutes.
                            </p>
                            <p style="margin:0;font-size:14px;line-height:1.6;color:#64748b;">
                                If you did not request this code, please ignore this email.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
