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
use App\Support\CartOwner;
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
        $order = $this->service()->createOrder(CartOwner::forUser($user), $this->customer, 'COD');

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
        $order = $this->service()->createOrder(CartOwner::forUser($user), $this->customer, 'COD');

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
            $this->service()->createOrder(CartOwner::forUser($user), $this->customer, 'COD');
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
        $this->service()->createOrder(CartOwner::forUser($user), $this->customer, 'COD');
    }

    public function test_paid_order_is_idempotent_on_payment_id(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 1000, 'stock' => 5]);
        CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($user);

        $payment = ['razorpay_payment_id' => 'pay_TEST123', 'payment_status' => 'Paid'];
        $first  = $this->service()->createOrder(CartOwner::forUser($user), $this->customer, 'Card', $payment);
        $second = $this->service()->createOrder(CartOwner::forUser($user), $this->customer, 'Card', $payment);

        $this->assertEquals($first->id, $second->id);
        $this->assertDatabaseCount('orders', 1);
        $this->assertEquals(4, $product->fresh()->stock, 'Stock must only be decremented once.');
    }

    public function test_empty_cart_throws(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->expectException(CheckoutException::class);
        $this->service()->createOrder(CartOwner::forUser($user), $this->customer, 'COD');
    }

    public function test_valid_coupon_discount_is_applied_at_checkout(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 1000, 'stock' => 5]);
        CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);
        $coupon = Coupon::factory()->create(['type' => 'percent', 'value' => 20, 'max_uses_per_user' => 1]);

        $this->actingAs($user);
        session(['applied_coupon' => $coupon->code]);

        $order = $this->service()->createOrder(CartOwner::forUser($user), $this->customer, 'COD');

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

        $order = $this->service()->createOrder(CartOwner::forUser($user), $this->customer, 'COD');

        $this->assertEquals(1000, $order->total, 'Per-user-exhausted coupon must not discount.');
        $this->assertNull($order->coupon_code);
    }

    public function test_guest_checkout_page_loads_with_cart_items(): void
    {
        $product = Product::factory()->create(['price' => 1000, 'stock' => 5]);
        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);
        $token = CartItem::whereNotNull('guest_token')->value('guest_token');

        $this->withCookie('guest_cart_token', $token)->get('/checkout')->assertOk();
    }

    public function test_guest_order_is_created_with_null_user_id(): void
    {
        $product = Product::factory()->create(['price' => 1000, 'stock' => 5]);
        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 2]);
        $token = CartItem::whereNotNull('guest_token')->value('guest_token');

        $order = $this->service()->createOrder(CartOwner::forGuestToken($token), $this->customer, 'COD');

        $this->assertNull($order->user_id);
        $this->assertEquals(2000, $order->total);
        $this->assertEquals(3, $product->fresh()->stock);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_guest_order_never_gets_a_coupon_discount(): void
    {
        // Coupons require a real account — a guest's checkout email is never
        // verified, so there's no durable identity to enforce a per-user limit
        // against. Guests are denied outright, regardless of the coupon's own validity.
        $product = Product::factory()->create(['price' => 1000, 'stock' => 5]);
        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);
        $token = CartItem::whereNotNull('guest_token')->value('guest_token');
        $coupon = Coupon::factory()->create(['type' => 'percent', 'value' => 20, 'max_uses_per_user' => 1]);

        $order = $this->service()->createOrder(CartOwner::forGuestToken($token), $this->customer, 'COD', [], $coupon->code);

        $this->assertEquals(1000, $order->total);
        $this->assertNull($order->coupon_code);
    }

    public function test_guest_cart_summary_never_shows_a_coupon_discount(): void
    {
        $product = Product::factory()->create(['price' => 1000, 'stock' => 5]);
        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);
        $token = CartItem::whereNotNull('guest_token')->value('guest_token');
        $coupon = Coupon::factory()->create(['type' => 'percent', 'value' => 20, 'max_uses_per_user' => 1]);
        session(['applied_coupon' => $coupon->code]);

        $summary = $this->service()->getSummary(CartOwner::forGuestToken($token));

        $this->assertEquals(0, $summary['discount']);
        $this->assertNull($summary['coupon']);
    }

    public function test_oversold_but_paid_order_is_persisted_as_cancelled_not_thrown_away(): void
    {
        // Two customers race for the last unit; this simulates the loser whose
        // payment was already captured by Razorpay before stock ran out. The
        // order must NOT be silently discarded — it has to be persisted (so the
        // customer/admin can see it and a refund can be issued), not thrown away
        // as a CheckoutException like the unpaid (COD) oversell case.
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 1000, 'stock' => 1]);
        CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 2]);

        $this->actingAs($user);

        $order = $this->service()->createOrder(
            CartOwner::forUser($user),
            $this->customer,
            'Razorpay',
            ['razorpay_payment_id' => 'pay_OVERSOLD1', 'payment_status' => 'Paid']
        );

        $this->assertEquals('Cancelled', $order->order_status);
        $this->assertEquals('Paid', $order->payment_status, 'CartService never calls the refund API itself — that is the controller\'s job once it sees order_status=Cancelled.');
        $this->assertEquals(1, $product->fresh()->stock, 'Stock must be untouched — nothing was actually fulfilled.');
        $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => 2]);
        $this->assertEquals(1, CartItem::count(), 'Cart must be left intact so support can see exactly what was ordered.');
    }

    public function test_sized_product_oversold_but_paid_is_persisted_as_cancelled(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->withSizes()->create(['price' => 1000]);
        ProductSize::factory()->create(['product_id' => $product->id, 'size' => 'M', 'stock' => 1]);
        CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 2, 'size' => 'M']);

        $this->actingAs($user);

        $order = $this->service()->createOrder(
            CartOwner::forUser($user),
            $this->customer,
            'Razorpay',
            ['razorpay_payment_id' => 'pay_OVERSOLD2', 'payment_status' => 'Paid']
        );

        $this->assertEquals('Cancelled', $order->order_status);
        $this->assertEquals(1, ProductSize::where('product_id', $product->id)->where('size', 'M')->value('stock'));
    }

    public function test_duplicate_razorpay_payment_id_is_rejected_at_db_level(): void
    {
        // Guards the TOCTOU window where a browser verifyPayment() call and the
        // Razorpay webhook race for the same payment: both can pass CartService's
        // pre-transaction SELECT check before either inserts. The DB-level unique
        // index (migration 2026_07_30_000001) is the backstop that actually
        // prevents two Order rows — this proves the constraint is in place.
        $user = User::factory()->create();
        Order::create([
            'user_id' => $user->id, 'order_number' => 'MS-DUPTEST1', 'email' => $user->email,
            'first_name' => 'a', 'last_name' => 'b', 'address' => 'c', 'city' => 'd', 'postal_code' => 'e',
            'payment_method' => 'Razorpay', 'payment_status' => 'Paid', 'order_status' => 'Pending',
            'razorpay_payment_id' => 'pay_DUPLICATE', 'subtotal' => 1000, 'discount' => 0, 'tax' => 0, 'total' => 1000,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Order::create([
            'user_id' => $user->id, 'order_number' => 'MS-DUPTEST2', 'email' => $user->email,
            'first_name' => 'a', 'last_name' => 'b', 'address' => 'c', 'city' => 'd', 'postal_code' => 'e',
            'payment_method' => 'Razorpay', 'payment_status' => 'Paid', 'order_status' => 'Pending',
            'razorpay_payment_id' => 'pay_DUPLICATE', 'subtotal' => 1000, 'discount' => 0, 'tax' => 0, 'total' => 1000,
        ]);
    }

    public function test_checkout_store_rejects_when_cart_item_exceeds_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 1000, 'stock' => 1]);
        CartItem::factory()->create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 5]);

        $response = $this->actingAs($user)->postJson('/checkout', $this->customer);

        $response->assertStatus(409);
        $response->assertJsonFragment(['success' => false]);
    }

    public function test_guest_cannot_apply_a_coupon(): void
    {
        $product = Product::factory()->create(['price' => 1000, 'stock' => 5]);
        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);
        $token = CartItem::whereNotNull('guest_token')->value('guest_token');
        Coupon::factory()->create(['code' => 'GUESTDENY']);

        $response = $this->withCookie('guest_cart_token', $token)
            ->post('/coupon/apply', ['code' => 'GUESTDENY'], ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertOk();
        $response->assertJson(['success' => false, 'message' => 'Please sign in to apply a coupon.']);
        $this->assertNull(session('applied_coupon'));
    }
}
