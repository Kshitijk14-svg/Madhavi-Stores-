<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Status Update</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #faf8f5; color: #181818; line-height: 1.6; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; padding: 40px; }
        h1 { font-family: 'Georgia', serif; font-weight: normal; font-size: 24px; text-align: center; color: #181818; margin-bottom: 8px; }
        .brand-line { text-align: center; font-size: 11px; letter-spacing: 0.15em; text-transform: uppercase; color: #888; margin-bottom: 36px; }
        p { font-size: 14px; margin-bottom: 16px; color: #444; }
        .status-box { border: 1px solid #e5e5e5; padding: 24px; margin-bottom: 28px; text-align: center; }
        .status-label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.12em; color: #888; margin-bottom: 10px; }
        .status-arrow { font-size: 12px; color: #aaa; margin: 6px 0; }
        .status-value { font-size: 20px; font-weight: 700; letter-spacing: 0.05em; }
        .status-delivered { color: #059669; }
        .status-shipped { color: #4f46e5; }
        .status-processing { color: #7c3aed; }
        .status-cancelled { color: #dc2626; }
        .status-pending { color: #d97706; }
        .details-box { background: #faf8f5; padding: 20px; margin-bottom: 28px; }
        .details-box p { margin: 0 0 8px 0; font-size: 13px; }
        .details-box p:last-child { margin-bottom: 0; }
        .divider { border: none; border-top: 1px solid #e5e5e5; margin: 28px 0; }
        .cta { display: block; text-align: center; background: #181818; color: #ffffff; text-decoration: none; font-size: 11px; letter-spacing: 0.12em; text-transform: uppercase; padding: 14px 28px; margin: 24px 0; }
        .footer { text-align: center; font-size: 11px; color: #aaa; margin-top: 36px; padding-top: 20px; border-top: 1px solid #e5e5e5; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Madhavi Stores</h1>
        <div class="brand-line">Order Status Update</div>

        <p>Dear {{ $order->first_name ?? ($order->user->name ?? 'Valued Customer') }},</p>
        <p>Your order status has been updated. Here are the latest details for your reference.</p>

        <div class="status-box">
            <div class="status-label">Order Status</div>
            <div style="font-size:13px; color:#888;">{{ $previousStatus }}</div>
            <div class="status-arrow">↓</div>
            @php
                $cls = match($order->order_status) {
                    'Delivered'  => 'status-delivered',
                    'Shipped'    => 'status-shipped',
                    'Processing' => 'status-processing',
                    'Cancelled'  => 'status-cancelled',
                    default      => 'status-pending',
                };
            @endphp
            <div class="status-value {{ $cls }}">{{ $order->order_status }}</div>
        </div>

        @if($order->order_status === 'Processing')
            <p>We have received your order and our team is preparing it for dispatch. We will notify you again once it is shipped.</p>
        @elseif($order->order_status === 'Shipped')
            <p>Great news — your order is on its way! You can expect delivery within the estimated timeframe. Thank you for your patience.</p>
        @elseif($order->order_status === 'Delivered')
            <p>Your order has been delivered. We hope you love your new pieces from Madhavi Stores! If anything is amiss, please don't hesitate to reach out.</p>
        @elseif($order->order_status === 'Cancelled')
            <p>Your order has been cancelled. If this was unexpected or you need further assistance, please contact us and we will be happy to help.</p>
        @endif

        <div class="details-box">
            <p><strong>Order Number:</strong> #{{ $order->order_number }}</p>
            <p><strong>Order Date:</strong> {{ $order->created_at->format('d M, Y') }}</p>
            <p><strong>Total Amount:</strong> ₹{{ number_format($order->total, 2) }}</p>
            <p><strong>Payment Method:</strong> {{ $order->payment_method }}</p>
        </div>

        <a href="{{ url('/account') }}" class="cta">View Your Orders</a>

        <hr class="divider">
        <p style="font-size:13px;">If you have any questions about your order, simply reply to this email and we will get back to you shortly.</p>

        <div class="footer">
            &copy; {{ date('Y') }} Madhavi Stores. All rights reserved.<br>
            {{ $order->address ?? '' }}{{ $order->city ? ', ' . $order->city : '' }}
        </div>
    </div>
</body>
</html>
