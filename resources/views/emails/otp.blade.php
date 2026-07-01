<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<title>{{ $purpose === 'reset' ? 'Password Reset' : 'Email Verification' }} — Madhavi Stores</title>
</head>
<body style="margin:0;padding:0;background-color:#f0ebe3;">
<!-- Preheader (hidden preview text shown in the inbox list) -->
<div style="display:none;max-height:0;overflow:hidden;mso-hide:all;font-size:1px;line-height:1px;color:#f0ebe3;">
  Your {{ $purpose === 'reset' ? 'password reset' : 'verification' }} code is {{ $otp }} — valid for 10 minutes.
</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f0ebe3;">
  <tr>
    <td align="center" style="padding:40px 16px;">
      <table role="presentation" width="560" cellpadding="0" cellspacing="0" border="0" style="width:560px;max-width:100%;background-color:#ffffff;">

        <!-- Header -->
        <tr>
          <td align="center" style="background-color:#181818;padding:36px 40px;">
            <div style="font-family:Georgia,'Times New Roman',serif;font-style:italic;font-weight:300;font-size:28px;line-height:1.3;color:#ffffff;">Madhavi Stores</div>
            <div style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:10px;letter-spacing:4px;text-transform:uppercase;color:rgba(255,255,255,0.45);margin-top:8px;">Quiet Luxury &middot; Indian Heritage</div>
          </td>
        </tr>

        <!-- Body -->
        <tr>
          <td style="padding:48px 40px;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">
            <p style="margin:0 0 16px;font-size:16px;line-height:1.5;color:#181818;font-weight:700;">
              {{ $purpose === 'reset' ? 'Password Reset Request' : 'Verify Your Email Address' }}
            </p>
            <p style="margin:0 0 36px;font-size:14px;line-height:1.7;color:#888888;">
              @if($purpose === 'reset')
                We received a request to reset your password. Use the code below to proceed.
                If you didn't request this, you can safely ignore this email.
              @else
                Thank you for joining Madhavi Stores. Use the code below to verify your email
                and complete your registration.
              @endif
            </p>

            <!-- OTP box -->
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:36px;">
              <tr>
                <td align="center" style="background-color:#f0ebe3;border:1px solid rgba(235,184,41,0.3);padding:32px;">
                  <div style="font-family:'Courier New',Courier,monospace;font-size:42px;font-weight:700;letter-spacing:10px;color:#181818;">{{ $otp }}</div>
                  <div style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:11px;letter-spacing:2px;text-transform:uppercase;color:#ebb829;margin-top:14px;">Valid for 10 minutes</div>
                </td>
              </tr>
            </table>

            <!-- Security note -->
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td style="background-color:#fafafa;border-left:3px solid #ebb829;padding:20px;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:12px;line-height:1.6;color:#999999;">
                  <strong style="color:#666666;">Security note:</strong> Never share this code with anyone.
                  Madhavi Stores will never ask for your OTP via phone or chat.
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td align="center" style="background-color:#181818;padding:24px 40px;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">
            <p style="margin:0;font-size:11px;color:rgba(255,255,255,0.35);">&copy; {{ date('Y') }} Madhavi Stores</p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>
</body>
</html>
