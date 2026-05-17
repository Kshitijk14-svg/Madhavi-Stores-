<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>@yield('title', 'Madhavi Stores')</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;1,300;1,400&family=Manrope:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  <style>
    .auth-page { min-height:100vh; display:grid; grid-template-columns:1fr; }
    @media(min-width:1024px){ .auth-page { grid-template-columns:1fr 1fr; } }

    .auth-image { display:none; position:relative; overflow:hidden; }
    @media(min-width:1024px){ .auth-image { display:block; } }
    .auth-image img { width:100%; height:100%; object-fit:cover; object-position:center top; }
    .auth-image-overlay { position:absolute; inset:0; background:linear-gradient(to bottom,rgba(24,24,24,0.3),rgba(24,24,24,0.7)); }
    .auth-image-text { position:absolute; bottom:0; inset-x:0; padding:48px; color:#fff; }

    .auth-panel { display:flex; flex-direction:column; min-height:100vh; background:#faf8f5; }
    .auth-panel-inner { flex:1; display:flex; flex-direction:column; justify-content:center; padding:48px 40px; max-width:480px; width:100%; margin:0 auto; }
    @media(min-width:640px){ .auth-panel-inner { padding:64px 48px; } }

    .auth-logo { margin-bottom:48px; }
    .auth-logo a { font-family:'Cormorant Garamond',serif; font-size:1.75rem; font-style:italic; font-weight:300; color:#181818; text-decoration:none; }

    .auth-title { font-family:'Cormorant Garamond',serif; font-size:2.25rem; font-weight:300; line-height:1.1; margin-bottom:8px; color:#181818; }
    .auth-subtitle { font-size:13px; color:#888; font-weight:300; margin-bottom:40px; line-height:1.5; }

    .form-group { margin-bottom:24px; }
    .form-label { display:block; font-size:10px; font-weight:600; letter-spacing:0.3em; text-transform:uppercase; color:#181818; margin-bottom:10px; }
    .form-input {
      width:100%; padding:14px 16px; background:#fff; border:1px solid #e5e5e5;
      font-size:14px; font-family:inherit; color:#181818; outline:none;
      transition:border-color 0.2s; box-sizing:border-box;
    }
    .form-input:focus { border-color:#b8986e; }
    .form-input.error { border-color:#dc2626; }
    .form-error { font-size:11px; color:#dc2626; margin-top:6px; display:block; }

    .form-input-wrap { position:relative; }
    .pw-toggle { position:absolute; right:14px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:#888; padding:4px; }

    .otp-inputs { display:flex; gap:10px; }
    .otp-inputs input {
      flex:1; text-align:center; font-size:1.5rem; font-weight:700;
      padding:16px 4px; background:#fff; border:1px solid #e5e5e5;
      font-family:inherit; color:#181818; outline:none; transition:border-color 0.2s;
    }
    .otp-inputs input:focus { border-color:#b8986e; }

    .auth-submit { width:100%; padding:16px; background:#181818; color:#fff; border:none; cursor:pointer; font-size:11px; font-weight:700; letter-spacing:0.3em; text-transform:uppercase; font-family:inherit; transition:background 0.3s; margin-top:8px; }
    .auth-submit:hover { background:#b8986e; color:#181818; }

    .auth-divider { display:flex; align-items:center; gap:16px; margin:28px 0; }
    .auth-divider span { font-size:11px; color:#ccc; text-transform:uppercase; letter-spacing:0.2em; }
    .auth-divider::before, .auth-divider::after { content:''; flex:1; height:1px; background:#e8e8e8; }

    .auth-link-row { text-align:center; font-size:13px; color:#888; }
    .auth-link-row a { color:#181818; font-weight:600; text-decoration:none; border-bottom:1px solid rgba(24,24,24,0.2); padding-bottom:1px; transition:border-color 0.2s; }
    .auth-link-row a:hover { border-color:#b8986e; color:#b8986e; }

    .auth-alert { padding:14px 16px; margin-bottom:24px; font-size:13px; line-height:1.5; }
    .auth-alert.success { background:#f0fdf4; border-left:3px solid #16a34a; color:#15803d; }
    .auth-alert.error   { background:#fef2f2; border-left:3px solid #dc2626; color:#dc2626; }
    .auth-alert.info    { background:#eff6ff; border-left:3px solid #3b82f6; color:#1d4ed8; }

    .pw-strength { height:3px; margin-top:8px; background:#f0f0f0; transition:all 0.3s; }
    .pw-strength-bar { height:100%; width:0; transition:width 0.4s, background 0.4s; }

    .check-row { display:flex; align-items:center; gap:10px; }
    .check-row input[type=checkbox] { width:16px; height:16px; accent-color:#b8986e; flex-shrink:0; }
    .check-row label { font-size:13px; color:#888; }
    .check-row label a { color:#181818; font-weight:600; }

    .resend-row { text-align:center; font-size:13px; color:#888; margin-top:20px; }
    .resend-row button { background:none; border:none; cursor:pointer; color:#b8986e; font-weight:600; font-family:inherit; font-size:13px; text-decoration:underline; }
    .resend-row button:disabled { color:#ccc; cursor:not-allowed; text-decoration:none; }
  </style>
</head>
<body style="margin:0;background:#faf8f5;">

<div class="auth-page">

  {{-- LEFT — Image panel --}}
  <div class="auth-image">
    <img src="https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=1200&q=85&auto=format&fit=crop&crop=top"
         alt="Madhavi Stores">
    <div class="auth-image-overlay"></div>
    <div class="auth-image-text">
      <p style="font-size:10px;font-weight:700;letter-spacing:0.4em;text-transform:uppercase;color:rgba(184,152,110,0.8);margin-bottom:16px;">Madhavi Stores</p>
      <h2 style="font-family:'Cormorant Garamond',serif;font-size:3rem;font-weight:300;font-style:italic;line-height:0.9;color:#fff;margin-bottom:20px;">Quiet Luxury.<br>Indian Heritage.</h2>
      <p style="font-size:13px;color:rgba(255,255,255,0.5);font-weight:300;line-height:1.7;">Handcrafted textiles that tell the story<br>of a culture woven through time.</p>
    </div>
  </div>

  {{-- RIGHT — Form panel --}}
  <div class="auth-panel">
    <div class="auth-panel-inner">

      <div class="auth-logo">
        <a href="{{ route('home') }}">← Madhavi</a>
      </div>

      {{-- Flash messages --}}
      @if(session('success'))
        <div class="auth-alert success">{{ session('success') }}</div>
      @endif
      @if(session('error'))
        <div class="auth-alert error">{{ session('error') }}</div>
      @endif
      @if(session('info'))
        <div class="auth-alert info">{{ session('info') }}</div>
      @endif

      @yield('form')

    </div>
  </div>
</div>

</body>
</html>
