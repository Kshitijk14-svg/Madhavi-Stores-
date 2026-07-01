@extends('mobile.layouts.app')
@section('title', 'Checkout — Madhavi Stores')

@section('content')
<div class="pb-32">

  {{-- Header --}}
  <div class="px-4 py-5 border-b border-gray-100">
    <h1 style="font-family:'Cormorant Garamond',serif;font-size:1.75rem;font-weight:300;">Checkout</h1>
  </div>

  {{-- Order Summary (collapsible) --}}
  <details class="border-b border-gray-100">
    <summary class="flex items-center justify-between px-4 py-3.5 cursor-pointer list-none" style="min-height:52px;">
      <span class="text-xs font-bold tracking-wider uppercase">Order Summary ({{ $cartItems->count() }} items)</span>
      <span class="text-sm font-bold text-secondary">₹{{ number_format($total, 0) }}</span>
    </summary>
    <div class="px-4 pb-4 divide-y divide-gray-50">
      @foreach($cartItems as $item)
        @if(!$item->product) @continue @endif
        <div class="flex items-center gap-3 py-2.5">
          <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}" class="w-12 h-14 object-cover bg-gray-50 shrink-0">
          <div class="flex-1 min-w-0">
            <p class="text-xs font-semibold text-primary line-clamp-1">{{ $item->product->name }}</p>
            @if($item->size)<p class="text-[10px] text-gray-400">Size: {{ $item->size }}</p>@endif
            <p class="text-[10px] text-gray-400">Qty: {{ $item->quantity }}</p>
          </div>
          <p class="text-xs font-bold text-secondary shrink-0">₹{{ number_format($item->product->final_price * $item->quantity, 0) }}</p>
        </div>
      @endforeach
      @if($discount > 0)
      <div class="flex justify-between py-2 text-xs">
        <span class="text-green-600">Discount</span>
        <span class="text-green-600 font-bold">−₹{{ number_format($discount, 0) }}</span>
      </div>
      @endif
      <div class="flex justify-between py-2.5 text-sm font-bold">
        <span>Total</span>
        <span class="text-secondary">₹{{ number_format($total, 0) }}</span>
      </div>
    </div>
  </details>

  {{-- Checkout Form --}}
  <form id="checkout-form" class="px-4 py-6 space-y-4">
    @csrf
    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="text-[10px] font-bold tracking-wider uppercase text-gray-500 block mb-1">First Name *</label>
        <input type="text" name="first_name" required class="w-full border border-gray-200 px-3 py-3 text-sm outline-none focus:border-primary">
      </div>
      <div>
        <label class="text-[10px] font-bold tracking-wider uppercase text-gray-500 block mb-1">Last Name *</label>
        <input type="text" name="last_name" required class="w-full border border-gray-200 px-3 py-3 text-sm outline-none focus:border-primary">
      </div>
    </div>
    <div>
      <label class="text-[10px] font-bold tracking-wider uppercase text-gray-500 block mb-1">Email *</label>
      <input type="email" name="email" required class="w-full border border-gray-200 px-3 py-3 text-sm outline-none focus:border-primary">
    </div>
    <div>
      <label class="text-[10px] font-bold tracking-wider uppercase text-gray-500 block mb-1">Mobile Number *</label>
      <div class="flex">
        <span class="flex items-center px-3 border border-r-0 border-gray-200 text-sm text-gray-500">+91</span>
        <input type="tel" name="phone" placeholder="10-digit mobile number" inputmode="numeric" maxlength="10" pattern="[6-9][0-9]{9}" title="Enter a valid 10-digit Indian mobile number" required class="w-full border border-gray-200 px-3 py-3 text-sm outline-none focus:border-primary">
      </div>
    </div>
    <div>
      <label class="text-[10px] font-bold tracking-wider uppercase text-gray-500 block mb-1">Address *</label>
      <textarea name="address" required rows="2" class="w-full border border-gray-200 px-3 py-3 text-sm outline-none focus:border-primary resize-none"></textarea>
    </div>
    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="text-[10px] font-bold tracking-wider uppercase text-gray-500 block mb-1">City *</label>
        <input type="text" name="city" required class="w-full border border-gray-200 px-3 py-3 text-sm outline-none focus:border-primary">
      </div>
      <div>
        <label class="text-[10px] font-bold tracking-wider uppercase text-gray-500 block mb-1">Postal Code *</label>
        <input type="text" name="postal_code" required class="w-full border border-gray-200 px-3 py-3 text-sm outline-none focus:border-primary">
      </div>
    </div>

    {{-- Payment --}}
    <div class="border border-gray-200 px-4 py-3.5 bg-gray-50">
      <p class="text-[11px] text-gray-500 leading-relaxed">
        You'll choose how to pay — Card, UPI, Net Banking or Wallet — in the secure Razorpay window after you place your order.
      </p>
    </div>

    <div id="checkout-error" class="text-sm text-red-500 hidden"></div>
  </form>

</div>

{{-- Sticky Pay Button --}}
<div class="fixed bottom-16 left-0 right-0 bg-white border-t border-gray-100 px-4 py-3 z-40" style="padding-bottom:env(safe-area-inset-bottom);">
  <button type="button" id="place-order-btn"
          onclick="placeOrder()"
          class="w-full btn-primary py-4 text-xs font-bold tracking-widest uppercase"
          style="min-height:52px;">
    Place Order — ₹{{ number_format($total, 0) }}
  </button>
</div>

@section('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js" defer></script>
<script>
const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

function placeOrder() {
  const form = document.getElementById('checkout-form');
  const data = new FormData(form);
  const btn = document.getElementById('place-order-btn');
  const errEl = document.getElementById('checkout-error');

  btn.disabled = true;
  btn.textContent = 'Processing…';
  errEl.classList.add('hidden');

  fetch('{{ route("checkout.store") }}', {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
    body: data
  })
  .then(r => r.json())
  .then(res => {
    if (!res.success) {
      errEl.textContent = res.message || 'Something went wrong.';
      errEl.classList.remove('hidden');
      btn.disabled = false;
      btn.textContent = 'Place Order — ₹{{ number_format($total, 0) }}';
      return;
    }

    if (res.is_mock) {
      if (res.redirect) window.location.href = res.redirect;
      return;
    }

    const options = {
      key: res.razorpay_key,
      amount: res.amount,
      currency: res.currency,
      name: res.company_name,
      order_id: res.razorpay_order_id,
      handler: function(payment) {
        const vData = new FormData(form);
        vData.append('razorpay_order_id', payment.razorpay_order_id);
        vData.append('razorpay_payment_id', payment.razorpay_payment_id);
        vData.append('razorpay_signature', payment.razorpay_signature);

        fetch('{{ route("checkout.verify") }}', {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
          body: vData
        }).then(r => r.json()).then(v => {
          if (v.redirect) window.location.href = v.redirect;
        });
      },
      prefill: { name: res.customer?.name, email: res.customer?.email, contact: res.customer?.phone },
      theme: { color: '#1a1a1a' }
    };
    new Razorpay(options).open();
    btn.disabled = false;
    btn.textContent = 'Place Order — ₹{{ number_format($total, 0) }}';
  })
  .catch(() => {
    errEl.textContent = 'Connection error. Please try again.';
    errEl.classList.remove('hidden');
    btn.disabled = false;
    btn.textContent = 'Place Order — ₹{{ number_format($total, 0) }}';
  });
}
</script>
@endsection
@endsection
