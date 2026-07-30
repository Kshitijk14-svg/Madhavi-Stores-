<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Refund {{ $refundSucceeded ? 'Processed' : 'Failed' }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #faf8f5; color: #181818; line-height: 1.6; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; padding: 40px; }
        h1 { font-family: 'Georgia', serif; font-weight: normal; font-size: 22px; margin-bottom: 8px; }
        .banner { padding: 14px 20px; margin-bottom: 24px; font-size: 13px; font-weight: 600; }
        .banner-ok { background: #ecfdf5; color: #059669; }
        .banner-action { background: #fef2f2; color: #dc2626; }
        p { font-size: 14px; margin-bottom: 12px; color: #444; }
        .details-box { background: #faf8f5; padding: 20px; margin: 20px 0; }
        .details-box p { margin: 0 0 8px 0; font-size: 13px; }
        .details-box p:last-child { margin-bottom: 0; }
        .cta { display: inline-block; background: #181818; color: #ffffff; text-decoration: none; font-size: 11px; letter-spacing: 0.12em; text-transform: uppercase; padding: 12px 24px; margin-top: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Madhavi Stores — Admin Alert</h1>

        @if($refundSucceeded)
            <div class="banner banner-ok">A customer's payment was automatically refunded — no action required.</div>
        @else
            <div class="banner banner-action">Automatic refund FAILED — this payment needs to be refunded manually in the Razorpay Dashboard.</div>
        @endif

        <p>{{ $reason }}</p>

        <div class="details-box">
            <p><strong>Order Number:</strong> #{{ $order->order_number }}</p>
            <p><strong>Customer:</strong> {{ $order->first_name }} {{ $order->last_name }} ({{ $order->email }})</p>
            <p><strong>Amount:</strong> ₹{{ number_format($order->total, 2) }}</p>
            <p><strong>Razorpay Payment ID:</strong> {{ $order->razorpay_payment_id }}</p>
            <p><strong>Order Status:</strong> {{ $order->order_status }} / Payment: {{ $order->payment_status }}</p>
        </div>

        <a href="{{ route('admin.orders.index', ['search' => $order->order_number]) }}" class="cta">View Order in Admin</a>
    </div>
</body>
</html>
