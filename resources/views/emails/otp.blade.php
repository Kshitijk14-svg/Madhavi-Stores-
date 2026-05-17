<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{{ $purpose === 'reset' ? 'Password Reset' : 'Email Verification' }} — Madhavi Stores</title>
<style>
  body { margin:0; padding:0; background:#f0ebe3; font-family:'Helvetica Neue',Arial,sans-serif; }
  .wrapper { max-width:560px; margin:40px auto; background:#fff; }
  .header { background:#181818; padding:36px 40px; text-align:center; }
  .header h1 { font-family:Georgia,serif; font-style:italic; font-weight:300; font-size:28px; color:#fff; margin:0; }
  .header p { font-size:10px; letter-spacing:0.4em; text-transform:uppercase; color:rgba(255,255,255,0.4); margin:8px 0 0; }
  .body { padding:48px 40px; }
  .greeting { font-size:16px; color:#181818; margin-bottom:16px; }
  .desc { font-size:14px; color:#888; line-height:1.7; margin-bottom:36px; }
  .otp-box { background:#f0ebe3; border:1px solid rgba(184,152,110,0.25); text-align:center; padding:32px; margin-bottom:36px; }
  .otp-code { font-size:42px; font-weight:700; letter-spacing:0.25em; color:#181818; font-family:'Courier New',monospace; }
  .otp-expires { font-size:11px; letter-spacing:0.2em; text-transform:uppercase; color:#b8986e; margin-top:12px; }
  .warning { font-size:12px; color:#aaa; line-height:1.6; padding:20px; background:#fafafa; border-left:2px solid #b8986e; }
  .footer { background:#181818; padding:24px 40px; text-align:center; }
  .footer p { font-size:11px; color:rgba(255,255,255,0.3); margin:0; }
  .footer a { color:rgba(184,152,110,0.7); text-decoration:none; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>Madhavi Stores</h1>
    <p>Quiet Luxury · Indian Heritage</p>
  </div>
  <div class="body">
    <p class="greeting">
      {{ $purpose === 'reset' ? 'Password Reset Request' : 'Verify Your Email Address' }}
    </p>
    <p class="desc">
      @if($purpose === 'reset')
        We received a request to reset your password. Use the code below to proceed.
        If you didn't request this, you can safely ignore this email.
      @else
        Thank you for joining Madhavi Stores. Use the code below to verify your email
        and complete your registration.
      @endif
    </p>
    <div class="otp-box">
      <div class="otp-code">{{ $otp }}</div>
      <div class="otp-expires">Valid for 10 minutes</div>
    </div>
    <div class="warning">
      <strong>Security note:</strong> Never share this code with anyone.
      Madhavi Stores will never ask for your OTP via phone or chat.
    </div>
  </div>
  <div class="footer">
    <p>&copy; {{ date('Y') }} Madhavi Stores &nbsp;·&nbsp; <a href="#">Unsubscribe</a></p>
  </div>
</div>
</body>
</html>
