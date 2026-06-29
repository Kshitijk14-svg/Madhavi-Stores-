<?php

namespace Tests\Feature;

use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OtpFlowTest extends TestCase
{
    use RefreshDatabase;

    /** Capture the raw OTP that was emailed (codes are stored hashed in the DB). */
    private function lastSentOtp(): string
    {
        $captured = null;
        Mail::assertSent(OtpMail::class, function (OtpMail $mail) use (&$captured) {
            $captured = $mail->otp;
            return true;
        });
        $this->assertNotNull($captured, 'No OTP email was sent.');
        return $captured;
    }

    public function test_registration_defers_account_creation_and_stores_hashed_code(): void
    {
        Mail::fake();

        $this->post('/register', [
            'name'                  => 'Alice',
            'email'                 => 'alice@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('verify.show'));

        // Account is NOT created until the OTP is verified.
        $this->assertNull(User::where('email', 'alice@example.com')->first());

        // Exactly one code row, and it is hashed (never the raw 6 digits).
        $row = DB::table('otp_codes')->where('email', 'alice@example.com')->first();
        $this->assertNotNull($row);
        $this->assertEquals('register', $row->purpose);
        $this->assertNotEquals($this->lastSentOtp(), $row->code);
        $this->assertTrue(Hash::check($this->lastSentOtp(), $row->code));
    }

    public function test_correct_otp_creates_and_logs_in_user(): void
    {
        Mail::fake();

        $this->post('/register', [
            'name'                  => 'Alice',
            'email'                 => 'alice@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->post('/verify-email', ['otp' => $this->lastSentOtp()])
            ->assertRedirect();

        $user = User::where('email', 'alice@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->email_verified_at);
        $this->assertAuthenticatedAs($user);

        // Codes are consumed on success.
        $this->assertEquals(0, DB::table('otp_codes')->where('email', 'alice@example.com')->count());
    }

    public function test_wrong_otp_is_rejected_and_account_not_created(): void
    {
        Mail::fake();

        $this->post('/register', [
            'name'                  => 'Alice',
            'email'                 => 'alice@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->post('/verify-email', ['otp' => '000000'])
            ->assertSessionHasErrors('otp');

        $this->assertNull(User::where('email', 'alice@example.com')->first());
        $this->assertGuest();
    }

    public function test_expired_otp_is_rejected(): void
    {
        Mail::fake();

        $this->post('/register', [
            'name'                  => 'Alice',
            'email'                 => 'alice@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $otp = $this->lastSentOtp();

        DB::table('otp_codes')->where('email', 'alice@example.com')
            ->update(['expires_at' => now()->subMinute()]);

        $this->post('/verify-email', ['otp' => $otp])
            ->assertSessionHasErrors('otp');

        $this->assertNull(User::where('email', 'alice@example.com')->first());
    }

    public function test_too_many_wrong_attempts_burns_the_code(): void
    {
        Mail::fake();
        // Bypass the per-IP route throttle so we can exercise the controller-level cap.
        $this->withoutMiddleware(ThrottleRequests::class);

        $this->post('/register', [
            'name'                  => 'Alice',
            'email'                 => 'alice@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $otp = $this->lastSentOtp();

        // 6 wrong guesses: attempts reaches the cap and the code is destroyed.
        for ($i = 0; $i < 6; $i++) {
            $this->post('/verify-email', ['otp' => '000000'])->assertSessionHasErrors('otp');
        }

        $this->assertEquals(0, DB::table('otp_codes')->where('email', 'alice@example.com')->count());

        // Even the correct code no longer works — and no account was created.
        $this->post('/verify-email', ['otp' => $otp])->assertSessionHasErrors('otp');
        $this->assertNull(User::where('email', 'alice@example.com')->first());
    }

    public function test_password_reset_via_otp(): void
    {
        Mail::fake();
        $user = User::factory()->create(['email' => 'bob@example.com']);

        $this->post('/forgot-password', ['email' => 'bob@example.com'])
            ->assertRedirect(route('verify.show'));

        $this->post('/verify-email', ['otp' => $this->lastSentOtp()])
            ->assertRedirect(route('password.reset.show'));

        $this->post('/reset-password', [
            'password'              => 'newpassword456',
            'password_confirmation' => 'newpassword456',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('newpassword456', $user->fresh()->password));
    }

    public function test_forgot_password_is_user_enumeration_safe(): void
    {
        Mail::fake();

        // Unknown email: same destination, but no code is ever sent.
        $this->post('/forgot-password', ['email' => 'nobody@example.com'])
            ->assertRedirect(route('verify.show'));

        Mail::assertNotSent(OtpMail::class);
    }
}
