@extends('layouts.auth')
@section('title', 'Verify Your Email — Madhavi Stores')

@section('form')
<div style="text-align:center;margin-bottom:32px;">
  <div style="width:64px;height:64px;background:#f0ebe3;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;">
    <svg width="28" height="28" fill="none" stroke="#b8986e" stroke-width="1.5" viewBox="0 0 24 24">
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
    <div class="auth-alert success" style="margin-bottom:28px; border: 1px solid #b8986e; background-color: #faf8f5; color: #b8986e; padding: 12px 16px; font-size: 12px; letter-spacing: 0.05em; text-align: center; line-height: 1.5;">
      ✦ <strong>Local Dev Fallback:</strong> Local SMTP SSL is not configured. Your verification code is: <strong style="text-decoration: underline; font-size: 14px; margin-left: 4px;">{{ session('local_otp') }}</strong>
    </div>
  @endif

  @error('otp')
    <div class="auth-alert error" style="margin-bottom:20px;">{{ $message }}</div>
  @enderror

  <div class="form-group">
    <label class="form-label" style="text-align:center;display:block;margin-bottom:16px;">Enter 6-digit code</label>
    <div class="otp-inputs" id="otp-boxes">
      @for($i = 0; $i < 6; $i++)
        <input type="text" inputmode="numeric" maxlength="1" class="otp-digit"
               autocomplete="off" data-index="{{ $i }}">
      @endfor
    </div>
    <input type="hidden" name="otp" id="otp-value">
  </div>

  <button type="submit" class="auth-submit" id="otp-submit" disabled>Verify Email</button>
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
// OTP input auto-advance
var boxes = document.querySelectorAll('.otp-digit');
var hidden = document.getElementById('otp-value');
var submitBtn = document.getElementById('otp-submit');

boxes.forEach(function(box, idx) {
  box.addEventListener('input', function(e) {
    var val = e.target.value.replace(/\D/g,'');
    e.target.value = val;
    if(val && idx < 5) boxes[idx+1].focus();
    updateHidden();
  });
  box.addEventListener('keydown', function(e) {
    if(e.key==='Backspace' && !e.target.value && idx > 0) boxes[idx-1].focus();
  });
  box.addEventListener('paste', function(e) {
    e.preventDefault();
    var data = (e.clipboardData||window.clipboardData).getData('text').replace(/\D/g,'').slice(0,6);
    data.split('').forEach(function(c,i){ if(boxes[i]) boxes[i].value=c; });
    updateHidden();
    if(data.length===6) submitBtn.disabled=false;
  });
});

function updateHidden() {
  var val = Array.from(boxes).map(function(b){ return b.value; }).join('');
  hidden.value = val;
  submitBtn.disabled = val.length < 6;
}

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
