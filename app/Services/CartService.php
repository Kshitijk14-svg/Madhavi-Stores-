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
     * Build the cart summary for a user.
     *
     * Side effects (intentional, matches prior behaviour): removes orphaned cart
     * rows whose product was deleted, and clears an invalid session coupon.
     *
     * @return array{cartItems:\Illuminate\Support\Collection,subtotal:float,discount:float,tax:float,total:float,coupon:?Coupon,couponCode:?string,cartCount:int}
     */
    public function getSummary(User $user): array
    {
        $cartItems = CartItem::with('product')->where('user_id', $user->id)->get();

        $subtotal = 0;
        foreach ($cartItems as $key => $item) {
            if (!$item->product) {
                $item->delete();
                $cartItems->forget($key);
                continue;
            }
            $subtotal += $item->product->final_price * $item->quantity;
        }

        [$discount, $coupon] = $this->resolveCoupon($user, session('applied_coupon'), $subtotal, false);

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
    private function resolveCoupon(User $user, ?string $code, float $subtotal, bool $lock): array
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

        $userUsage = Order::where('user_id', $user->id)->where('coupon_code', $code)->count();

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

            [$discount, $coupon] = $this->resolveCoupon($user, $couponCode, $subtotal, true);

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
}
