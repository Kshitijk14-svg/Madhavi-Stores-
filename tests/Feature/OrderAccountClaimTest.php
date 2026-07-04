<?php

namespace Tests\Feature;

use App\Mail\OtpMail;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderAccountClaimTest extends TestCase
{
    use RefreshDatabase;

    private function makeGuestOrder(array $overrides = []): Order
    {
        return Order::create(array_merge([
            'user_id' => null,
            'order_number' => 'MS-CLAIM01',
            'email' => 'claimant@example.com',
            'first_name' => 'Claire',
            'last_name' => 'Ant',
            'address' => '1 Test St',
            'city' => 'Mumbai',
            'postal_code' => '400001',
            'payment_method' => 'COD',
            'payment_status' => 'Paid',
            'order_status' => 'Pending',
            'subtotal' => 1000,
            'discount' => 0,
            'tax' => 0,
            'total' => 1000,
        ], $overrides));
    }

    /** Capture the raw OTP that was emailed (codes are stored hashed... see OtpFlowTest). */
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

    private function claim(array $overrides = []): \Illuminate\Testing\TestResponse
    {
        return $this->post('/track-order/claim', array_merge([
            'order_number'          => 'MS-CLAIM01',
            'email'                 => 'claimant@example.com',
            'name'                  => 'Claire Ant',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ], $overrides));
    }

    public function test_claim_sends_an_otp_and_does_not_create_the_account_yet(): void
    {
        Mail::fake();
        $this->makeGuestOrder();

        $this->claim()->assertRedirect(route('verify.show'));

        $this->assertNull(User::where('email', 'claimant@example.com')->first());
        $this->assertGuest();
    }

    public function test_guest_can_create_account_from_a_found_order_after_verifying_otp(): void
    {
        Mail::fake();
        $order = $this->makeGuestOrder();

        $this->claim();
        $this->post('/verify-email', ['otp' => $this->lastSentOtp()])->assertRedirect(route('account'));

        $user = User::where('email', 'claimant@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->email_verified_at);
        $this->assertAuthenticatedAs($user);
        $this->assertEquals($user->id, $order->fresh()->user_id);
    }

    public function test_claim_fails_with_wrong_order_number(): void
    {
        Mail::fake();
        $this->makeGuestOrder();

        $response = $this->claim(['order_number' => 'MS-WRONGNUM']);

        $response->assertSessionHasErrors('email');
        $this->assertNull(User::where('email', 'claimant@example.com')->first());
        $this->assertGuest();
        Mail::assertNotSent(OtpMail::class);
    }

    public function test_claim_fails_if_order_already_belongs_to_a_user(): void
    {
        Mail::fake();
        $owner = User::factory()->create();
        $this->makeGuestOrder(['user_id' => $owner->id]);

        $response = $this->claim();

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        Mail::assertNotSent(OtpMail::class);
    }

    public function test_claim_fails_if_an_account_with_that_email_already_exists(): void
    {
        Mail::fake();
        User::factory()->create(['email' => 'claimant@example.com']);
        $this->makeGuestOrder();

        $response = $this->claim();

        $response->assertSessionHasErrors('email');
        $this->assertEquals(1, User::where('email', 'claimant@example.com')->count());
        $this->assertGuest();
        Mail::assertNotSent(OtpMail::class);
    }

    public function test_claiming_links_only_the_proven_order_not_other_guest_orders_under_that_email(): void
    {
        Mail::fake();
        $this->makeGuestOrder(['order_number' => 'MS-CLAIM01']);
        $this->makeGuestOrder(['order_number' => 'MS-CLAIM02']);

        $this->claim();
        $this->post('/verify-email', ['otp' => $this->lastSentOtp()]);

        $user = User::where('email', 'claimant@example.com')->first();
        $this->assertEquals($user->id, Order::where('order_number', 'MS-CLAIM01')->value('user_id'));
        $this->assertNull(Order::where('order_number', 'MS-CLAIM02')->value('user_id'));
    }

    public function test_claim_matches_case_insensitively(): void
    {
        Mail::fake();
        $this->makeGuestOrder(['email' => 'claimant@example.com']);

        $this->claim(['email' => 'Claimant@Example.com'])->assertRedirect(route('verify.show'));
        $response = $this->post('/verify-email', ['otp' => $this->lastSentOtp()]);

        $response->assertRedirect(route('account'));
        $user = User::where('email', 'claimant@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals($user->id, Order::where('order_number', 'MS-CLAIM01')->value('user_id'));
    }

    public function test_wrong_otp_does_not_create_the_account(): void
    {
        Mail::fake();
        $this->makeGuestOrder();

        $this->claim();
        $this->post('/verify-email', ['otp' => '000000'])->assertSessionHasErrors('otp');

        $this->assertNull(User::where('email', 'claimant@example.com')->first());
        $this->assertGuest();
    }

    public function test_order_claimed_by_someone_else_while_otp_pending_is_not_overwritten(): void
    {
        Mail::fake();
        $order = $this->makeGuestOrder();

        $this->claim();
        $otp = $this->lastSentOtp();

        // Someone else claims the order through a different means while this OTP is pending.
        $otherOwner = User::factory()->create();
        $order->update(['user_id' => $otherOwner->id]);

        $this->post('/verify-email', ['otp' => $otp])->assertRedirect(route('account'));

        $newUser = User::where('email', 'claimant@example.com')->first();
        $this->assertNotNull($newUser, 'The account should still be created.');
        $this->assertEquals($otherOwner->id, $order->fresh()->user_id, 'The order must not be reassigned.');
    }
}
