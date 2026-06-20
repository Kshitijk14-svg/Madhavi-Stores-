<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Your Invoice</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #faf8f5; color: #181818; line-height: 1.6; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        h1 { font-family: 'Georgia', serif; font-weight: normal; font-size: 24px; text-align: center; color: #181818; margin-bottom: 30px; }
        p { font-size: 15px; margin-bottom: 20px; }
        .details { background: #faf8f5; padding: 20px; border-radius: 6px; margin-bottom: 30px; }
        .details p { margin: 0 0 10px 0; font-size: 14px; }
        .footer { text-align: center; font-size: 12px; color: #888888; margin-top: 40px; border-top: 1px solid #e5e5e5; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Madhavi Stores</h1>
        <p>Dear {{ $order->user->name ?? 'Customer' }},</p>
        <p>Thank you for shopping with us! Please find attached the invoice for your recent order.</p>
        
        <div class="details">
            <p><strong>Order Number:</strong> #{{ $order->order_number }}</p>
            <p><strong>Order Date:</strong> {{ $order->created_at->format('d M, Y') }}</p>
            <p><strong>Total Amount:</strong> ₹{{ number_format($order->total, 2) }}</p>
        </div>

        <p>If you have any questions regarding this invoice, simply reply to this email or contact our support team.</p>
        
        <div class="footer">
            &copy; {{ date('Y') }} Madhavi Stores. All rights reserved.
        </div>
    </div>
</body>
</html>
