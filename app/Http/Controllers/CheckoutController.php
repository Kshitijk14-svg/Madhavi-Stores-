<?php

namespace App\Http\Controllers;

use App\Exceptions\CheckoutException;
use App\Models\Order;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Razorpay\Api\Api;

class CheckoutController extends Controller
{
    public function __construct(private CartService $cart)
    {
    }

    public function index()
    {
        $summary = $this->cart->getSummary(Auth::user());

        if ($summary['cartItems']->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Your shopping bag is empty.');
        }

        return view('pages.checkout', [
            'cartItems' => $summary['cartItems'],
            'subtotal'  => $summary['subtotal'],
            'discount'  => $summary['discount'],
            'tax'       => $summary['tax'],
            'total'     => $summary['total'],
            'coupon'    => $summary['coupon'],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name'     => 'required|string|max:100',
            'last_name'      => 'required|string|max:100',
            'email'          => 'required|email|max:255',
            'address'        => 'required|string',
            'city'           => 'required|string|max:100',
            'postal_code'    => 'required|string|max:20',
        ]);

        $user     = Auth::user();
        $summary  = $this->cart->getSummary($user);
        $customer = $request->only(['first_name', 'last_name', 'email', 'address', 'city', 'postal_code']);

        // The actual method (Card/UPI/NetBanking/Wallet) is chosen inside the
        // Razorpay modal; we record a single generic method server-side rather
        // than trusting a client-supplied value.
        $paymentMethod = 'Razorpay';

        if ($summary['cartItems']->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Your shopping bag is empty.'], 400);
        }

        // ── Razorpay: create a gateway order, the charge happens client-side ──
        $keyId     = config('razorpay.key_id');
        $keySecret = config('razorpay.key_secret');
        $total     = $summary['total'];

        if ($keyId === 'rzp_test_dummykey123' || empty($keyId) || empty($keySecret)) {
            if (!app()->isLocal()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment gateway is not configured. Please contact support.',
                ], 503);
            }

            // Local-only mock so the flow can be exercised without live keys.
            return response()->json([
                'success'           => true,
                'payment_method'    => $paymentMethod,
                'razorpay_order_id' => 'order_fake_' . Str::random(14),
                'razorpay_key'      => 'rzp_test_dummykey123',
                'amount'            => intval(round($total * 100)),
                'currency'          => 'INR',
                'company_name'      => 'Madhavi Stores',
                'is_mock'           => true,
                'customer'          => [
                    'name'  => $request->first_name . ' ' . $request->last_name,
                    'email' => $request->email,
                ],
            ]);
        }

        try {
            $api          = new Api($keyId, $keySecret);
            $razorpayOrder = $api->order->create([
                'receipt'         => 'rcpt_' . time() . '_' . $user->id,
                'amount'          => intval(round($total * 100)),
                'currency'        => 'INR',
                'payment_capture' => 1,
                // Carried back to us by the webhook so an order can be created even
                // if the customer closes the tab after paying.
                'notes'           => array_merge($customer, [
                    'user_id'        => (string) $user->id,
                    'payment_method' => $paymentMethod,
                    'coupon_code'    => $summary['couponCode'] ?? '',
                ]),
            ]);

            return response()->json([
                'success'           => true,
                'payment_method'    => $paymentMethod,
                'razorpay_order_id' => $razorpayOrder['id'],
                'razorpay_key'      => $keyId,
                'amount'            => intval(round($total * 100)),
                'currency'          => 'INR',
                'company_name'      => 'Madhavi Stores',
                'is_mock'           => false,
                'customer'          => [
                    'name'  => $request->first_name . ' ' . $request->last_name,
                    'email' => $request->email,
                ],
            ]);
        } catch (\Exception $e) {
            logger()->error('Razorpay order creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'We could not start the payment. Please try again or choose Cash on Delivery.',
            ], 500);
        }
    }

    public function verifyPayment(Request $request)
    {
        $request->validate([
            'first_name'          => 'required|string|max:100',
            'last_name'           => 'required|string|max:100',
            'email'               => 'required|email|max:255',
            'address'             => 'required|string',
            'city'                => 'required|string|max:100',
            'postal_code'         => 'required|string|max:20',
            'razorpay_order_id'   => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature'  => 'required|string',
        ]);

        $user      = Auth::user();
        $keyId     = config('razorpay.key_id');
        $keySecret = config('razorpay.key_secret');

        $isMock = app()->isLocal() && (
            $keyId === 'rzp_test_dummykey123' || empty($keyId) || empty($keySecret)
            || Str::startsWith($request->razorpay_order_id, 'order_fake_')
        );

        if (!$isMock) {
            try {
                $api = new Api($keyId, $keySecret);
                $api->utility->verifyPaymentSignature([
                    'razorpay_order_id'   => $request->razorpay_order_id,
                    'razorpay_payment_id' => $request->razorpay_payment_id,
                    'razorpay_signature'  => $request->razorpay_signature,
                ]);
            } catch (\Exception $e) {
                logger()->error('Razorpay signature verification failed: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Payment signature verification failed. Please contact support.',
                ], 400);
            }
        }

        $customer = $request->only(['first_name', 'last_name', 'email', 'address', 'city', 'postal_code']);

        try {
            $order = $this->cart->createOrder($user, $customer, 'Razorpay', [
                'razorpay_order_id'   => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature'  => $request->razorpay_signature,
                'payment_status'      => 'Paid',
            ]);
        } catch (CheckoutException $e) {
            // Payment captured but the order could not be created (e.g. stock gone).
            logger()->warning('Paid order could not be created: ' . $e->getMessage(), [
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'user_id'             => $user->id,
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() . ' Your payment will be refunded — please contact support with payment id ' . $request->razorpay_payment_id . '.',
            ], 422);
        } catch (\Throwable $e) {
            logger()->error('Paid order creation failed: ' . $e->getMessage(), [
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'user_id'             => $user->id,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Your payment was received but we hit a problem finalising the order. Please contact support with payment id ' . $request->razorpay_payment_id . '.',
            ], 500);
        }

        session()->flash('success', 'Thank you! Your order ' . $order->order_number . ' was successfully placed.');
        return response()->json([
            'success'  => true,
            'redirect' => route('account'),
            'message'  => 'Thank you! Your order ' . $order->order_number . ' was successfully placed.',
        ]);
    }

    /**
     * Server-to-server safety net. Razorpay calls this when a payment is captured;
     * if the customer closed the tab before verifyPayment ran, we still create the
     * order here. Idempotent via CartService (keyed on razorpay_payment_id).
     *
     * Configure the URL + secret in the Razorpay Dashboard and RAZORPAY_WEBHOOK_SECRET.
     */
    public function webhook(Request $request)
    {
        $secret = config('razorpay.webhook_secret');
        if (empty($secret)) {
            return response()->json(['status' => 'ignored'], 200);
        }

        $payload   = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature', '');

        try {
            (new Api(config('razorpay.key_id'), config('razorpay.key_secret')))
                ->utility->verifyWebhookSignature($payload, $signature, $secret);
        } catch (\Exception $e) {
            logger()->warning('Razorpay webhook signature invalid: ' . $e->getMessage());
            return response()->json(['status' => 'invalid signature'], 400);
        }

        $data  = $request->json()->all();
        $event = $data['event'] ?? '';

        if ($event !== 'payment.captured' && $event !== 'order.paid') {
            return response()->json(['status' => 'ignored'], 200);
        }

        $payment = $data['payload']['payment']['entity'] ?? [];
        $notes   = $payment['notes'] ?? [];
        $paymentId = $payment['id'] ?? null;

        if (!$paymentId || empty($notes['user_id'])) {
            return response()->json(['status' => 'ignored'], 200);
        }

        // Already created by the browser flow — nothing to do.
        if (Order::where('razorpay_payment_id', $paymentId)->exists()) {
            return response()->json(['status' => 'exists'], 200);
        }

        $user = User::find($notes['user_id']);
        if (!$user) {
            return response()->json(['status' => 'ignored'], 200);
        }

        $customer = [
            'first_name'  => $notes['first_name']  ?? '',
            'last_name'   => $notes['last_name']   ?? '',
            'email'       => $notes['email']       ?? $user->email,
            'address'     => $notes['address']     ?? '',
            'city'        => $notes['city']        ?? '',
            'postal_code' => $notes['postal_code'] ?? '',
        ];

        try {
            $this->cart->createOrder(
                $user,
                $customer,
                $notes['payment_method'] ?? 'Razorpay',
                [
                    'razorpay_order_id'   => $payment['order_id'] ?? null,
                    'razorpay_payment_id' => $paymentId,
                    'payment_status'      => 'Paid',
                ],
                $notes['coupon_code'] ?: null
            );
        } catch (CheckoutException $e) {
            // Cart was empty (already ordered) or stock gone — log, acknowledge.
            logger()->warning('Webhook order creation skipped: ' . $e->getMessage(), ['payment_id' => $paymentId]);
            return response()->json(['status' => 'skipped'], 200);
        } catch (\Throwable $e) {
            logger()->error('Webhook order creation failed: ' . $e->getMessage(), ['payment_id' => $paymentId]);
            return response()->json(['status' => 'error'], 500);
        }

        return response()->json(['status' => 'created'], 200);
    }
}
