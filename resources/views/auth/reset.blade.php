@extends('layouts.auth')
@section('title', 'Reset Password — Madhavi Stores')

@section('form')
<h1 class="auth-title">Set new password</h1>
<p class="auth-subtitle">Choose a strong password for your account.</p>

<form action="{{ route('password.reset.post') }}" method="POST">
  @csrf

  <div class="form-group">
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
