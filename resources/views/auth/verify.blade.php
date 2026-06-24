@extends('layouts.auth')
@section('title', 'Verify Your Email — Madhavi Stores')

@section('form')
<div style="text-align:center;margin-bottom:32px;">
  <div style="width:64px;height:64px;background:#f0ebe3;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;">
    <svg width="28" height="28" fill="none" stroke="#ebb829" stroke-width="1.5" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
    </svg>
  </div>
  <h1 class="auth-title">Check your inbox</h1>
  <p class="auth-subtitle">
    We sent a 6-digit code to<br>
    <strong style="color:#181818;">{{ session('otp_email', 'your email') }}</strong>
  </p>
</div>

<form action="{{ route('verify.post') }}" method="POST" id="otp-form">
  @csrf

  @if(session('local_otp'))
    <div class="auth-alert success" style="margin-bottom:28px; border: 1px solid #ebb829; background-color: #faf8f5; color: #ebb829; padding: 12px 16px; font-size: 12px; letter-spacing: 0.05em; text-align: center; line-height: 1.5;">
      ✦ <strong>Local Dev Fallback:</strong> Local SMTP SSL is not configured. Your verification code is: <strong style="text-decoration: underline; font-size: 14px; margin-left: 4px;">{{ session('local_otp') }}</strong>
    </div>
  @endif

  @error('otp')
    <div class="auth-alert error" style="margin-bottom:20px;">{{ $message }}</div>
  @enderror

  <div class="form-group" style="margin-bottom:32px;">
    <label class="form-label" style="text-align:center;display:block;margin-bottom:12px;">Enter 6-digit code</label>
    <div style="position:relative; width: 100%; max-width: 280px; margin: 0 auto;">
        <input type="text" inputmode="numeric" name="otp" id="otp-value"
               autocomplete="one-time-code" maxlength="6"
               style="width: 100%; background: transparent; border: none; border-bottom: 2px solid #e5e5e5; font-size: 2.5rem; font-weight: 300; letter-spacing: 0.5em; text-align: center; color: #181818; padding: 12px 0; outline: none; transition: border-color 0.3s; padding-left: 0.5em;"
               onfocus="this.style.borderColor='#ebb829'"
               onblur="this.style.borderColor=this.value.length === 6 ? '#ebb829' : '#e5e5e5'"
               placeholder="------">
    </div>
  </div>

  <button type="submit" class="auth-submit" id="otp-submit" disabled>
    {{ ($purpose ?? 'register') === 'reset' ? 'Verify & Continue' : 'Verify Email' }}
  </button>
</form>

<div class="resend-row">
  Didn't receive the code?
  <form action="{{ route('verify.resend') }}" method="POST" style="display:inline;">
    @csrf
    <button type="submit" id="resend-btn">Resend Code</button>
  </form>
  <span id="resend-timer" style="display:none;color:#ccc;font-size:12px;">Resend in <span id="countdown">60</span>s</span>
</div>

<div style="margin-top:32px;text-align:center;">
  <a href="{{ route('login') }}" style="font-size:12px;color:#888;">← Back to login</a>
</div>

<script>
// OTP input logic
var input = document.getElementById('otp-value');
var submitBtn = document.getElementById('otp-submit');

input.addEventListener('input', function(e) {
  var val = e.target.value.replace(/\D/g, '').substring(0, 6);
  e.target.value = val;
  submitBtn.disabled = val.length < 6;
  
  // Auto submit when 6 digits are entered
  if(val.length === 6) {
    document.getElementById('otp-form').submit();
  }
});

// Resend cooldown
(function(){
  var btn = document.getElementById('resend-btn');
  var timer = document.getElementById('resend-timer');
  var cd = document.getElementById('countdown');
  var secs = 60;
  btn.disabled = true; btn.style.display='none'; timer.style.display='inline';
  var iv = setInterval(function(){
    secs--;
    cd.textContent = secs;
    if(secs <= 0){
      clearInterval(iv);
      timer.style.display='none';
      btn.style.display='inline';
      btn.disabled = false;
    }
  }, 1000);
})();
</script>
@endsection
