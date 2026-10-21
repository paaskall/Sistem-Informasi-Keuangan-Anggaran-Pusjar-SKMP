<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kode OTP</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:40px 0;">
        <tr>
            <td align="center">
                <table width="500" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 6px rgba(0,0,0,0.05);">
                    <tr>
                        <td style="background:linear-gradient(135deg,#0A669D,#0DACFF);padding:30px;text-align:center;">
                            <h1 style="color:#ffffff;margin:0;font-size:24px;">SIKAP</h1>
                            <p style="color:#e0f2fe;margin:5px 0 0;font-size:12px;">Sistem Informasi Keuangan & Anggaran</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:40px 30px;">
                            <p style="color:#374151;font-size:16px;margin:0 0 10px;">Halo, <strong>{{ $userName }}</strong></p>
                            <p style="color:#6b7280;font-size:14px;margin:0 0 20px;">Kami menerima permintaan reset password untuk akun Anda. Gunakan kode OTP di bawah ini:</p>
                            
                            <div style="background:#f0f9ff;border:2px dashed #0A669D;border-radius:8px;padding:20px;text-align:center;margin:20px 0;">
                                <p style="color:#6b7280;font-size:12px;margin:0 0 8px;text-transform:uppercase;letter-spacing:1px;">Kode OTP</p>
                                <p style="color:#0A669D;font-size:36px;font-weight:bold;letter-spacing:8px;margin:0;font-family:monospace;">{{ $otp }}</p>
                            </div>

                            <p style="color:#6b7280;font-size:13px;margin:0 0 10px;">
                                Kode ini berlaku selama <strong>{{ $expiresInMinutes }} menit</strong>.
                            </p>
                            <p style="color:#6b7280;font-size:13px;margin:0;">
                                Jika Anda tidak merasa meminta reset password, abaikan email ini.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#f9fafb;padding:20px;text-align:center;border-top:1px solid #e5e7eb;">
                            <p style="color:#9ca3af;font-size:11px;margin:0;">© 2026 SIKAP · Pusjar SKMP LAN RI</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>