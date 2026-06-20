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
        foreach ($cartItems as $key => $item) {
            if (!$item->product) {
                $item->delete();
                $cartItems->forget($key);
                continue;
            }
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

        $tax = 0;
        $total = $subtotal - $discount;

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
        $product = Product::findOrFail($productId);
        
        $size = $product->has_sizes ? ($request->size ?? 'M') : null;
        $quantity = $request->quantity ?? 1;

        $cartItem = CartItem::where('user_id', $user->id)
                            ->where('product_id', $productId)
                            ->where('size', $size)
                            ->first();

        $requestedQuantity = $cartItem ? ($cartItem->quantity + $quantity) : $quantity;

        // Validate stock
        if ($product->has_sizes) {
            $productSize = \App\Models\ProductSize::where('product_id', $productId)
                                                  ->where('size', $size)
                                                  ->first();
            if ($productSize && $productSize->stock < $requestedQuantity) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sorry, only ' . $productSize->stock . ' items available in size ' . $size . '.'
                    ], 422);
                }
                return redirect()->back()->with('error', 'Sorry, only ' . $productSize->stock . ' items available in size ' . $size . '.');
            }
        } else {
            if ($product->stock < $requestedQuantity) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sorry, only ' . $product->stock . ' items available in stock.'
                    ], 422);
                }
                return redirect()->back()->with('error', 'Sorry, only ' . $product->stock . ' items available in stock.');
            }
        }

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

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Product added to your shopping bag.',
                'cart_count' => CartItem::where('user_id', $user->id)->sum('quantity')
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

        $cartItem = CartItem::where('user_id', Auth::id())->where('id', $request->cart_item_id)->firstOrFail();

        if ($request->action === 'increase') {
            // Validate stock before increasing
            if ($cartItem->product) {
                if ($cartItem->product->has_sizes && $cartItem->size) {
                    $productSize = \App\Models\ProductSize::where('product_id', $cartItem->product_id)
                                                          ->where('size', $cartItem->size)->first();
                    if ($productSize && $productSize->stock <= $cartItem->quantity) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Sorry, only ' . $productSize->stock . ' items available in size ' . $cartItem->size . '.'
                        ], 422);
                    }
                } elseif ($cartItem->product->stock <= $cartItem->quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sorry, only ' . $cartItem->product->stock . ' items available in stock.'
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
                $cartItem->quantity = 0;
            }
        }

        if ($request->ajax()) {
            $user = Auth::user();
            $cartItems = CartItem::with('product')->where('user_id', $user->id)->get();
            $subtotal = $cartItems->sum(function($item) {
                if (!$item->product) return 0;
                return $item->product->price * $item->quantity;
            });
            
            $discount = 0;
            $couponCode = session('applied_coupon');
            if ($couponCode) {
                $coupon = Coupon::where('code', $couponCode)->first();
                if ($coupon && $coupon->isValidFor($subtotal)) {
                    $discount = $coupon->calculateDiscount($subtotal);
                } else {
                    session()->forget('applied_coupon');
                }
            }
            $tax = 0;
            $total = $subtotal - $discount;

            return response()->json([
                'success' => true,
                'message' => 'Shopping bag updated.',
                'cart_count' => $cartItems->sum('quantity'),
                'item_quantity' => $cartItem->exists ? $cartItem->quantity : 0,
                'item_total' => $cartItem->exists ? '₹' . number_format($cartItem->product->price * $cartItem->quantity) : 0,
                'subtotal' => '₹' . number_format($subtotal),
                'discount' => '₹' . number_format($discount),
                'tax' => '₹' . number_format($tax),
                'total' => '₹' . number_format($total)
            ]);
        }

        return redirect()->route('cart')->with('success', 'Shopping bag updated.');
    }

    public function remove($id)
    {
        $cartItem = CartItem::where('user_id', Auth::id())->where('id', $id)->firstOrFail();
        $cartItem->delete();

        if (request()->ajax()) {
            $user = Auth::user();
            $cartItems = CartItem::with('product')->where('user_id', $user->id)->get();
            $subtotal = $cartItems->sum(function($item) {
                if (!$item->product) return 0;
                return $item->product->price * $item->quantity;
            });
            
            $discount = 0;
            $couponCode = session('applied_coupon');
            if ($couponCode) {
                $coupon = Coupon::where('code', $couponCode)->first();
                if ($coupon && $coupon->isValidFor($subtotal)) {
                    $discount = $coupon->calculateDiscount($subtotal);
                } else {
                    session()->forget('applied_coupon');
                }
            }
            $tax = 0;
            $total = $subtotal - $discount;

            return response()->json([
                'success' => true,
                'message' => 'Product removed from your bag.',
                'cart_count' => $cartItems->sum('quantity'),
                'subtotal' => '₹' . number_format($subtotal),
                'discount' => '₹' . number_format($discount),
                'tax' => '₹' . number_format($tax),
                'total' => '₹' . number_format($total)
            ]);
        }

        return redirect()->route('cart')->with('success', 'Product removed from your bag.');
    }

    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $coupon = Coupon::where('code', $request->code)->first();

        if (!$coupon) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Invalid coupon code.']);
            }
            return redirect()->route('cart')->with('error', 'Invalid coupon code.');
        }

        $user = Auth::user();
        $cartItems = CartItem::with('product')->where('user_id', $user->id)->get();
        
        $subtotal = 0;
        foreach ($cartItems as $key => $item) {
            if (!$item->product) {
                $item->delete();
                $cartItems->forget($key);
                continue;
            }
            $subtotal += ($item->product->price * $item->quantity);
        }

        if (!$coupon->isValidFor($subtotal)) {
            $msg = 'Coupon is inactive or expired.';
            if ($subtotal < $coupon->min_cart_value) {
                $msg = 'Minimum spend for coupon ' . $coupon->code . ' is ₹' . number_format($coupon->min_cart_value);
            }
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg]);
            }
            return redirect()->route('cart')->with('error', $msg);
        }

        session(['applied_coupon' => $coupon->code]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Coupon "' . $coupon->code . '" applied successfully!']);
        }
        return redirect()->route('cart')->with('success', 'Coupon "' . $coupon->code . '" applied successfully!');
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
