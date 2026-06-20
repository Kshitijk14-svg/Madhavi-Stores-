@extends('layouts.auth')
@section('title', 'Sign In — Madhavi Stores')

@section('form')
<h1 class="auth-title">Welcome back</h1>
<p class="auth-subtitle">Sign in to your Madhavi Stores account</p>

<form action="{{ route('login.post') }}" method="POST" novalidate id="login-form">
  @csrf

  {{-- Email --}}
  <div class="form-group">
    <label class="form-label" for="email">Email Address</label>
    <div class="form-input-wrap">
      <span class="input-icon">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
      </span>
      <input type="email" id="email" name="email"
             class="form-input has-icon {{ $errors->has('email') ? 'error' : '' }}"
             value="{{ old('email') }}" placeholder="you@example.com"
             autocomplete="email" required>
    </div>
    @error('email')
      <span class="form-error">
        <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
        {{ $message }}
      </span>
    @enderror
  </div>



  {{-- Remember me --}}
  <div class="form-group check-row" style="margin-bottom:28px;">
    <input type="checkbox" id="remember" name="remember">
    <label for="remember">Keep me logged in for 15 days</label>
  </div>

  {{-- Submit --}}
  <button type="submit" class="auth-submit" id="login-btn">
    <span id="btn-text">Send Login Code</span>
    <span id="btn-loader" style="display:none;align-items:center;gap:8px;">
      <svg class="spin" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
      Sending Code…
    </span>
  </button>
</form>

<div class="auth-divider"><span>or</span></div>

<div class="auth-link-row">
  New to Madhavi Stores? <a href="{{ route('register') }}">Create an account</a>
</div>

{{-- Inline style additions --}}
<style>
.input-icon {
  position:absolute; left:14px; top:50%; transform:translateY(-50%);
  color:#aaa; pointer-events:none; display:flex;
}
.form-input.has-icon { padding-left:42px; }

.auth-inline-link {
  font-size:11px; color:#b8986e; font-weight:600; text-decoration:none;
  border-bottom:1px solid transparent; transition:border-color 0.2s;
}
.auth-inline-link:hover { border-color:#b8986e; }

.form-error {
  display:flex; align-items:center; gap:5px;
  font-size:11px; color:#dc2626; margin-top:6px;
}

@keyframes spin { to { transform: rotate(360deg); } }
.spin { animation: spin 0.8s linear infinite; }

.auth-submit:active { transform:scale(0.98); }
.auth-submit:disabled { opacity:0.6; cursor:not-allowed; }
</style>

<script>

// Loading state on submit
document.getElementById('login-form').addEventListener('submit', function() {
  var btn    = document.getElementById('login-btn');
  var text   = document.getElementById('btn-text');
  var loader = document.getElementById('btn-loader');
  btn.disabled = true;
  text.style.display   = 'none';
  loader.style.display = 'flex';
});
</script>
@endsection
