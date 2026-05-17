@extends('layouts.auth')
@section('title', 'Create Account — Madhavi Stores')

@section('form')
<h1 class="auth-title">Create an account</h1>
<p class="auth-subtitle">Join Madhavi Stores — discover quiet luxury, Indian heritage.</p>

<form action="{{ route('register.post') }}" method="POST" novalidate id="reg-form">
  @csrf

  <div class="form-group">
    <label class="form-label" for="name">Full Name</label>
    <input type="text" id="name" name="name" class="form-input {{ $errors->has('name') ? 'error' : '' }}"
           value="{{ old('name') }}" placeholder="Priya Sharma" autocomplete="name" required>
    @error('name')<span class="form-error">{{ $message }}</span>@enderror
  </div>

  <div class="form-group">
    <label class="form-label" for="email">Email Address</label>
    <input type="email" id="email" name="email" class="form-input {{ $errors->has('email') ? 'error' : '' }}"
           value="{{ old('email') }}" placeholder="you@example.com" autocomplete="email" required>
    @error('email')<span class="form-error">{{ $message }}</span>@enderror
  </div>

  <div class="form-group">
    <label class="form-label" for="password">Password <span style="font-weight:300;letter-spacing:0;text-transform:none;color:#aaa;">(min. 8 characters)</span></label>
    <div class="form-input-wrap">
      <input type="password" id="password" name="password" class="form-input {{ $errors->has('password') ? 'error' : '' }}"
             placeholder="••••••••" autocomplete="new-password" required oninput="checkStrength(this.value)">
      <button type="button" class="pw-toggle" onclick="togglePw('password',this)">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
      </button>
    </div>
    <div class="pw-strength"><div class="pw-strength-bar" id="pw-bar"></div></div>
    <span id="pw-label" style="font-size:10px;color:#aaa;margin-top:4px;display:block;"></span>
    @error('password')<span class="form-error">{{ $message }}</span>@enderror
  </div>

  <div class="form-group">
    <label class="form-label" for="password_confirmation">Confirm Password</label>
    <div class="form-input-wrap">
      <input type="password" id="password_confirmation" name="password_confirmation"
             class="form-input {{ $errors->has('password_confirmation') ? 'error' : '' }}"
             placeholder="••••••••" autocomplete="new-password" required>
      <button type="button" class="pw-toggle" onclick="togglePw('password_confirmation',this)">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
      </button>
    </div>
    @error('password_confirmation')<span class="form-error">{{ $message }}</span>@enderror
  </div>

  <div class="form-group check-row">
    <input type="checkbox" id="terms" required>
    <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
  </div>

  <button type="submit" class="auth-submit">Create Account &amp; Send Verification</button>
</form>

<div class="auth-divider"><span>or</span></div>
<div class="auth-link-row">
  Already have an account? <a href="{{ route('login') }}">Sign in</a>
</div>

<script>
function togglePw(id) {
  var i = document.getElementById(id);
  i.type = i.type === 'password' ? 'text' : 'password';
}
function checkStrength(val) {
  var bar = document.getElementById('pw-bar'), lbl = document.getElementById('pw-label');
  var score = 0;
  if(val.length >= 8) score++;
  if(/[A-Z]/.test(val)) score++;
  if(/[0-9]/.test(val)) score++;
  if(/[^A-Za-z0-9]/.test(val)) score++;
  var colors = ['#dc2626','#f97316','#eab308','#16a34a'];
  var labels = ['Weak','Fair','Good','Strong'];
  var w = [25,50,75,100];
  if(val.length === 0){ bar.style.width='0'; lbl.textContent=''; return; }
  var idx = Math.max(0, score-1);
  bar.style.width = w[idx]+'%';
  bar.style.background = colors[idx];
  lbl.textContent = labels[idx];
  lbl.style.color = colors[idx];
}
</script>
@endsection
