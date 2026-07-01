<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestCartWishlistTest extends TestCase
{
    use RefreshDatabase;

    /** Read back the guest token the app just stored, so we can carry it into the next request. */
    private function guestTokenFromCartItems(): string
    {
        $token = CartItem::whereNotNull('guest_token')->value('guest_token');
        $this->assertNotNull($token, 'No guest cart_items row was created.');
        return $token;
    }

    public function test_guest_can_add_to_cart_and_receives_a_guest_token_cookie(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $response = $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 2]);

        $response->assertRedirect(route('cart'));
        $response->assertCookie('guest_cart_token');

        $row = CartItem::first();
        $this->assertNotNull($row);
        $this->assertNull($row->user_id);
        $this->assertNotNull($row->guest_token);
        $this->assertEquals(2, $row->quantity);
    }

    public function test_guest_can_view_cart_and_update_quantity(): void
    {
        $product = Product::factory()->create(['stock' => 10]);
        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);
        $token = $this->guestTokenFromCartItems();
        $itemId = CartItem::first()->id;

        $response = $this->withCookie('guest_cart_token', $token)
            ->get('/cart');
        $response->assertOk();

        // Note: postJson() only sends cookies if withCredentials() is enabled
        // (it mimics a cross-origin XHR), and doesn't set X-Requested-With —
        // so AJAX-branching endpoints are exercised via post() + that header
        // instead, exactly like the app's own real fetch() calls do.
        $update = $this->withCookie('guest_cart_token', $token)
            ->post('/cart/update', ['cart_item_id' => $itemId, 'action' => 'increase'], ['X-Requested-With' => 'XMLHttpRequest']);
        $update->assertOk();
        $update->assertJsonPath('item_quantity', 2);

        $this->assertEquals(2, CartItem::first()->quantity);
    }

    public function test_guest_can_toggle_wishlist(): void
    {
        $product = Product::factory()->create();

        $response = $this->post('/wishlist/toggle/' . $product->id, [], ['X-Requested-With' => 'XMLHttpRequest']);
        $response->assertOk();
        $response->assertJson(['success' => true, 'added' => true]);
        $response->assertCookie('guest_cart_token');

        $row = WishlistItem::first();
        $this->assertNotNull($row);
        $this->assertNull($row->user_id);
        $this->assertNotNull($row->guest_token);
    }

    public function test_guest_can_apply_coupon(): void
    {
        $product = Product::factory()->create(['price' => 1000, 'stock' => 10]);
        \App\Models\Coupon::factory()->create(['code' => 'GUEST10']);

        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);
        $token = $this->guestTokenFromCartItems();

        $response = $this->withCookie('guest_cart_token', $token)
            ->post('/coupon/apply', ['code' => 'GUEST10'], ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_guest_is_still_redirected_to_login_at_checkout(): void
    {
        $this->get('/checkout')->assertRedirect(route('login'));
        $this->post('/checkout', [])->assertRedirect(route('login'));
    }

    public function test_merge_on_login_combines_guest_and_existing_user_cart_without_duplicating(): void
    {
        $user = User::factory()->create();
        $productA = Product::factory()->create(['stock' => 100]);
        $productB = Product::factory()->create(['stock' => 100]);

        CartItem::factory()->create([
            'user_id' => $user->id, 'product_id' => $productA->id, 'quantity' => 1, 'size' => null,
        ]);

        // Guest (not logged in) adds product A again and product B.
        $this->post('/cart/add', ['product_id' => $productA->id, 'quantity' => 3]);
        $token = $this->guestTokenFromCartItems();
        $this->withCookie('guest_cart_token', $token)
            ->post('/cart/add', ['product_id' => $productB->id, 'quantity' => 1]);

        $this->withCookie('guest_cart_token', $token)
            ->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect();

        $this->assertDatabaseCount('cart_items', 2);
        $this->assertEquals(0, CartItem::whereNotNull('guest_token')->count());

        $rowA = CartItem::where('user_id', $user->id)->where('product_id', $productA->id)->first();
        $rowB = CartItem::where('user_id', $user->id)->where('product_id', $productB->id)->first();

        $this->assertNotNull($rowA);
        $this->assertEquals(4, $rowA->quantity); // 1 (existing) + 3 (guest)
        $this->assertNotNull($rowB);
        $this->assertEquals(1, $rowB->quantity);
    }

    public function test_merge_on_login_caps_combined_quantity_to_available_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 2]);

        CartItem::factory()->create([
            'user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1, 'size' => null,
        ]);

        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);
        // add() itself clamps to available stock (1 existing-in-DB + N requested vs stock=2),
        // so bypass the controller and seed the guest row directly at a quantity the
        // controller would have blocked, to exercise the merge's own cap independently.
        CartItem::whereNotNull('guest_token')->update(['quantity' => 5]);
        $token = $this->guestTokenFromCartItems();

        $this->withCookie('guest_cart_token', $token)
            ->post('/login', ['email' => $user->email, 'password' => 'password']);

        $row = CartItem::where('user_id', $user->id)->where('product_id', $product->id)->first();
        $this->assertEquals(2, $row->quantity); // capped to stock, not 1 + 5 = 6
    }

    public function test_merge_on_login_dedupes_wishlist(): void
    {
        $user = User::factory()->create();
        $productX = Product::factory()->create();
        $productY = Product::factory()->create();

        WishlistItem::create(['user_id' => $user->id, 'product_id' => $productX->id]);

        $this->post('/wishlist/toggle/' . $productX->id); // guest also wishlists X
        $token = WishlistItem::whereNotNull('guest_token')->value('guest_token');
        $this->withCookie('guest_cart_token', $token)
            ->post('/wishlist/toggle/' . $productY->id); // and Y

        $this->withCookie('guest_cart_token', $token)
            ->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertDatabaseCount('wishlist_items', 2);
        $this->assertEquals(0, WishlistItem::whereNotNull('guest_token')->count());
        $this->assertDatabaseHas('wishlist_items', ['user_id' => $user->id, 'product_id' => $productX->id]);
        $this->assertDatabaseHas('wishlist_items', ['user_id' => $user->id, 'product_id' => $productY->id]);
    }

    public function test_merge_on_otp_verify_register_also_merges_guest_cart(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        $product = Product::factory()->create(['stock' => 10]);

        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 2]);
        $token = $this->guestTokenFromCartItems();

        $this->withCookie('guest_cart_token', $token)->post('/register', [
            'name'                  => 'Guest Shopper',
            'email'                 => 'shopper@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $captured = null;
        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\OtpMail::class, function ($mail) use (&$captured) {
            $captured = $mail->otp;
            return true;
        });

        $this->withCookie('guest_cart_token', $token)
            ->post('/verify-email', ['otp' => $captured])
            ->assertRedirect();

        $user = User::where('email', 'shopper@example.com')->first();
        $this->assertNotNull($user);

        $row = CartItem::where('user_id', $user->id)->where('product_id', $product->id)->first();
        $this->assertNotNull($row);
        $this->assertEquals(2, $row->quantity);
        $this->assertEquals(0, CartItem::whereNotNull('guest_token')->count());
    }
}
