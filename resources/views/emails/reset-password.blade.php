<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password</title>
</head>
<body style="margin:0;padding:0;background:#f4f6f8;font-family:Arial, sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f4f6f8;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 6px 16px rgba(17,24,39,0.08);">
                    <tr>
                        <td style="padding:24px 32px;background:#0f172a;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="left" style="vertical-align:middle;">
                                        @php
                                            $logoPath = public_path('images/Logo.png');
                                            $logoSrc = is_file($logoPath)
                                                ? $message->embed($logoPath)
                                                : 'https://upload.wikimedia.org/wikipedia/commons/9/98/Logo_Kementerian_Ketenagakerjaan_%282016%29.png';
                                        @endphp
                                        <img src="{{ $logoSrc }}" alt="Logo Kemenaker" height="44" style="display:block;" />
                                    </td>
                                    <td align="right" style="color:#e2e8f0;font-size:14px;font-weight:600;">
                                        Balai K3 Surabaya
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <h2 style="margin:0 0 12px 0;font-size:20px;">Reset Password</h2>
                            <p style="margin:0 0 16px 0;line-height:1.6;">
                                Halo{{ isset($user) && $user->name ? ' ' . $user->name : '' }},
                                kami menerima permintaan untuk mengatur ulang kata sandi akun Anda.
                            </p>
                            <p style="margin:0 0 24px 0;line-height:1.6;">
                                Klik tombol di bawah ini untuk membuat kata sandi baru.
                            </p>
                            <p style="margin:0 0 24px 0;">
                                <a href="{{ $url }}" style="display:inline-block;background:#2563eb;color:#ffffff;text-decoration:none;padding:12px 20px;border-radius:8px;font-weight:600;">Reset Password</a>
                            </p>
                            <p style="margin:0 0 12px 0;line-height:1.6;">
                                Jika Anda tidak meminta reset password, abaikan email ini.
                            </p>
                            <p style="margin:0;line-height:1.6;font-size:13px;color:#6b7280;">
                                Link reset password akan kadaluarsa dalam
                                {{ config('auth.passwords.users.expire') ?? 60 }} menit.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 32px;background:#f8fafc;font-size:12px;color:#6b7280;">
                            Jika tombol tidak bekerja, salin dan tempel tautan berikut ke browser Anda:<br>
                            <a href="{{ $url }}" style="color:#2563eb;word-break:break-all;">{{ $url }}</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
