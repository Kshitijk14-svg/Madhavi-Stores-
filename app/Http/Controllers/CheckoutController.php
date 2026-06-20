<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Coupon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Razorpay\Api\Api;

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
            if (!$item->product) continue;
            $subtotal += ($item->product->price * $item->quantity);
        }

        $discount = 0;
        $couponCode = session('applied_coupon');
        $coupon = null;
        if ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)->first();
            $userUsageCount = Order::where('user_id', $user->id)->where('coupon_code', $couponCode)->count();
            if ($coupon && $coupon->isValidFor($subtotal, $userUsageCount)) {
                $discount = $coupon->calculateDiscount($subtotal);
            } else {
                session()->forget('applied_coupon');
                $coupon = null;
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
            'payment_method' => 'required|string|in:COD,Card,UPI',
        ]);

        $user = Auth::user();
        $cartItems = CartItem::with('product')->where('user_id', $user->id)->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Your shopping bag is empty.'
            ], 400);
        }

        $subtotal = 0;
        foreach ($cartItems as $item) {
            if (!$item->product) continue;
            $subtotal += ($item->product->price * $item->quantity);
        }

        $discount = 0;
        $coupon = null;
        $couponCode = session('applied_coupon');
        if ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)->first();
            $userUsageCount = Order::where('user_id', $user->id)->where('coupon_code', $couponCode)->count();
            if ($coupon && $coupon->isValidFor($subtotal, $userUsageCount)) {
                $discount = $coupon->calculateDiscount($subtotal);
            } else {
                $coupon = null;
            }
        }

        $tax = 0;
        $total = $subtotal - $discount;

        if ($request->payment_method === 'COD') {
            $orderNumber = 'MS-' . strtoupper(Str::random(8));
            while (Order::where('order_number', $orderNumber)->exists()) {
                $orderNumber = 'MS-' . strtoupper(Str::random(8));
            }

            DB::transaction(function () use ($request, $user, $cartItems, $subtotal, $discount, $tax, $total, $couponCode, $coupon, $orderNumber) {
                $order = Order::create([
                    'user_id' => $user->id,
                    'order_number' => $orderNumber,
                    'email' => $request->email,
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'address' => $request->address,
                    'city' => $request->city,
                    'postal_code' => $request->postal_code,
                    'payment_method' => 'COD',
                    'payment_status' => 'Pending',
                    'order_status' => 'Pending',
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'tax' => $tax,
                    'total' => $total,
                    'coupon_code' => $couponCode,
                ]);

                foreach ($cartItems as $item) {
                    if (!$item->product) continue;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name,
                        'price' => $item->product->price,
                        'quantity' => $item->quantity,
                        'size' => $item->size,
                        'color' => $item->color,
                    ]);

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

                if ($coupon) {
                    $coupon->incrementUsage();
                }

                CartItem::where('user_id', $user->id)->delete();
                session()->forget('applied_coupon');
            });

            session()->flash('success', 'Thank you! Your order ' . $orderNumber . ' was successfully placed.');
            return response()->json([
                'success' => true,
                'redirect' => route('account'),
                'message' => 'Thank you! Your order ' . $orderNumber . ' was successfully placed.'
            ]);
        }

        // Razorpay (Card or UPI)
        $keyId = config('razorpay.key_id');
        $keySecret = config('razorpay.key_secret');

        if ($keyId === 'rzp_test_dummykey123' || empty($keyId) || empty($keySecret)) {
            if (!app()->isLocal()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment gateway is not configured. Please contact support.'
                ], 503);
            }

            $razorpayOrderId = 'order_fake_' . Str::random(14);
            return response()->json([
                'success' => true,
                'payment_method' => $request->payment_method,
                'razorpay_order_id' => $razorpayOrderId,
                'razorpay_key' => 'rzp_test_dummykey123',
                'amount' => intval(round($total * 100)),
                'currency' => 'INR',
                'company_name' => 'Madhavi Stores',
                'is_mock' => true,
                'customer' => [
                    'name' => $request->first_name . ' ' . $request->last_name,
                    'email' => $request->email,
                ]
            ]);
        }

        try {
            $api = new Api($keyId, $keySecret);
            $razorpayOrder = $api->order->create([
                'receipt'         => 'rcpt_' . time() . '_' . $user->id,
                'amount'          => intval(round($total * 100)),
                'currency'        => 'INR',
                'payment_capture' => 1
            ]);

            return response()->json([
                'success' => true,
                'payment_method' => $request->payment_method,
                'razorpay_order_id' => $razorpayOrder['id'],
                'razorpay_key' => $keyId,
                'amount' => intval(round($total * 100)),
                'currency' => 'INR',
                'company_name' => 'Madhavi Stores',
                'is_mock' => false,
                'customer' => [
                    'name' => $request->first_name . ' ' . $request->last_name,
                    'email' => $request->email,
                ]
            ]);
        } catch (\Exception $e) {
            logger()->error('Razorpay Order creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Could not initiate Razorpay payment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function verifyPayment(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'payment_method' => 'required|string|in:Card,UPI',
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $user = Auth::user();
        $cartItems = CartItem::with('product')->where('user_id', $user->id)->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Your shopping bag is empty.'
            ], 400);
        }

        $subtotal = 0;
        foreach ($cartItems as $item) {
            if (!$item->product) continue;
            $subtotal += ($item->product->price * $item->quantity);
        }

        $discount = 0;
        $coupon = null;
        $couponCode = session('applied_coupon');
        if ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)->first();
            $userUsageCount = Order::where('user_id', $user->id)->where('coupon_code', $couponCode)->count();
            if ($coupon && $coupon->isValidFor($subtotal, $userUsageCount)) {
                $discount = $coupon->calculateDiscount($subtotal);
            } else {
                $coupon = null;
            }
        }

        $tax = 0;
        $total = $subtotal - $discount;

        $keyId = config('razorpay.key_id');
        $keySecret = config('razorpay.key_secret');
        $isMock = app()->isLocal() && ($keyId === 'rzp_test_dummykey123' || empty($keyId) || empty($keySecret) || Str::startsWith($request->razorpay_order_id, 'order_fake_'));

        if (!$isMock) {
            try {
                $api = new Api($keyId, $keySecret);
                $attributes = [
                    'razorpay_order_id' => $request->razorpay_order_id,
                    'razorpay_payment_id' => $request->razorpay_payment_id,
                    'razorpay_signature' => $request->razorpay_signature
                ];
                $api->utility->verifyPaymentSignature($attributes);
            } catch (\Exception $e) {
                logger()->error('Razorpay signature verification failed: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Payment signature verification failed. Please contact support.'
                ], 400);
            }
        }

        $orderNumber = 'MS-' . strtoupper(Str::random(8));
        while (Order::where('order_number', $orderNumber)->exists()) {
            $orderNumber = 'MS-' . strtoupper(Str::random(8));
        }

        DB::transaction(function () use ($request, $user, $cartItems, $subtotal, $discount, $tax, $total, $couponCode, $coupon, $orderNumber) {
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
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
                'payment_status' => 'Paid',
                'order_status' => 'Pending',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'coupon_code' => $couponCode,
            ]);

            foreach ($cartItems as $item) {
                if (!$item->product) continue;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'price' => $item->product->price,
                    'quantity' => $item->quantity,
                    'size' => $item->size,
                    'color' => $item->color,
                ]);

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

            if ($coupon) {
                $coupon->incrementUsage();
            }

            CartItem::where('user_id', $user->id)->delete();
            session()->forget('applied_coupon');
        });

        session()->flash('success', 'Thank you! Your order ' . $orderNumber . ' was successfully placed.');
        return response()->json([
            'success' => true,
            'redirect' => route('account'),
            'message' => 'Thank you! Your order ' . $orderNumber . ' was successfully placed.'
        ]);
    }
}
