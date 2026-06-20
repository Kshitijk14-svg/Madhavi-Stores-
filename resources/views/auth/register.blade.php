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



  <div class="form-group check-row">
    <input type="checkbox" id="terms" required>
    <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
  </div>

  <button type="submit" class="auth-submit">Create Account &amp; Send Code</button>
</form>

<div class="auth-divider"><span>or</span></div>
<div class="auth-link-row">
  Already have an account? <a href="{{ route('login') }}">Sign in</a>
</div>


@endsection
