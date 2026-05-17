@extends('layouts.auth')
@section('title', 'Reset Password — Madhavi Stores')

@section('form')
<h1 class="auth-title">Set new password</h1>
<p class="auth-subtitle">
  Enter the 6-digit code sent to<br>
  <strong style="color:#181818;">{{ session('otp_email') }}</strong>
</p>

<form action="{{ route('password.reset.post') }}" method="POST">
  @csrf

  @if(session('local_otp'))
    <div class="auth-alert success" style="margin-bottom:28px; border: 1px solid #b8986e; background-color: #faf8f5; color: #b8986e; padding: 12px 16px; font-size: 12px; letter-spacing: 0.05em; text-align: center; line-height: 1.5; border-radius: 4px;">
      ✦ <strong>Local Dev Fallback:</strong> Local SMTP SSL is not configured. Your reset code is: <strong style="text-decoration: underline; font-size: 14px; margin-left: 4px;">{{ session('local_otp') }}</strong>
    </div>
  @endif

  <div class="form-group">
    <label class="form-label" style="text-align:center;display:block;margin-bottom:16px;">Verification Code</label>
    <div class="otp-inputs" id="otp-boxes">
      @for($i = 0; $i < 6; $i++)
        <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" data-index="{{ $i }}">
      @endfor
    </div>
    <input type="hidden" name="otp" id="otp-value">
    @error('otp')<span class="form-error" style="text-align:center;display:block;margin-top:8px;">{{ $message }}</span>@enderror
  </div>

  <div class="form-group" style="margin-top:32px;">
    <label class="form-label" for="password">New Password</label>
    <div class="form-input-wrap">
      <input type="password" id="password" name="password" class="form-input {{ $errors->has('password') ? 'error' : '' }}"
             placeholder="Min. 8 characters" required oninput="checkStrength(this.value)">
      <button type="button" class="pw-toggle" onclick="togglePw('password')">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
      </button>
    </div>
    <div class="pw-strength"><div class="pw-strength-bar" id="pw-bar"></div></div>
    @error('password')<span class="form-error">{{ $message }}</span>@enderror
  </div>

  <div class="form-group">
    <label class="form-label" for="password_confirmation">Confirm New Password</label>
    <div class="form-input-wrap">
      <input type="password" id="password_confirmation" name="password_confirmation"
             class="form-input" placeholder="••••••••" required>
      <button type="button" class="pw-toggle" onclick="togglePw('password_confirmation')">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
      </button>
    </div>
    @error('password_confirmation')<span class="form-error">{{ $message }}</span>@enderror
  </div>

  <button type="submit" class="auth-submit">Reset Password</button>
</form>

<script>
var boxes = document.querySelectorAll('.otp-digit');
var hidden = document.getElementById('otp-value');
boxes.forEach(function(box,idx){
  box.addEventListener('input', function(e){
    var val = e.target.value.replace(/\D/g,''); e.target.value=val;
    if(val && idx<5) boxes[idx+1].focus();
    hidden.value = Array.from(boxes).map(function(b){return b.value;}).join('');
  });
  box.addEventListener('keydown', function(e){ if(e.key==='Backspace'&&!e.target.value&&idx>0) boxes[idx-1].focus(); });
});
function togglePw(id){ var i=document.getElementById(id); i.type=i.type==='password'?'text':'password'; }
function checkStrength(val){
  var bar=document.getElementById('pw-bar');
  var score=0;
  if(val.length>=8)score++;if(/[A-Z]/.test(val))score++;if(/[0-9]/.test(val))score++;if(/[^A-Za-z0-9]/.test(val))score++;
  var c=['#dc2626','#f97316','#eab308','#16a34a'],w=[25,50,75,100];
  bar.style.width=(val?w[Math.max(0,score-1)]:0)+'%'; bar.style.background=c[Math.max(0,score-1)];
}
</script>
@endsection
