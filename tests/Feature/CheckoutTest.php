<?php

namespace Tests\Feature;

use App\Exceptions\CheckoutException;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private array $customer = [
        'first_name'  => 'Test',
        'last_name'   => 'Buyer',
        'email'       => 'buyer@example.com',
        'phone'       => '9876543210',
        'address'     => '1 Test St',
        'city'        => 'Mumbai',
        'postal_code' => '400001',
    ];

    private function service(): CartService
    {
        return app(CartService::class);
    }

    public function test_checkout_page_loads_for_authenticated_user_with_cart_items(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 1000, 'stock' => 5]);
        CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($user)->get('/checkout')->assertOk();
    }

    public function test_checkout_requires_a_phone_number(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 1000, 'stock' => 5]);
        CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

        $payload = collect($this->customer)->except('phone')->all();

        $this->actingAs($user)
            ->postJson('/checkout', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors('phone');
    }

    public function test_checkout_rejects_a_non_indian_looking_phone_number(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 1000, 'stock' => 5]);
        CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

        $payload = array_merge($this->customer, ['phone' => '1234567890']); // leading digit not 6-9

        $this->actingAs($user)
            ->postJson('/checkout', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors('phone');
    }

    public function test_checkout_accepts_a_valid_phone_number_and_passes_validation(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 1000, 'stock' => 5]);
        CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

        $response = $this->actingAs($user)->postJson('/checkout', $this->customer);

        // No Razorpay keys configured in the test environment, so this can't
        // reach a real/mocked gateway response — but a valid phone must get
        // past validation (422) and fail for the unrelated "gateway not
        // configured" reason (503) instead.
        $response->assertStatus(503);
    }

    public function test_cod_order_is_created_and_stock_decremented(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 1000, 'stock' => 5]);
        CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 2]);

        $this->actingAs($user);
        $order = $this->service()->createOrder($user, $this->customer, 'COD');

        $this->assertEquals('9876543210', $order->phone);
        $this->assertEquals(2000, $order->total);
        $this->assertEquals('COD', $order->payment_method);
        $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => 2]);
        $this->assertEquals(3, $product->fresh()->stock);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_order_charges_the_discounted_final_price(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->percentDiscount(10)->create(['price' => 1000, 'stock' => 5]);
        CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($user);
        $order = $this->service()->createOrder($user, $this->customer, 'COD');

        $this->assertEquals(900, $order->total);
        $this->assertEquals(900, $order->items->first()->price);
    }

    public function test_oversell_is_rejected_and_rolls_back(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 1000, 'stock' => 1]);
        CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 2]);

        $this->actingAs($user);

        try {
            $this->service()->createOrder($user, $this->customer, 'COD');
            $this->fail('Expected CheckoutException for oversell.');
        } catch (CheckoutException $e) {
            // expected
        }

        $this->assertDatabaseCount('orders', 0);
        $this->assertEquals(1, $product->fresh()->stock, 'Stock must be untouched after rollback.');
        $this->assertEquals(1, \App\Models\CartItem::count(), 'Cart must not be cleared on a failed order.');
    }

    public function test_sized_product_oversell_is_rejected(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->withSizes()->create(['price' => 1000]);
        ProductSize::factory()->create(['product_id' => $product->id, 'size' => 'M', 'stock' => 1]);
        CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 2, 'size' => 'M']);

        $this->actingAs($user);

        $this->expectException(CheckoutException::class);
        $this->service()->createOrder($user, $this->customer, 'COD');
    }

    public function test_paid_order_is_idempotent_on_payment_id(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 1000, 'stock' => 5]);
        CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($user);

        $payment = ['razorpay_payment_id' => 'pay_TEST123', 'payment_status' => 'Paid'];
        $first  = $this->service()->createOrder($user, $this->customer, 'Card', $payment);
        $second = $this->service()->createOrder($user, $this->customer, 'Card', $payment);

        $this->assertEquals($first->id, $second->id);
        $this->assertDatabaseCount('orders', 1);
        $this->assertEquals(4, $product->fresh()->stock, 'Stock must only be decremented once.');
    }

    public function test_empty_cart_throws(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->expectException(CheckoutException::class);
        $this->service()->createOrder($user, $this->customer, 'COD');
    }

    public function test_valid_coupon_discount_is_applied_at_checkout(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 1000, 'stock' => 5]);
        CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);
        $coupon = Coupon::factory()->create(['type' => 'percent', 'value' => 20, 'max_uses_per_user' => 1]);

        $this->actingAs($user);
        session(['applied_coupon' => $coupon->code]);

        $order = $this->service()->createOrder($user, $this->customer, 'COD');

        $this->assertEquals(800, $order->total);
        $this->assertEquals($coupon->code, $order->coupon_code);
        $this->assertEquals(1, $coupon->fresh()->used_count);
    }

    public function test_per_user_exhausted_coupon_is_ignored_at_checkout(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 1000, 'stock' => 5]);
        CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);
        $coupon = Coupon::factory()->create(['type' => 'percent', 'value' => 20, 'max_uses_per_user' => 1]);

        // Simulate the user already having used this coupon once.
        Order::create([
            'user_id' => $user->id, 'order_number' => 'MS-PRIOR01', 'email' => $user->email,
            'first_name' => 'a', 'last_name' => 'b', 'address' => 'c', 'city' => 'd', 'postal_code' => 'e',
            'payment_method' => 'COD', 'payment_status' => 'Pending', 'order_status' => 'Pending',
            'subtotal' => 1000, 'discount' => 200, 'tax' => 0, 'total' => 800, 'coupon_code' => $coupon->code,
        ]);

        $this->actingAs($user);
        session(['applied_coupon' => $coupon->code]);

        $order = $this->service()->createOrder($user, $this->customer, 'COD');

        $this->assertEquals(1000, $order->total, 'Per-user-exhausted coupon must not discount.');
        $this->assertNull($order->coupon_code);
    }
}
