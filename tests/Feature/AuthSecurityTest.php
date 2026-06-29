<?php

namespace Tests\Feature;

use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_cannot_self_assign_admin(): void
    {
        Mail::fake();

        $this->post('/register', [
            'name'                  => 'Mallory',
            'email'                 => 'mallory@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            // Attempted privilege escalation via mass assignment:
            'role'                  => 'superadmin',
            'is_admin'              => 1,
        ]);

        // Account creation is deferred until the emailed OTP is verified.
        $otp = null;
        Mail::assertSent(OtpMail::class, function (OtpMail $mail) use (&$otp) {
            $otp = $mail->otp;
            return true;
        });
        $this->post('/verify-email', ['otp' => $otp]);

        $user = User::where('email', 'mallory@example.com')->first();
        $this->assertNotNull($user);
        $this->assertFalse($user->isAdmin(), 'A registering user must never become an admin.');
        $this->assertEquals('customer', $user->role);
    }

    public function test_user_model_does_not_mass_assign_role(): void
    {
        $user = new User();
        $user->fill(['name' => 'X', 'email' => 'x@example.com', 'role' => 'admin', 'is_admin' => true]);

        $this->assertNull($user->role);
        $this->assertNotTrue($user->is_admin);
    }

    public function test_is_admin_syncs_from_role_on_save(): void
    {
        $user = User::factory()->create();
        $this->assertFalse((bool) $user->is_admin);

        $user->forceFill(['role' => 'admin'])->save();
        $this->assertTrue((bool) $user->fresh()->is_admin, 'is_admin must mirror role.');

        $user->forceFill(['role' => 'customer'])->save();
        $this->assertFalse((bool) $user->fresh()->is_admin);
    }
}
