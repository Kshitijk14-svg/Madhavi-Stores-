<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
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

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password], true)) {
            $request->session()->regenerate();
            if (Auth::user()->is_admin) {
                return redirect('/admin');
            }
            return redirect()->intended(route('account'))->with('success', 'Welcome back to Madhavi Stores.');
        }

        return back()->withErrors(['email' => 'These credentials do not match our records.'])->withInput();
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
            'name'                  => 'required|string|max:100',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|min:8|confirmed',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $this->sendOtp($user->email, 'register');
        session(['otp_email' => $user->email, 'otp_purpose' => 'register']);

        return redirect()->route('verify.show')
            ->with('success', 'Account created! Please check your email for the verification code.');
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

        // Atomically consume the OTP — prevents TOCTOU race condition
        $deleted = DB::table('otp_codes')
            ->where('email', $email)
            ->where('purpose', $purpose)
            ->where('code', $request->otp)
            ->where('expires_at', '>', now())
            ->delete();

        if (!$deleted) {
            return back()->withErrors(['otp' => 'Invalid or expired code. Please try again.']);
        }

        DB::table('otp_codes')->where('email', $email)->delete();

        if ($purpose === 'reset') {
            // OTP verified for password reset — go to set new password page
            session(['otp_reset_verified' => true]);
            session()->forget('otp_purpose');
            return redirect()->route('password.reset.show');
        }

        // Register verification flow — log the user in
        $user = User::where('email', $email)->first();
        if ($user && !$user->email_verified_at) {
            $user->update(['email_verified_at' => now()]);
        }
        session()->forget(['otp_email', 'otp_purpose']);

        Auth::login($user, true);
        $request->session()->regenerate();

        if ($user->is_admin) {
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

        $this->sendOtp($email, $purpose);
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
            $this->sendOtp($user->email, 'reset');
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
    public function account()
    {
        $user = Auth::user();

        $orders = \App\Models\Order::where('user_id', $user->id)
                                   ->with('items.product')
                                   ->latest()
                                   ->paginate(10)->withQueryString();

        $wishlist = \App\Models\WishlistItem::where('user_id', $user->id)
                                           ->with('product')
                                           ->latest()
                                           ->get();

        $cartCount = \App\Models\CartItem::where('user_id', $user->id)->sum('quantity');

        return view('pages.account', compact('user', 'orders', 'wishlist', 'cartCount'));
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
    private function sendOtp(string $email, string $purpose): void
    {
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('otp_codes')
            ->where('email', $email)
            ->where('purpose', $purpose)
            ->delete();

        DB::table('otp_codes')->insert([
            'email'      => $email,
            'code'       => $otp,
            'purpose'    => $purpose,
            'expires_at' => now()->addMinutes(10),
            'created_at' => now(),
        ]);

        try {
            Mail::to($email)->send(new OtpMail($otp, $purpose));
        } catch (\Throwable $e) {
            logger()->error("Failed to send OTP email: " . $e->getMessage());
            if (app()->isLocal()) {
                session()->flash('local_otp', $otp);
            }
        }
    }
}
