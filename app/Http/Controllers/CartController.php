<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\CartItem;
use App\Models\Coupon;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $cartItems = CartItem::with('product')->where('user_id', $user->id)->get();
        
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += ($item->product->price * $item->quantity);
        }

        // Apply coupon discount if set in session
        $discount = 0;
        $couponCode = session('applied_coupon');
        $coupon = null;
        if ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)->first();
            if ($coupon && $coupon->isValidFor($subtotal)) {
                $discount = $coupon->calculateDiscount($subtotal);
            } else {
                session()->forget('applied_coupon');
            }
        }

        $tax = round($subtotal * 0.18, 2); // 18% GST standard
        $total = ($subtotal - $discount) + $tax;

        $cartCount = $cartItems->sum('quantity');

        return view('pages.cart', compact('cartItems', 'subtotal', 'discount', 'tax', 'total', 'coupon', 'cartCount'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'size' => 'nullable|string|max:10',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $user = Auth::user();
        $productId = $request->product_id;
        $size = $request->size ?? 'M';
        $quantity = $request->quantity ?? 1;

        $cartItem = CartItem::where('user_id', $user->id)
                            ->where('product_id', $productId)
                            ->where('size', $size)
                            ->first();

        if ($cartItem) {
            $cartItem->quantity += $quantity;
            $cartItem->save();
        } else {
            CartItem::create([
                'user_id' => $user->id,
                'product_id' => $productId,
                'size' => $size,
                'quantity' => $quantity,
            ]);
        }

        return redirect()->route('cart')->with('success', 'Product added to your shopping bag.');
    }

    public function update(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|exists:cart_items,id',
            'action' => 'required|in:increase,decrease',
        ]);

        $cartItem = CartItem::findOrFail($request->cart_item_id);
        
        if ($request->action === 'increase') {
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

        return redirect()->route('cart')->with('success', 'Shopping bag updated.');
    }

    public function remove($id)
    {
        $cartItem = CartItem::where('user_id', Auth::id())->where('id', $id)->firstOrFail();
        $cartItem->delete();

        return redirect()->route('cart')->with('success', 'Product removed from your bag.');
    }

    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $coupon = Coupon::where('code', $request->code)->first();

        if (!$coupon) {
            return redirect()->route('cart')->with('error', 'Invalid coupon code.');
        }

        $user = Auth::user();
        $cartItems = CartItem::with('product')->where('user_id', $user->id)->get();
        
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += ($item->product->price * $item->quantity);
        }

        if (!$coupon->isValidFor($subtotal)) {
            if ($subtotal < $coupon->min_cart_value) {
                return redirect()->route('cart')->with('error', 'Minimum spend for coupon ' . $coupon->code . ' is ₹' . number_format($coupon->min_cart_value));
            }
            return redirect()->route('cart')->with('error', 'Coupon is inactive or expired.');
        }

        session(['applied_coupon' => $coupon->code]);

        return redirect()->route('cart')->with('success', 'Coupon "' . $coupon->code . '" applied successfully!');
    }

    public function removeCoupon()
    {
        session()->forget('applied_coupon');
        return redirect()->route('cart')->with('success', 'Coupon removed.');
    }
}
