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

        // Coupons require a real account — a guest's checkout email is never
        // verified, so there's no durable identity to enforce a per-user limit
        // against. Deny outright rather than try to track unverifiable usage.
        if ($owner->isGuest()) {
            if (!$lock) {
                session()->forget('applied_coupon');
            }
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

        $userUsage = Order::where('user_id', $owner->userId)->where('coupon_code', $code)->count();

        if (!$coupon->isValidFor($subtotal, $userUsage)) {
            if (!$lock) {
                session()->forget('applied_coupon');
            }
            return [0, null];
        }

        return [$coupon->calculateDiscount($subtotal), $coupon];
    }

    /**
     * Best-effort, unlocked stock check used before charging the customer
     * (CheckoutController::store()) to reject an obviously out-of-stock cart
     * before payment starts. NOT a substitute for the locked check inside
     * createOrder() — completing a Razorpay payment can take the customer
     * minutes, so stock can still run out after this check passes. Returns
     * the name of the first unavailable item, or null if everything is fine.
     */
    public function findUnavailableItem(CartOwner $owner): ?string
    {
        $cartItems = $owner->scope(CartItem::with('product'))->get();

        foreach ($cartItems as $item) {
            if (!$item->product) {
                continue;
            }

            if ($item->product->has_sizes && $item->size) {
                $stock = ProductSize::where('product_id', $item->product_id)->where('size', $item->size)->value('stock');
                if ($stock === null || $stock < $item->quantity) {
                    return $item->product->name . ' (size ' . $item->size . ')';
                }
            } elseif ($item->product->stock < $item->quantity) {
                return $item->product->name;
            }
        }

        return null;
    }

    /**
     * Create an order atomically.
     *
     * Guarantees:
     *  - Idempotent on razorpay_payment_id (a replayed browser POST or a webhook
     *    firing after the browser already created the order returns the existing
     *    order instead of duplicating it) — including the case where two
     *    concurrent requests both pass the pre-transaction check below before
     *    either inserts; the DB-level unique index on razorpay_payment_id catches
     *    that race and the QueryException handler below returns the winner's row.
     *  - Pricing recomputed under the transaction using final_price.
     *  - Coupon row locked + re-validated before incrementing its usage.
     *  - Stock is checked for ALL items (locked) before anything is mutated.
     *    - If payment has NOT been captured yet (COD, or no payment info at all):
     *      an oversell throws CheckoutException and the whole attempt rolls back —
     *      nothing was charged, so rejecting outright is safe.
     *    - If payment HAS already been captured (payment_status === 'Paid', i.e.
     *      Razorpay verifyPayment/webhook): an oversell must never silently
     *      vanish a paid order. The Order + OrderItem rows are still persisted
     *      (order_status = 'Cancelled', stock untouched) so the charge is visible
     *      to the customer and admin instead of leaving only a log line. The
     *      caller is responsible for triggering a refund once it sees that status.
     *
     * @param  array  $customer  first_name,last_name,email,address,city,postal_code
     * @param  array  $payment   razorpay_order_id,razorpay_payment_id,razorpay_signature,payment_status
     * @param  ?string $couponCode  explicit coupon (webhook context); falls back to session
     *
     * @throws CheckoutException  with a user-safe message (only when nothing was charged)
     */
    public function createOrder(CartOwner $owner, array $customer, string $paymentMethod, array $payment = [], ?string $couponCode = null): Order
    {
        // Idempotency guard — before opening a transaction.
        if (!empty($payment['razorpay_payment_id'])) {
            $existing = Order::where('razorpay_payment_id', $payment['razorpay_payment_id'])->first();
            if ($existing) {
                return $existing;
            }
        }

        $couponCode = $couponCode ?? session('applied_coupon');

        if (!empty($customer['email'])) {
            $customer['email'] = strtolower(trim($customer['email']));
        }

        $alreadyPaid = ($payment['payment_status'] ?? null) === 'Paid';

        try {
            return DB::transaction(function () use ($owner, $customer, $paymentMethod, $payment, $couponCode, $alreadyPaid) {
                $cartItems = $owner->scope(CartItem::with('product'))->get();

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

                [$discount, $coupon] = $this->resolveCoupon($owner, $couponCode, $subtotal, true);

                $tax   = 0;
                $total = max(0, $subtotal - $discount);

                $orderNumber = 'MS-' . strtoupper(Str::random(8));
                while (Order::where('order_number', $orderNumber)->exists()) {
                    $orderNumber = 'MS-' . strtoupper(Str::random(8));
                }

                // Pass 1: lock every relevant stock row and determine sufficiency
                // for ALL items before mutating anything, so a shortage on one
                // item never leaves an earlier item's stock partially decremented.
                $locked   = [];
                $shortage = null;
                foreach ($cartItems as $item) {
                    if (!$item->product) {
                        continue;
                    }

                    if ($item->product->has_sizes && $item->size) {
                        $row       = ProductSize::where('product_id', $item->product_id)
                            ->where('size', $item->size)
                            ->lockForUpdate()
                            ->first();
                        $label     = $item->product->name . ' (size ' . $item->size . ')';
                    } else {
                        $row   = Product::where('id', $item->product_id)->lockForUpdate()->first();
                        $label = $item->product->name;
                    }

                    $locked[] = ['item' => $item, 'row' => $row];

                    if ($shortage === null && (!$row || $row->stock < $item->quantity)) {
                        $shortage = $label . ' just went out of stock.';
                    }
                }

                if ($shortage !== null && !$alreadyPaid) {
                    throw new CheckoutException($shortage . ' Your order was not placed and you have not been charged.');
                }

                $oversoldButPaid = $shortage !== null;

                $order = Order::create([
                    'user_id'             => $owner->userId,
                    'order_number'        => $orderNumber,
                    'email'               => $customer['email'],
                    'phone'               => $customer['phone'] ?? null,
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
                    'order_status'        => $oversoldButPaid ? 'Cancelled' : 'Pending',
                    'subtotal'            => $subtotal,
                    'discount'            => $discount,
                    'tax'                 => $tax,
                    'total'               => $total,
                    'coupon_code'         => $coupon?->code,
                    'coupon_id'           => $coupon?->id,
                ]);

                foreach ($locked as $entry) {
                    $item = $entry['item'];

                    OrderItem::create([
                        'order_id'     => $order->id,
                        'product_id'   => $item->product_id,
                        'product_name' => $item->product->name,
                        'price'        => $item->product->final_price,
                        'quantity'     => $item->quantity,
                        'size'         => $item->size,
                        'color'        => $item->color,
                    ]);

                    if (!$oversoldButPaid) {
                        $entry['row']->decrement('stock', $item->quantity);
                    }
                }

                if ($oversoldButPaid) {
                    // Payment already captured — leave the cart and coupon usage
                    // untouched (support may need to see exactly what was ordered)
                    // and skip straight to returning the Cancelled order. The
                    // caller (CheckoutController) refunds the payment and alerts
                    // admin when it sees order_status === 'Cancelled' here.
                    return $order;
                }

                if ($coupon) {
                    $coupon->incrementUsage();
                }

                $owner->scope(CartItem::query())->delete();
                session()->forget('applied_coupon');

                return $order;
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // Two concurrent requests for the SAME payment (browser verifyPayment
            // racing the webhook) can both pass the idempotency guard above before
            // either has inserted; the unique index on razorpay_payment_id catches
            // that here instead of surfacing a 500 to whichever request loses.
            if (
                $e->getCode() === '23000'
                && !empty($payment['razorpay_payment_id'])
                && str_contains($e->getMessage(), 'razorpay_payment_id')
            ) {
                $existing = Order::where('razorpay_payment_id', $payment['razorpay_payment_id'])->first();
                if ($existing) {
                    return $existing;
                }
            }
            throw $e;
        }
    }

    /**
     * Attach any guest orders placed under this email to the now-real account —
     * called both right after a normal registration and from the post-purchase
     * "create an account from this order" flow (AuthController).
     */
    public function claimGuestOrders(User $user): int
    {
        return Order::where('email', $user->email)->whereNull('user_id')->update(['user_id' => $user->id]);
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
