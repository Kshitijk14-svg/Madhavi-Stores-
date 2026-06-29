<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Mail\OtpMail;

class AuthController extends Controller
{
    // ── SHOW LOGIN ──────────────────────────────────────────
    public function showLogin()
    {
        return view('auth.login');
    }

    // ── LOGIN (password-based) ──────────────────────────────
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Per-account lockout (email+IP) on top of the per-IP route throttle —
        // blocks credential-stuffing against one account regardless of source.
        $this->ensureIsNotRateLimited($request);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password], true)) {
            RateLimiter::clear($this->throttleKey($request));
            $request->session()->regenerate();

            if (! Auth::user()->email_verified_at) {
                Auth::logout();
                return back()
                    ->withErrors(['email' => 'Please verify your email address before signing in.'])
                    ->withInput();
            }

            if (Auth::user()->isAdmin()) {
                return redirect('/admin');
            }
            return redirect()->intended(route('account'))->with('success', 'Welcome back to Madhavi Stores.');
        }

        RateLimiter::hit($this->throttleKey($request));

        return back()->withErrors(['email' => 'These credentials do not match our records.'])->withInput();
    }

    // ── HELPER: LOGIN RATE LIMITING ─────────────────────────
    // Throttle key is scoped to the submitted email + client IP.
    private function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower((string) $request->input('email')) . '|' . $request->ip());
    }

    // Throws a ValidationException with the remaining lockout time once the
    // account hits too many failed attempts (5 per the default decay window).
    private function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => 'Too many login attempts. Please try again in ' . $seconds . ' seconds.',
        ]);
    }

    // ── SHOW REGISTER ───────────────────────────────────────
    public function showRegister()
    {
        return view('auth.register');
    }

    // ── REGISTER (with password + email OTP verification) ───
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        if (User::where('email', $request->email)->exists()) {
            return back()
                ->withErrors(['email' => 'This email address is already taken. Please sign in instead.'])
                ->withInput();
        }

        // Defer creating the account until the OTP is verified. This prevents
        // orphan unverified rows from blocking future sign-ups.
        session([
            'otp_email'            => $request->email,
            'otp_purpose'          => 'register',
            'pending_registration' => [
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ],
        ]);

        if (! $this->sendOtp($request->email, 'register')) {
            session()->forget(['otp_email', 'otp_purpose', 'pending_registration']);
            return back()
                ->withErrors(['email' => 'We could not send the verification email right now. Please try again in a moment.'])
                ->withInput();
        }

        return redirect()->route('verify.show')
            ->with('success', 'Verification code sent! Check your email to finish signing up.');
    }

    // ── SHOW VERIFY ─────────────────────────────────────────
    public function showVerify()
    {
        if (!session('otp_email')) {
            return redirect()->route('login');
        }
        $purpose = session('otp_purpose', 'register');
        return view('auth.verify', compact('purpose'));
    }

    // ── VERIFY OTP ──────────────────────────────────────────
    public function verify(Request $request)
    {
        $request->validate(['otp' => 'required|digits:6']);

        $email   = session('otp_email');
        $purpose = session('otp_purpose', 'register');
        if (!$email) return redirect()->route('login');

        // Verify the OTP under a row lock — prevents TOCTOU races and caps
        // brute-force guesses. Codes are stored hashed, so we fetch the latest
        // row and compare with Hash::check rather than matching in SQL.
        $error = DB::transaction(function () use ($email, $purpose, $request) {
            $row = DB::table('otp_codes')
                ->where('email', $email)
                ->where('purpose', $purpose)
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if (! $row || now()->greaterThanOrEqualTo($row->expires_at)) {
                return 'Invalid or expired code. Please try again.';
            }

            if ($row->attempts >= 5) {
                // Too many wrong guesses — burn the code so it can't be brute-forced.
                DB::table('otp_codes')->where('email', $email)->delete();
                return 'Too many incorrect attempts. Please request a new code.';
            }

            if (! Hash::check($request->otp, $row->code)) {
                DB::table('otp_codes')->where('id', $row->id)->increment('attempts');
                return 'Invalid or expired code. Please try again.';
            }

            // Success — consume every code for this email.
            DB::table('otp_codes')->where('email', $email)->delete();
            return null;
        });

        if ($error !== null) {
            return back()->withErrors(['otp' => $error]);
        }

        if ($purpose === 'reset') {
            // OTP verified for password reset — go to set new password page
            session(['otp_reset_verified' => true]);
            session()->forget('otp_purpose');
            return redirect()->route('password.reset.show');
        }

        // Register verification flow — create the account now that email ownership
        // is proven. Requires a valid pending_registration in session.
        $pending = session('pending_registration');

        if (! $pending || ($pending['email'] ?? null) !== $email) {
            session()->forget(['otp_email', 'otp_purpose', 'pending_registration']);
            return redirect()->route('register')
                ->withErrors(['email' => 'Your sign-up session expired. Please register again.']);
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            $user->update([
                'name'              => $pending['name'],
                'password'          => $pending['password'],
                'email_verified_at' => now(),
            ]);
        } else {
            $user = User::create([
                'name'              => $pending['name'],
                'email'             => $pending['email'],
                'password'          => $pending['password'],
                'email_verified_at' => now(),
            ]);
        }

        session()->forget(['otp_email', 'otp_purpose', 'pending_registration']);

        Auth::login($user, true);
        $request->session()->regenerate();

        if ($user->isAdmin()) {
            return redirect('/admin');
        }
        return redirect()->intended(route('account'))->with('success', 'Welcome to Madhavi Stores.');
    }

    // ── RESEND OTP ──────────────────────────────────────────
    public function resendOtp(Request $request)
    {
        $email   = session('otp_email');
        $purpose = session('otp_purpose', 'register');
        if (!$email) return redirect()->route('login');

        if (! $this->sendOtp($email, $purpose)) {
            return back()->withErrors(['otp' => 'Could not send the code right now. Please try again shortly.']);
        }
        return back()->with('success', 'A new code has been sent to ' . $email);
    }

    // ── SHOW FORGOT PASSWORD ────────────────────────────────
    public function showForgotPassword()
    {
        return view('auth.forgot');
    }

    // ── FORGOT PASSWORD (sends reset OTP) ──────────────────
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();
        if ($user) {
            if (! $this->sendOtp($user->email, 'reset')) {
                return back()
                    ->withErrors(['email' => 'We could not send the reset code right now. Please try again in a moment.'])
                    ->withInput();
            }
            session(['otp_email' => $user->email, 'otp_purpose' => 'reset']);
        }

        // Always show same message to prevent user enumeration
        return redirect()->route('verify.show')
            ->with('info', 'If that email is registered, a reset code has been sent.');
    }

    // ── SHOW RESET PASSWORD FORM ────────────────────────────
    public function showResetPassword()
    {
        if (!session('otp_email') || !session('otp_reset_verified')) {
            return redirect()->route('password.forgot');
        }
        return view('auth.reset');
    }

    // ── RESET PASSWORD ──────────────────────────────────────
    public function resetPassword(Request $request)
    {
        if (!session('otp_email') || !session('otp_reset_verified')) {
            return redirect()->route('password.forgot');
        }

        $request->validate(['password' => 'required|min:8|confirmed']);

        $user = User::where('email', session('otp_email'))->first();
        if (!$user) {
            return redirect()->route('login')->withErrors(['email' => 'Account not found.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        session()->forget(['otp_email', 'otp_reset_verified']);

        return redirect()->route('login')
            ->with('success', 'Password reset successfully. Please sign in with your new password.');
    }

    // ── ACCOUNT PAGE ────────────────────────────────────────
    public function account(\Illuminate\Http\Request $request)
    {
        $user = Auth::user();

        $ordersQuery = \App\Models\Order::where('user_id', $user->id)->with('items.product');

        if ($request->get('order_sort') === 'oldest') {
            $ordersQuery->oldest();
        } else {
            $ordersQuery->latest();
        }

        if ($request->filled('order_month')) {
            $parts = explode('-', $request->order_month);
            if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                $ordersQuery->whereYear('created_at', $parts[0])->whereMonth('created_at', $parts[1]);
            }
        }

        $orders = $ordersQuery->paginate(15)->withQueryString();

        $wishlist = \App\Models\WishlistItem::where('user_id', $user->id)
                                           ->with('product')
                                           ->latest()
                                           ->get();

        $cartCount = \App\Models\CartItem::where('user_id', $user->id)->sum('quantity');

        return view('pages.account', compact('user', 'orders', 'wishlist', 'cartCount'));
    }

    // ── ORDER RECEIPT (customer PDF download/view) ──────────
    public function orderReceipt(\Illuminate\Http\Request $request, $id)
    {
        $order = \App\Models\Order::with(['items.product', 'user'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        try {
            $bytes    = \App\Http\Controllers\AdminController::buildInvoicePdfBytes($order);
            $filename = 'Receipt-' . $order->order_number . '.pdf';
            $disposition = $request->boolean('download') ? 'attachment' : 'inline';
            return response($bytes, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => $disposition . '; filename="' . $filename . '"',
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Customer receipt failed: ' . $e->getMessage(), ['order_id' => $id]);
            return redirect()->route('account')->with('error', 'Could not generate the receipt. Please try again.');
        }
    }

    // ── UPDATE PROFILE ──────────────────────────────────────
    public function updateProfile(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);

        $user = Auth::user();
        $user->name = $request->name;
        $user->save();

        return redirect()->back()->with('success', 'Your profile settings have been updated successfully!');
    }

    // ── LOGOUT ──────────────────────────────────────────────
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }

    // ── HELPER: SEND OTP ────────────────────────────────────
    // Returns true if the code was sent (or surfaced locally), false on failure.
    private function sendOtp(string $email, string $purpose): bool
    {
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('otp_codes')
            ->where('email', $email)
            ->where('purpose', $purpose)
            ->delete();

        DB::table('otp_codes')->insert([
            'email'      => $email,
            // Store a hash, never the raw code — limits damage if the DB is read.
            'code'       => Hash::make($otp),
            'purpose'    => $purpose,
            'attempts'   => 0,
            'expires_at' => now()->addMinutes(10),
            'created_at' => now(),
        ]);

        try {
            // Sent synchronously (sendNow) so it works without a running queue
            // worker — most shared hosting has none, which silently swallowed OTPs.
            Mail::to($email)->sendNow(new OtpMail($otp, $purpose));
            return true;
        } catch (\Throwable $e) {
            logger()->error('Failed to send OTP email: ' . $e->getMessage());
            // In local dev, surface the code so testing isn't blocked by SMTP setup.
            if (app()->isLocal()) {
                session()->flash('local_otp', $otp);
                return true;
            }
            return false;
        }
    }
}
