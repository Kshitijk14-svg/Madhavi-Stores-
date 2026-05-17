<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use App\Mail\OtpMail;

class AuthController extends Controller
{
    // ── SHOW LOGIN ──────────────────────────────────────────
    public function showLogin()
    {
        return view('auth.login');
    }

    // ── LOGIN ───────────────────────────────────────────────
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        $credentials = $request->only('email', 'password');
        $remember    = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            if (!$user->email_verified_at) {
                Auth::logout();
                $this->sendOtp($user->email, 'verify');
                session(['otp_email' => $user->email, 'otp_purpose' => 'verify']);
                return redirect()->route('verify.show')
                    ->with('info', 'Please verify your email before logging in. A new code has been sent.');
            }

            $request->session()->regenerate();

            // Admins go to dashboard
            if ($user->is_admin) {
                return redirect('/admin');
            }

            return redirect()->intended(route('account'));
        }

        return back()->withErrors(['email' => 'These credentials do not match our records.'])->withInput();
    }

    // ── SHOW REGISTER ───────────────────────────────────────
    public function showRegister()
    {
        return view('auth.register');
    }

    // ── REGISTER ────────────────────────────────────────────
    public function register(Request $request)
    {
        $request->validate([
            'name'                  => 'required|string|max:100',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Send OTP
        $this->sendOtp($user->email, 'verify');
        session(['otp_email' => $user->email, 'otp_purpose' => 'verify']);

        return redirect()->route('verify.show')
            ->with('success', 'Account created! Please check your email for the verification code.');
    }

    // ── SHOW VERIFY ─────────────────────────────────────────
    public function showVerify()
    {
        if (!session('otp_email')) {
            return redirect()->route('login');
        }
        return view('auth.verify');
    }

    // ── VERIFY OTP ──────────────────────────────────────────
    public function verify(Request $request)
    {
        $request->validate(['otp' => 'required|digits:6']);

        $email = session('otp_email');
        if (!$email) return redirect()->route('login');

        $record = DB::table('otp_codes')
            ->where('email', $email)
            ->where('purpose', 'verify')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$record || $record->code !== $request->otp) {
            return back()->withErrors(['otp' => 'Invalid or expired code. Please try again.']);
        }

        // Mark verified
        User::where('email', $email)->update(['email_verified_at' => now()]);
        DB::table('otp_codes')->where('email', $email)->delete();
        session()->forget(['otp_email', 'otp_purpose']);

        Auth::login(User::where('email', $email)->first());
        return redirect()->route('account')->with('success', 'Email verified! Welcome to Madhavi Stores.');
    }

    // ── RESEND OTP ──────────────────────────────────────────
    public function resendOtp(Request $request)
    {
        $email   = session('otp_email');
        $purpose = session('otp_purpose', 'verify');
        if (!$email) return redirect()->route('login');

        $this->sendOtp($email, $purpose);
        return back()->with('success', 'A new code has been sent to ' . $email);
    }

    // ── SHOW FORGOT ─────────────────────────────────────────
    public function showForgot()
    {
        return view('auth.forgot');
    }

    // ── SEND RESET OTP ──────────────────────────────────────
    public function sendResetOtp(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $this->sendOtp($request->email, 'reset');
        session(['otp_email' => $request->email, 'otp_purpose' => 'reset']);

        return redirect()->route('password.reset')
            ->with('success', 'A 6-digit reset code has been sent to ' . $request->email);
    }

    // ── SHOW RESET ──────────────────────────────────────────
    public function showReset()
    {
        if (!session('otp_email')) return redirect()->route('password.forgot');
        return view('auth.reset');
    }

    // ── RESET PASSWORD ──────────────────────────────────────
    public function resetPassword(Request $request)
    {
        $request->validate([
            'otp'                   => 'required|digits:6',
            'password'              => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        $email = session('otp_email');
        if (!$email) return redirect()->route('password.forgot');

        $record = DB::table('otp_codes')
            ->where('email', $email)
            ->where('purpose', 'reset')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$record || $record->code !== $request->otp) {
            return back()->withErrors(['otp' => 'Invalid or expired code.']);
        }

        User::where('email', $email)->update(['password' => Hash::make($request->password)]);
        DB::table('otp_codes')->where('email', $email)->delete();
        session()->forget(['otp_email', 'otp_purpose']);

        return redirect()->route('login')->with('success', 'Password reset successfully. Please log in.');
    }

    // ── ACCOUNT PAGE ────────────────────────────────────────
    public function account()
    {
        $user = Auth::user();
        
        // Fetch all placed orders with purchased items and products
        $orders = \App\Models\Order::where('user_id', $user->id)
                                   ->with('items.product')
                                   ->latest()
                                   ->get();

        // Fetch customer's wishlist items
        $wishlist = \App\Models\WishlistItem::where('user_id', $user->id)
                                           ->with('product')
                                           ->latest()
                                           ->get();

        // Cart count
        $cartCount = \App\Models\CartItem::where('user_id', $user->id)->sum('quantity');

        return view('pages.account', compact('user', 'orders', 'wishlist', 'cartCount'));
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

        // Delete old codes for this email+purpose
        DB::table('otp_codes')
            ->where('email', $email)
            ->where('purpose', $purpose)
            ->delete();

        // Store new code (expires in 10 minutes)
        DB::table('otp_codes')->insert([
            'email'      => $email,
            'code'       => $otp,
            'purpose'    => $purpose,
            'expires_at' => now()->addMinutes(10),
            'created_at' => now(),
        ]);

        // Send email with local fallback
        try {
            Mail::to($email)->send(new OtpMail($otp, $purpose));
        } catch (\Throwable $e) {
            logger()->error("Failed to send OTP email: " . $e->getMessage());
            // Flash the OTP so local developers can verify their account without db access
            session()->flash('local_otp', $otp);
        }
    }
}
