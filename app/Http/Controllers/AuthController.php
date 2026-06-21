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
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $this->sendOtp($user->email, 'login');
            session(['otp_email' => $user->email, 'otp_purpose' => 'login']);
            return redirect()->route('verify.show')
                ->with('info', 'A login code has been sent to your email.');
        }

        return back()->withErrors(['email' => 'We could not find an account with that email address.'])->withInput();
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
            'name'  => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make(Str::random(32)),
        ]);

        // Send OTP
        $this->sendOtp($user->email, 'login');
        session(['otp_email' => $user->email, 'otp_purpose' => 'login']);

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

        // Atomically consume the OTP — prevents TOCTOU race condition
        $deleted = DB::table('otp_codes')
            ->where('email', $email)
            ->where('purpose', 'login')
            ->where('code', $request->otp)
            ->where('expires_at', '>', now())
            ->delete();

        if (!$deleted) {
            return back()->withErrors(['otp' => 'Invalid or expired code. Please try again.']);
        }

        $user = User::where('email', $email)->first();
        if (!$user->email_verified_at) {
            $user->update(['email_verified_at' => now()]);
        }

        DB::table('otp_codes')->where('email', $email)->delete();
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
        $purpose = session('otp_purpose', 'login');
        if (!$email) return redirect()->route('login');

        $this->sendOtp($email, $purpose);
        return back()->with('success', 'A new code has been sent to ' . $email);
    }



    // ── ACCOUNT PAGE ────────────────────────────────────────
    public function account()
    {
        $user = Auth::user();
        
        // Fetch placed orders with purchased items and products
        $orders = \App\Models\Order::where('user_id', $user->id)
                                   ->with('items.product')
                                   ->latest()
                                   ->paginate(10);

        // Fetch customer's wishlist items
        $wishlist = \App\Models\WishlistItem::where('user_id', $user->id)
                                           ->with('product')
                                           ->latest()
                                           ->get();

        // Cart count
        $cartCount = \App\Models\CartItem::where('user_id', $user->id)->sum('quantity');

        return view('pages.account', compact('user', 'orders', 'wishlist', 'cartCount'));
    }

    // ── UPDATE PROFILE ──────────────────────────────────────
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
        ]);

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
