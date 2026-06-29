<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountIsolationTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(User $owner): Order
    {
        return Order::create([
            'user_id'        => $owner->id,
            'order_number'   => 'ORD-' . uniqid(),
            'email'          => $owner->email,
            'first_name'     => 'Test',
            'last_name'      => 'User',
            'address'        => '1 Test St',
            'city'           => 'Testville',
            'postal_code'    => '000000',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'order_status'   => 'pending',
            'subtotal'       => 100,
            'discount'       => 0,
            'tax'            => 0,
            'total'          => 100,
        ]);
    }

    public function test_user_cannot_download_another_users_receipt(): void
    {
        $alice = User::factory()->create();
        $bob   = User::factory()->create();
        $aliceOrder = $this->makeOrder($alice);

        // Bob requests Alice's order id directly via the URL.
        $this->actingAs($bob)
            ->get(route('account.order.receipt', $aliceOrder->id))
            ->assertNotFound();
    }

    public function test_user_cannot_update_another_users_cart_item(): void
    {
        $alice = User::factory()->create();
        $bob   = User::factory()->create();
        $aliceItem = CartItem::factory()->create(['user_id' => $alice->id]);

        $this->actingAs($bob)
            ->post(route('cart.update'), [
                'cart_item_id' => $aliceItem->id,
                'action'       => 'increase',
            ])
            ->assertNotFound();

        // Alice's item is untouched.
        $this->assertEquals(1, $aliceItem->fresh()->quantity);
    }

    public function test_user_cannot_remove_another_users_cart_item(): void
    {
        $alice = User::factory()->create();
        $bob   = User::factory()->create();
        $aliceItem = CartItem::factory()->create(['user_id' => $alice->id]);

        $this->actingAs($bob)
            ->post(route('cart.remove', $aliceItem->id))
            ->assertNotFound();

        $this->assertNotNull($aliceItem->fresh());
    }

    public function test_guest_cannot_access_account(): void
    {
        $this->get(route('account'))->assertRedirect(route('login'));
    }

    public function test_customer_cannot_access_admin(): void
    {
        $customer = User::factory()->create(); // role = customer
        $this->actingAs($customer)
            ->get('/admin')
            ->assertRedirect(route('home'));
    }
}
