<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTrackingTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(array $overrides = []): Order
    {
        return Order::create(array_merge([
            'user_id' => null,
            'order_number' => 'MS-TRACK01',
            'email' => 'guest@example.com',
            'first_name' => 'Guest',
            'last_name' => 'Buyer',
            'address' => '1 Test St',
            'city' => 'Mumbai',
            'postal_code' => '400001',
            'payment_method' => 'COD',
            'payment_status' => 'Pending',
            'order_status' => 'Pending',
            'subtotal' => 1000,
            'discount' => 0,
            'tax' => 0,
            'total' => 1000,
        ], $overrides));
    }

    public function test_guest_can_look_up_an_order_by_number_and_email(): void
    {
        $this->makeOrder();

        $response = $this->post('/track-order', [
            'order_number' => 'MS-TRACK01',
            'email'        => 'guest@example.com',
        ]);

        $response->assertRedirect();
        $response = $this->get($response->headers->get('Location'));
        $response->assertOk();
        $response->assertSee('MS-TRACK01');
    }

    public function test_lookup_with_wrong_email_does_not_leak_order(): void
    {
        $this->makeOrder();

        $response = $this->post('/track-order', [
            'order_number' => 'MS-TRACK01',
            'email'        => 'wrong@example.com',
        ]);

        $response->assertRedirect();
        $response = $this->get($response->headers->get('Location'));
        $response->assertOk();
        $response->assertDontSee('1 Test St');
    }

    public function test_lookup_matches_case_insensitively(): void
    {
        $this->makeOrder(['email' => 'guest@example.com']);

        $response = $this->post('/track-order', [
            'order_number' => 'MS-TRACK01',
            'email'        => 'Guest@Example.com',
        ]);

        $response->assertRedirect();
        $response = $this->get($response->headers->get('Location'));
        $response->assertOk();
        $response->assertSee('MS-TRACK01');
    }

    public function test_lookup_does_not_leak_order_details_via_query_string(): void
    {
        $this->makeOrder();

        $response = $this->post('/track-order', [
            'order_number' => 'MS-TRACK01',
            'email'        => 'guest@example.com',
        ]);

        $location = $response->headers->get('Location');
        $this->assertStringNotContainsString('MS-TRACK01', $location);
        $this->assertStringNotContainsString('guest%40example.com', $location);
        $this->assertStringNotContainsString('email=', $location);
    }
}
