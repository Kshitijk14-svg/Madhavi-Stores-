@extends('layouts.auth')
@section('title', 'Forgot Password — Madhavi Stores')

@section('form')
<div style="text-align:center;margin-bottom:32px;">
  <div style="width:64px;height:64px;background:#f0ebe3;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;">
    <svg width="28" height="28" fill="none" stroke="#ebb829" stroke-width="1.5" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
    </svg>
  </div>
  <h1 class="auth-title">Forgot password?</h1>
  <p class="auth-subtitle">Enter your registered email and we'll send<br>a 6-digit reset code.</p>
</div>

<form action="{{ route('password.forgot.post') }}" method="POST">
  @csrf

  <div class="form-group">
    <label class="form-label" for="email">Email Address</label>
    <input type="email" id="email" name="email" class="form-input {{ $errors->has('email') ? 'error' : '' }}"
           value="{{ old('email') }}" placeholder="you@example.com" required>
    @error('email')<span class="form-error">{{ $message }}</span>@enderror
  </div>

  <button type="submit" class="auth-submit">Send Reset Code</button>
</form>

<div style="margin-top:24px;text-align:center;">
  <a href="{{ route('login') }}" style="font-size:12px;color:#888;">← Back to login</a>
</div>
@endsection
