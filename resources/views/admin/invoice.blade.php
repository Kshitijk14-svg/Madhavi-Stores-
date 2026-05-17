<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Invoice {{ $order->order_number }} — Madhavi Stores</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 12px;
        color: #1a1a1a;
        background: #fff;
        line-height: 1.6;
    }
    .page { padding: 40px; max-width: 800px; margin: 0 auto; }

    /* Header */
    .header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        border-bottom: 2px solid #1a1a1a;
        padding-bottom: 24px;
        margin-bottom: 32px;
    }
    .brand-name {
        font-size: 28px;
        font-weight: 700;
        letter-spacing: 4px;
        text-transform: uppercase;
        color: #1a1a1a;
    }
    .brand-tagline {
        font-size: 9px;
        letter-spacing: 3px;
        text-transform: uppercase;
        color: #888;
        margin-top: 4px;
    }
    .invoice-meta { text-align: right; }
    .invoice-meta .label {
        font-size: 9px;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: #888;
    }
    .invoice-meta .invoice-number {
        font-size: 18px;
        font-weight: 700;
        color: #1a1a1a;
        margin-top: 2px;
    }
    .invoice-meta .date { font-size: 11px; color: #555; margin-top: 4px; }

    /* Addresses */
    .addresses {
        display: flex;
        justify-content: space-between;
        margin-bottom: 32px;
        gap: 20px;
    }
    .address-block { flex: 1; }
    .address-block .section-label {
        font-size: 8px;
        font-weight: 700;
        letter-spacing: 3px;
        text-transform: uppercase;
        color: #888;
        border-bottom: 1px solid #e5e5e5;
        padding-bottom: 6px;
        margin-bottom: 10px;
    }
    .address-block .name { font-weight: 700; font-size: 13px; }
    .address-block p { color: #555; margin-top: 2px; }

    /* Items Table */
    .items-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
    .items-table thead tr {
        background: #f5f3ef;
        border-top: 1px solid #e5e5e5;
        border-bottom: 1px solid #e5e5e5;
    }
    .items-table th {
        padding: 10px 12px;
        font-size: 8px;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: #888;
        text-align: left;
    }
    .items-table th:last-child, .items-table td:last-child { text-align: right; }
    .items-table td {
        padding: 12px;
        border-bottom: 1px solid #f0f0f0;
        font-size: 11px;
    }
    .items-table .product-name { font-weight: 600; color: #1a1a1a; }
    .items-table .product-meta { font-size: 9px; color: #888; margin-top: 2px; }

    /* Totals */
    .totals-block {
        margin-left: auto;
        width: 260px;
        margin-bottom: 32px;
    }
    .totals-row {
        display: flex;
        justify-content: space-between;
        padding: 6px 0;
        font-size: 11px;
        color: #555;
        border-bottom: 1px solid #f0f0f0;
    }
    .totals-row.discount { color: #c0392b; }
    .totals-row.grand-total {
        font-size: 14px;
        font-weight: 700;
        color: #1a1a1a;
        border-top: 2px solid #1a1a1a;
        border-bottom: none;
        padding-top: 10px;
        margin-top: 4px;
    }

    /* Status badges */
    .badge {
        display: inline-block;
        padding: 3px 8px;
        font-size: 8px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        border-radius: 2px;
    }
    .badge-paid    { background: #d1fae5; color: #065f46; }
    .badge-pending { background: #fef3c7; color: #92400e; }
    .badge-refunded{ background: #fee2e2; color: #991b1b; }

    /* Payment Info */
    .payment-row {
        display: flex;
        gap: 40px;
        border-top: 1px solid #e5e5e5;
        padding-top: 16px;
        margin-bottom: 32px;
    }
    .payment-row .pfield label {
        font-size: 8px;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: #888;
        display: block;
        margin-bottom: 4px;
    }
    .payment-row .pfield span { font-size: 12px; font-weight: 600; color: #1a1a1a; }

    /* Footer */
    .footer {
        border-top: 1px solid #e5e5e5;
        padding-top: 16px;
        text-align: center;
        font-size: 9px;
        color: #aaa;
        letter-spacing: 1px;
    }
    .footer strong { color: #555; }
</style>
</head>
<body>
<div class="page">

    {{-- HEADER --}}
    <div class="header">
        <div>
            <div class="brand-name">Madhavi Stores</div>
            <div class="brand-tagline">Luxury Indian Fashion</div>
        </div>
        <div class="invoice-meta">
            <div class="label">Tax Invoice</div>
            <div class="invoice-number">#{{ $order->order_number }}</div>
            <div class="date">{{ $order->created_at->setTimezone('Asia/Kolkata')->format('d M Y, h:i A') }} IST</div>
        </div>
    </div>

    {{-- ADDRESSES --}}
    <div class="addresses">
        <div class="address-block">
            <div class="section-label">Sold By</div>
            <p class="name">Madhavi Stores</p>
            <p>India</p>
            <p>support@madhavistores.com</p>
        </div>
        <div class="address-block">
            <div class="section-label">Shipped To</div>
            <p class="name">{{ $order->first_name }} {{ $order->last_name }}</p>
            <p>{{ $order->email }}</p>
            <p>{{ $order->address }}</p>
            <p>{{ $order->city }}, {{ $order->postal_code }}</p>
        </div>
    </div>

    {{-- PAYMENT INFO --}}
    <div class="payment-row">
        <div class="pfield">
            <label>Payment Method</label>
            <span>{{ $order->payment_method }}</span>
        </div>
        <div class="pfield">
            <label>Payment Status</label>
            <span class="badge
                @if($order->payment_status === 'Paid') badge-paid
                @elseif($order->payment_status === 'Refunded') badge-refunded
                @else badge-pending
                @endif">
                {{ $order->payment_status }}
            </span>
        </div>
        <div class="pfield">
            <label>Order Status</label>
            <span>{{ $order->order_status }}</span>
        </div>
        @if($order->coupon_code)
        <div class="pfield">
            <label>Coupon Applied</label>
            <span>{{ $order->coupon_code }}</span>
        </div>
        @endif
    </div>

    {{-- ITEMS TABLE --}}
    <table class="items-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Size</th>
                <th>Unit Price</th>
                <th>Qty</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>
                    <div class="product-name">{{ $item->product_name }}</div>
                    @if($item->color)
                    <div class="product-meta">Color: {{ $item->color }}</div>
                    @endif
                </td>
                <td>{{ $item->size ?? 'M' }}</td>
                <td>&#8377;{{ number_format($item->price, 2) }}</td>
                <td>{{ $item->quantity }}</td>
                <td>&#8377;{{ number_format($item->price * $item->quantity, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- TOTALS --}}
    <div class="totals-block">
        <div class="totals-row">
            <span>Subtotal</span>
            <span>&#8377;{{ number_format($order->subtotal, 2) }}</span>
        </div>
        @if($order->discount > 0)
        <div class="totals-row discount">
            <span>Discount ({{ $order->coupon_code }})</span>
            <span>- &#8377;{{ number_format($order->discount, 2) }}</span>
        </div>
        @endif
        <div class="totals-row">
            <span>GST (18%)</span>
            <span>&#8377;{{ number_format($order->tax, 2) }}</span>
        </div>
        <div class="totals-row grand-total">
            <span>Grand Total</span>
            <span>&#8377;{{ number_format($order->total, 2) }}</span>
        </div>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        <p>Thank you for shopping with <strong>Madhavi Stores</strong>. For queries, email support@madhavistores.com</p>
        <p style="margin-top:6px;">This is a computer-generated invoice and does not require a physical signature.</p>
    </div>

</div>
</body>
</html>
