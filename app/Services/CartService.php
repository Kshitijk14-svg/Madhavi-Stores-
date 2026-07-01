<?php

namespace App\Services;

use App\Exceptions\CheckoutException;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\User;
use App\Models\WishlistItem;
use App\Support\CartOwner;
use App\Support\GuestCartToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Single source of truth for cart pricing and order creation.
 *
 * Previously the subtotal -> coupon -> discount -> total math was copy-pasted in
 * six places (CartController index/update/remove, CheckoutController index/store/
 * verify) and had drifted (cart ignored per-user coupon limits that checkout
 * enforced, and both ignored product discounts). Centralising it here guarantees
 * the cart, checkout and the charged total can never disagree.
 *
 * All pricing uses Product::final_price (discount-aware) — see App\Models\Product.
 */
class CartService
{
    /**
     * Build the cart summary for a cart owner (an authenticated user, or a guest
     * identified by a cookie token — see App\Support\CartOwner).
     *
     * Side effects (intentional, matches prior behaviour): removes orphaned cart
     * rows whose product was deleted, and clears an invalid session coupon.
     *
     * @return array{cartItems:\Illuminate\Support\Collection,subtotal:float,discount:float,tax:float,total:float,coupon:?Coupon,couponCode:?string,cartCount:int}
     */
    public function getSummary(CartOwner $owner): array
    {
        $cartItems = $owner->isEmpty()
            ? collect()
            : $owner->scope(CartItem::with('product'))->get();

        $subtotal = 0;
        foreach ($cartItems as $key => $item) {
            if (!$item->product) {
                $item->delete();
                $cartItems->forget($key);
                continue;
            }
            $subtotal += $item->product->final_price * $item->quantity;
        }

        [$discount, $coupon] = $this->resolveCoupon($owner, session('applied_coupon'), $subtotal, false);

        $tax   = 0;
        $total = max(0, $subtotal - $discount);

        return [
            'cartItems'  => $cartItems,
            'subtotal'   => $subtotal,
            'discount'   => $discount,
            'tax'        => $tax,
            'total'      => $total,
            'coupon'     => $coupon,
            'couponCode' => $coupon?->code,
            'cartCount'  => $cartItems->sum('quantity'),
        ];
    }

    /**
     * Resolve a coupon for a cart subtotal, enforcing the SAME rules the cart and
     * checkout both use (global validity + min cart + per-user usage limit).
     *
     * @param  bool  $lock  acquire a row lock (true only inside the order transaction)
     * @return array{0:float,1:?Coupon}  [discount, coupon|null]
     */
    private function resolveCoupon(CartOwner $owner, ?string $code, float $subtotal, bool $lock): array
    {
        if (!$code) {
            return [0, null];
        }

        $query = Coupon::where('code', $code);
        if ($lock) {
            $query->lockForUpdate();
        }
        $coupon = $query->first();

        if (!$coupon) {
            if (!$lock) {
                session()->forget('applied_coupon');
            }
            return [0, null];
        }

        // Guests have no Order history to have used a coupon against.
        $userUsage = $owner->isGuest()
            ? 0
            : Order::where('user_id', $owner->userId)->where('coupon_code', $code)->count();

        if (!$coupon->isValidFor($subtotal, $userUsage)) {
            if (!$lock) {
                session()->forget('applied_coupon');
            }
            return [0, null];
        }

        return [$coupon->calculateDiscount($subtotal), $coupon];
    }

    /**
     * Create an order atomically.
     *
     * Guarantees:
     *  - Idempotent on razorpay_payment_id (a replayed browser POST or a webhook
     *    firing after the browser already created the order returns the existing
     *    order instead of duplicating it).
     *  - Pricing recomputed under the transaction using final_price.
     *  - Coupon row locked + re-validated before incrementing its usage.
     *  - Stock decremented under lockForUpdate with an explicit sufficiency check —
     *    an oversell rolls the whole order back instead of silently clamping to 0.
     *
     * @param  array  $customer  first_name,last_name,email,address,city,postal_code
     * @param  array  $payment   razorpay_order_id,razorpay_payment_id,razorpay_signature,payment_status
     * @param  ?string $couponCode  explicit coupon (webhook context); falls back to session
     *
     * @throws CheckoutException  with a user-safe message
     */
    public function createOrder(User $user, array $customer, string $paymentMethod, array $payment = [], ?string $couponCode = null): Order
    {
        // Idempotency guard — before opening a transaction.
        if (!empty($payment['razorpay_payment_id'])) {
            $existing = Order::where('razorpay_payment_id', $payment['razorpay_payment_id'])->first();
            if ($existing) {
                return $existing;
            }
        }

        $couponCode = $couponCode ?? session('applied_coupon');

        return DB::transaction(function () use ($user, $customer, $paymentMethod, $payment, $couponCode) {
            $cartItems = CartItem::with('product')->where('user_id', $user->id)->get();

            if ($cartItems->isEmpty()) {
                throw new CheckoutException('Your shopping bag is empty.');
            }

            $subtotal = 0;
            foreach ($cartItems as $item) {
                if (!$item->product) {
                    continue;
                }
                $subtotal += $item->product->final_price * $item->quantity;
            }

            [$discount, $coupon] = $this->resolveCoupon(CartOwner::forUser($user), $couponCode, $subtotal, true);

            $tax   = 0;
            $total = max(0, $subtotal - $discount);

            $orderNumber = 'MS-' . strtoupper(Str::random(8));
            while (Order::where('order_number', $orderNumber)->exists()) {
                $orderNumber = 'MS-' . strtoupper(Str::random(8));
            }

            $order = Order::create([
                'user_id'             => $user->id,
                'order_number'        => $orderNumber,
                'email'               => $customer['email'],
                'first_name'          => $customer['first_name'],
                'last_name'           => $customer['last_name'],
                'address'             => $customer['address'],
                'city'                => $customer['city'],
                'postal_code'         => $customer['postal_code'],
                'payment_method'      => $paymentMethod,
                'razorpay_order_id'   => $payment['razorpay_order_id']   ?? null,
                'razorpay_payment_id' => $payment['razorpay_payment_id'] ?? null,
                'razorpay_signature'  => $payment['razorpay_signature']  ?? null,
                'payment_status'      => $payment['payment_status'] ?? 'Pending',
                'order_status'        => 'Pending',
                'subtotal'            => $subtotal,
                'discount'            => $discount,
                'tax'                 => $tax,
                'total'               => $total,
                'coupon_code'         => $coupon?->code,
                'coupon_id'           => $coupon?->id,
            ]);

            foreach ($cartItems as $item) {
                if (!$item->product) {
                    continue;
                }

                OrderItem::create([
                    'order_id'     => $order->id,
                    'product_id'   => $item->product_id,
                    'product_name' => $item->product->name,
                    'price'        => $item->product->final_price,
                    'quantity'     => $item->quantity,
                    'size'         => $item->size,
                    'color'        => $item->color,
                ]);

                if ($item->product->has_sizes && $item->size) {
                    $productSize = ProductSize::where('product_id', $item->product_id)
                        ->where('size', $item->size)
                        ->lockForUpdate()
                        ->first();
                    if (!$productSize || $productSize->stock < $item->quantity) {
                        throw new CheckoutException(
                            $item->product->name . ' (size ' . $item->size . ') just went out of stock. Your order was not placed and you have not been charged.'
                        );
                    }
                    $productSize->decrement('stock', $item->quantity);
                } else {
                    $product = Product::where('id', $item->product_id)->lockForUpdate()->first();
                    if (!$product || $product->stock < $item->quantity) {
                        throw new CheckoutException(
                            $item->product->name . ' just went out of stock. Your order was not placed and you have not been charged.'
                        );
                    }
                    $product->decrement('stock', $item->quantity);
                }
            }

            if ($coupon) {
                $coupon->incrementUsage();
            }

            CartItem::where('user_id', $user->id)->delete();
            session()->forget('applied_coupon');

            return $order;
        });
    }

    /**
     * Fold a guest's cart/wishlist into a user's, the moment they log in
     * (AuthController::login / verify). Runs atomically so a failure never
     * leaves a half-merged cart. No-op if the guest never had a cookie.
     */
    public function mergeGuestIntoUser(User $user, ?string $guestToken): void
    {
        if (!$guestToken) {
            return;
        }

        DB::transaction(function () use ($user, $guestToken) {
            $this->mergeGuestCartItems($user, $guestToken);
            $this->mergeGuestWishlistItems($user, $guestToken);
        });

        GuestCartToken::forget();
    }

    private function mergeGuestCartItems(User $user, string $guestToken): void
    {
        $guestItems = CartItem::with('product')->where('guest_token', $guestToken)->get();

        foreach ($guestItems as $guestItem) {
            $existing = CartItem::where('user_id', $user->id)
                ->where('product_id', $guestItem->product_id)
                ->where('size', $guestItem->size)
                ->where('color', $guestItem->color)
                ->first();

            if ($existing) {
                $combined = $existing->quantity + $guestItem->quantity;
                $cap = $this->availableStock($guestItem->product, $guestItem->size);
                $existing->quantity = $cap !== null ? max(1, min($combined, $cap)) : $combined;
                $existing->save();
            } else {
                $guestItem->user_id = $user->id;
                $guestItem->guest_token = null;
                $guestItem->save();
            }
        }

        // Cleans up rows left behind by the "existing" branch above; a no-op
        // for rows the "else" branch already re-pointed to the user.
        CartItem::where('guest_token', $guestToken)->delete();
    }

    private function mergeGuestWishlistItems(User $user, string $guestToken): void
    {
        $existingProductIds = WishlistItem::where('user_id', $user->id)->pluck('product_id')->all();

        foreach (WishlistItem::where('guest_token', $guestToken)->get() as $item) {
            if (in_array($item->product_id, $existingProductIds, true)) {
                continue; // already wishlisted — drop the guest duplicate
            }
            $item->update(['user_id' => $user->id, 'guest_token' => null]);
        }

        WishlistItem::where('guest_token', $guestToken)->delete();
    }

    /** Null means "no cap" (product/size vanished — leave the pre-existing quantity as-is). */
    private function availableStock(?Product $product, ?string $size): ?int
    {
        if (!$product) {
            return null;
        }
        if ($product->has_sizes && $size) {
            return ProductSize::where('product_id', $product->id)->where('size', $size)->value('stock');
        }
        return $product->stock;
    }
}
