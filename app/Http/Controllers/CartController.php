<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCartOwner;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductSize;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use ResolvesCartOwner;

    public function __construct(private CartService $cart)
    {
    }

    public function index(Request $request)
    {
        $owner   = $this->resolveOwner($request);
        $summary = $this->cart->getSummary($owner);

        return view('pages.cart', [
            'cartItems' => $summary['cartItems'],
            'subtotal'  => $summary['subtotal'],
            'discount'  => $summary['discount'],
            'tax'       => $summary['tax'],
            'total'     => $summary['total'],
            'coupon'    => $summary['coupon'],
            'cartCount' => $summary['cartCount'],
        ]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'size'       => 'nullable|string|max:10',
            'quantity'   => 'nullable|integer|min:1',
        ]);

        $owner     = $this->resolveOwner($request, creating: true);
        $productId = $request->product_id;
        $product   = Product::findOrFail($productId);

        $size     = $product->has_sizes ? ($request->size ?? 'M') : null;
        $quantity = $request->quantity ?? 1;

        $cartItem = $owner->scope(CartItem::where('product_id', $productId))
            ->where('size', $size)
            ->first();

        $requestedQuantity = $cartItem ? ($cartItem->quantity + $quantity) : $quantity;

        // Validate stock (best-effort, pre-checkout; the final authority is the
        // locked sufficiency check inside CartService::createOrder).
        if ($product->has_sizes) {
            $productSize = ProductSize::where('product_id', $productId)->where('size', $size)->first();
            if ($productSize && $productSize->stock < $requestedQuantity) {
                return $this->stockError($request, 'Sorry, only ' . $productSize->stock . ' items available in size ' . $size . '.');
            }
        } elseif ($product->stock < $requestedQuantity) {
            return $this->stockError($request, 'Sorry, only ' . $product->stock . ' items available in stock.');
        }

        if ($cartItem) {
            $cartItem->quantity += $quantity;
            $cartItem->save();
        } else {
            CartItem::create([
                ...$owner->attributes(),
                'product_id' => $productId,
                'size'       => $size,
                'quantity'   => $quantity,
            ]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'    => true,
                'message'    => 'Product added to your shopping bag.',
                'cart_count' => $owner->scope(CartItem::query())->sum('quantity'),
            ]);
        }

        return redirect()->route('cart')->with('success', 'Product added to your shopping bag.');
    }

    private function stockError(Request $request, string $message)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => false, 'message' => $message], 422);
        }
        return redirect()->back()->with('error', $message);
    }

    public function update(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|exists:cart_items,id',
            'action'       => 'required|in:increase,decrease',
        ]);

        $owner    = $this->resolveOwner($request);
        $cartItem = $owner->scope(CartItem::query())->where('id', $request->cart_item_id)->firstOrFail();

        if ($request->action === 'increase') {
            if ($cartItem->product) {
                if ($cartItem->product->has_sizes && $cartItem->size) {
                    $productSize = ProductSize::where('product_id', $cartItem->product_id)
                        ->where('size', $cartItem->size)->first();
                    if ($productSize && $productSize->stock <= $cartItem->quantity) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Sorry, only ' . $productSize->stock . ' items available in size ' . $cartItem->size . '.',
                        ], 422);
                    }
                } elseif ($cartItem->product->stock <= $cartItem->quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sorry, only ' . $cartItem->product->stock . ' items available in stock.',
                    ], 422);
                }
            }
            $cartItem->quantity += 1;
            $cartItem->save();
        } else {
            if ($cartItem->quantity > 1) {
                $cartItem->quantity -= 1;
                $cartItem->save();
            } else {
                $cartItem->delete();
            }
        }

        if ($request->ajax()) {
            $summary  = $this->cart->getSummary($owner);
            $stillIn  = $cartItem->exists;
            $lineTotal = ($stillIn && $cartItem->product)
                ? '₹' . number_format($cartItem->product->final_price * $cartItem->quantity)
                : 0;

            return response()->json([
                'success'       => true,
                'message'       => 'Shopping bag updated.',
                'cart_count'    => $summary['cartCount'],
                'item_quantity' => $stillIn ? $cartItem->quantity : 0,
                'item_total'    => $lineTotal,
                'subtotal'      => '₹' . number_format($summary['subtotal']),
                'discount'      => '₹' . number_format($summary['discount']),
                'tax'           => '₹' . number_format($summary['tax']),
                'total'         => '₹' . number_format($summary['total']),
            ]);
        }

        return redirect()->route('cart')->with('success', 'Shopping bag updated.');
    }

    public function remove(Request $request, $id)
    {
        $owner    = $this->resolveOwner($request);
        $cartItem = $owner->scope(CartItem::query())->where('id', $id)->firstOrFail();
        $cartItem->delete();

        if ($request->ajax()) {
            $summary = $this->cart->getSummary($owner);

            return response()->json([
                'success'    => true,
                'message'    => 'Product removed from your bag.',
                'cart_count' => $summary['cartCount'],
                'subtotal'   => '₹' . number_format($summary['subtotal']),
                'discount'   => '₹' . number_format($summary['discount']),
                'tax'        => '₹' . number_format($summary['tax']),
                'total'      => '₹' . number_format($summary['total']),
            ]);
        }

        return redirect()->route('cart')->with('success', 'Product removed from your bag.');
    }

    public function applyCoupon(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $owner  = $this->resolveOwner($request);
        $coupon = Coupon::where('code', $request->code)->first();

        if (!$coupon) {
            return $this->couponResponse($request, false, 'Invalid coupon code.');
        }

        // Compute subtotal with the same final_price logic the checkout uses.
        $summary  = $this->cart->getSummary($owner);
        $subtotal = $summary['subtotal'];

        // Validate exactly as checkout will (global + min cart + per-user limit),
        // so a coupon accepted here is never rejected at checkout. Guests have no
        // Order history, so their per-user usage is always 0.
        $userUsage = $owner->isGuest()
            ? 0
            : Order::where('user_id', $owner->userId)->where('coupon_code', $coupon->code)->count();
        if (!$coupon->isValidFor($subtotal, $userUsage)) {
            $msg = 'This coupon is inactive, expired, or no longer available.';
            if ($subtotal < $coupon->min_cart_value) {
                $msg = 'Minimum spend for coupon ' . $coupon->code . ' is ₹' . number_format($coupon->min_cart_value);
            } elseif ($userUsage >= $coupon->max_uses_per_user) {
                $msg = 'You have already used coupon ' . $coupon->code . '.';
            }
            return $this->couponResponse($request, false, $msg);
        }

        session(['applied_coupon' => $coupon->code]);

        return $this->couponResponse($request, true, 'Coupon "' . $coupon->code . '" applied successfully!');
    }

    private function couponResponse(Request $request, bool $success, string $message)
    {
        if ($request->ajax()) {
            return response()->json(['success' => $success, 'message' => $message]);
        }
        return redirect()->route('cart')->with($success ? 'success' : 'error', $message);
    }

    public function removeCoupon()
    {
        session()->forget('applied_coupon');
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Coupon removed.']);
        }
        return redirect()->route('cart')->with('success', 'Coupon removed.');
    }
}
