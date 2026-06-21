@extends('mobile.layouts.app')
@section('title', 'Shopping Bag — Madhavi Stores')

@section('content')
<div class="pb-32">

  {{-- Header --}}
  <div class="px-4 py-5 border-b border-gray-100 flex items-center justify-between">
    <h1 style="font-family:'Cormorant Garamond',serif;font-size:1.75rem;font-weight:300;">Shopping Bag</h1>
    <span class="text-xs text-gray-400">{{ $cartCount }} items</span>
  </div>

  @if($cartItems->isEmpty())
    <div class="px-6 py-16 text-center">
      <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" class="mx-auto text-gray-200 mb-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
      <p class="text-sm text-gray-500 mb-6">Your bag is empty.</p>
      <a href="{{ route('shop') }}" class="btn-primary text-sm" style="padding:12px 28px;">Browse Products</a>
    </div>
  @else

  {{-- Cart Items --}}
  <div class="divide-y divide-gray-100" id="cart-items-list">
    @foreach($cartItems as $item)
    @if(!$item->product) @continue @endif
    <div class="flex gap-3 px-4 py-4" id="cart-item-{{ $item->id }}">
      <a href="{{ route('product.show', $item->product->slug) }}" class="shrink-0">
        <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}"
             class="w-20 h-24 object-cover bg-gray-50">
      </a>
      <div class="flex-1 min-w-0">
        <a href="{{ route('product.show', $item->product->slug) }}"
           class="text-xs font-semibold leading-snug text-primary line-clamp-2 block mb-1">{{ $item->product->name }}</a>
        @if($item->size)
          <p class="text-[10px] text-gray-400 mb-2">Size: {{ $item->size }}</p>
        @endif
        <p class="text-sm font-bold text-secondary mb-3">₹{{ number_format($item->product->price, 0) }}</p>
        <div class="flex items-center gap-3">
          <div class="flex items-center border border-gray-200">
            <button type="button"
                    onclick="updateCartItem({{ $item->id }}, {{ max(1, $item->quantity - 1) }})"
                    class="w-8 h-8 flex items-center justify-center text-primary hover:text-secondary transition-colors text-lg leading-none"
                    {{ $item->quantity <= 1 ? 'disabled' : '' }}>−</button>
            <span class="w-8 text-center text-xs font-bold" id="qty-{{ $item->id }}">{{ $item->quantity }}</span>
            <button type="button"
                    onclick="updateCartItem({{ $item->id }}, {{ $item->quantity + 1 }})"
                    class="w-8 h-8 flex items-center justify-center text-primary hover:text-secondary transition-colors text-lg leading-none">+</button>
          </div>
          <button type="button" onclick="removeCartItem({{ $item->id }})"
                  class="text-[10px] text-red-400 font-semibold tracking-wide">Remove</button>
        </div>
      </div>
    </div>
    @endforeach
  </div>

  {{-- Coupon --}}
  <div class="px-4 py-4 border-t border-gray-100">
    <div class="flex gap-2">
      <input type="text" id="coupon-input" placeholder="Promo code"
             value="{{ session('applied_coupon', '') }}"
             class="flex-1 border border-gray-200 px-3 py-3 text-sm outline-none focus:border-primary">
      <button type="button" onclick="applyCoupon()"
              class="px-4 text-[10px] font-bold tracking-widest uppercase bg-primary text-white"
              style="min-height:48px;">Apply</button>
    </div>
    <p id="coupon-msg" class="text-[11px] mt-1 {{ $coupon ? 'text-green-600' : 'text-red-500' }} hidden"></p>
    @if($coupon)
      <p class="text-[11px] text-green-600 mt-1">Coupon "{{ $coupon->code }}" applied — -₹{{ number_format($discount, 0) }}</p>
    @endif
  </div>

  {{-- Summary --}}
  <div class="px-4 py-4 border-t border-gray-100 space-y-2">
    <div class="flex justify-between text-sm">
      <span class="text-gray-500 font-light">Subtotal</span>
      <span class="font-semibold">₹{{ number_format($subtotal, 0) }}</span>
    </div>
    @if($discount > 0)
    <div class="flex justify-between text-sm">
      <span class="text-green-600 font-light">Discount</span>
      <span class="text-green-600 font-semibold">−₹{{ number_format($discount, 0) }}</span>
    </div>
    @endif
    <div class="flex justify-between text-sm font-bold border-t border-gray-100 pt-2 mt-2">
      <span>Total</span>
      <span class="text-secondary">₹{{ number_format($total, 0) }}</span>
    </div>
  </div>

  @endif
</div>

{{-- Sticky Checkout Button --}}
@if(!$cartItems->isEmpty())
<div class="fixed bottom-16 left-0 right-0 bg-white border-t border-gray-100 px-4 py-3 z-40" style="padding-bottom:env(safe-area-inset-bottom);">
  <a href="{{ route('checkout') }}" class="btn-primary block w-full text-center py-4 text-xs font-bold tracking-widest uppercase no-pjax"
     style="min-height:52px;display:flex;align-items:center;justify-content:center;">Proceed to Checkout</a>
</div>
@endif

@section('scripts')
<script>
const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

function updateCartItem(id, qty) {
  fetch('{{ route("cart.update") }}', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
    body: JSON.stringify({ cart_item_id: id, quantity: qty })
  }).then(r => r.json()).then(data => {
    if (data.success) {
      window.location.reload();
    } else {
      showToast(data.message || 'Could not update.', 'error');
    }
  }).catch(() => showToast('Connection error.', 'error'));
}

function removeCartItem(id) {
  fetch(`/cart/remove/${id}`, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' }
  }).then(r => r.json()).then(data => {
    if (data.success) {
      document.getElementById('cart-item-' + id)?.remove();
      showToast('Item removed.', 'success');
    }
  }).catch(() => showToast('Connection error.', 'error'));
}

function applyCoupon() {
  const code = document.getElementById('coupon-input')?.value?.trim();
  if (!code) return;
  fetch('{{ route("coupon.apply") }}', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
    body: JSON.stringify({ code })
  }).then(r => r.json()).then(data => {
    const msg = document.getElementById('coupon-msg');
    if (msg) {
      msg.textContent = data.message || '';
      msg.className = 'text-[11px] mt-1 ' + (data.success ? 'text-green-600' : 'text-red-500');
      msg.classList.remove('hidden');
    }
    if (data.success) window.location.reload();
  }).catch(() => {});
}
</script>
@endsection
@endsection
