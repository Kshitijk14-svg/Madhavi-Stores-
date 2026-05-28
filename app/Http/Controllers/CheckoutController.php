<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Coupon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $cartItems = CartItem::with('product')->where('user_id', $user->id)->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Your shopping bag is empty.');
        }

        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += ($item->product->price * $item->quantity);
        }

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

        return view('pages.checkout', compact('cartItems', 'subtotal', 'discount', 'tax', 'total', 'coupon'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'payment_method' => 'required|string',
        ]);

        $user = Auth::user();
        $cartItems = CartItem::with('product')->where('user_id', $user->id)->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Your shopping bag is empty.');
        }

        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += ($item->product->price * $item->quantity);
        }

        $discount = 0;
        $couponCode = session('applied_coupon');
        if ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)->first();
            if ($coupon && $coupon->isValidFor($subtotal)) {
                $discount = $coupon->calculateDiscount($subtotal);
            }
        }

        $tax = 0;
        $total = $subtotal - $discount;

        // Generate unique order number
        $orderNumber = 'MS-' . strtoupper(Str::random(8));
        while (Order::where('order_number', $orderNumber)->exists()) {
            $orderNumber = 'MS-' . strtoupper(Str::random(8));
        }

        // Determine initial payment status based on method
        $paymentStatus = ($request->payment_method === 'COD') ? 'Pending' : 'Paid';

        // Create Order
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => $orderNumber,
            'email' => $request->email,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'address' => $request->address,
            'city' => $request->city,
            'postal_code' => $request->postal_code,
            'payment_method' => $request->payment_method,
            'payment_status' => $paymentStatus,
            'order_status' => 'Pending',
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total' => $total,
            'coupon_code' => $couponCode,
        ]);

        // Create Order Items
        foreach ($cartItems as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'price' => $item->product->price,
                'quantity' => $item->quantity,
                'size' => $item->size,
                'color' => $item->color,
            ]);

            // Decrement Stock
            if ($item->product->has_sizes && $item->size) {
                $productSize = \App\Models\ProductSize::where('product_id', $item->product_id)
                                                      ->where('size', $item->size)
                                                      ->first();
                if ($productSize) {
                    $productSize->stock = max(0, $productSize->stock - $item->quantity);
                    $productSize->save();
                }
            } else {
                $product = $item->product;
                $product->stock = max(0, $product->stock - $item->quantity);
                $product->save();
            }
        }

        // Clear cart
        CartItem::where('user_id', $user->id)->delete();
        session()->forget('applied_coupon');

        return redirect()->route('account')->with('success', 'Thank you! Your order ' . $orderNumber . ' was successfully placed.');
    }
}
