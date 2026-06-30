@extends('mobile.layouts.app')
@section('title', 'My Account — Madhavi Stores')

@section('content')
<div class="pb-24">

  {{-- Header --}}
  <div class="px-4 py-6 bg-primary text-white">
    <p class="text-[10px] font-bold tracking-widest uppercase text-white/40 mb-1">Welcome back</p>
    <h1 style="font-family:'Cormorant Garamond',serif;font-size:1.75rem;font-weight:300;">{{ $user->name }}</h1>
    <p class="text-xs text-white/50 mt-1">{{ $user->email }}</p>
  </div>

  {{-- Tab Nav --}}
  <div class="flex overflow-x-auto hide-scrollbar border-b border-gray-100 bg-white sticky top-[60px] z-10" id="account-tabs">
    @foreach([['id' => 'orders', 'label' => 'Orders'], ['id' => 'wishlist', 'label' => 'Wishlist'], ['id' => 'profile', 'label' => 'Profile']] as $tab)
    <button type="button" onclick="switchTab('{{ $tab['id'] }}')"
            class="tab-btn shrink-0 px-5 py-3.5 text-[10px] font-bold tracking-wider uppercase border-b-2 transition-colors whitespace-nowrap"
            id="tab-btn-{{ $tab['id'] }}"
            style="min-height:44px;">{{ $tab['label'] }}</button>
    @endforeach
  </div>

  {{-- Orders Tab --}}
  <div id="tab-orders" class="tab-content px-4 py-4">

    {{-- Sort controls --}}
    <div class="flex items-center gap-2 mb-4 flex-wrap">
      <button onclick="applyOrderParam('order_sort','latest')"
              class="text-[10px] font-bold uppercase tracking-wider px-3 py-1.5 border transition-colors"
              style="{{ request('order_sort','latest')==='latest' ? 'background:var(--primary);color:#fff;border-color:var(--primary);' : 'background:#fff;color:var(--muted);border-color:#e5e5e5;' }}">
        Latest
      </button>
      <button onclick="applyOrderParam('order_sort','oldest')"
              class="text-[10px] font-bold uppercase tracking-wider px-3 py-1.5 border transition-colors"
              style="{{ request('order_sort')==='oldest' ? 'background:var(--primary);color:#fff;border-color:var(--primary);' : 'background:#fff;color:var(--muted);border-color:#e5e5e5;' }}">
        Oldest
      </button>
      <input type="month" value="{{ request('order_month','') }}"
             onchange="applyOrderParam('order_month', this.value)"
             class="text-[10px] border px-2 py-1.5 outline-none bg-white text-primary"
             style="{{ request('order_month') ? 'border-color:var(--secondary);' : 'border-color:#e5e5e5;' }}">
      @if(request('order_month'))
      <button onclick="applyOrderParam('order_month','')" class="text-[10px] text-gray-400 underline">Clear</button>
      @endif
    </div>

    @if($orders->isEmpty())
      <div class="py-10 text-center">
        <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24" class="mx-auto text-gray-200 mb-3"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
        <p class="text-sm text-gray-400">{{ request('order_month') ? 'No orders for this period.' : 'No orders yet.' }}</p>
        @if(!request('order_month'))
        <a href="{{ route('shop') }}" class="btn-primary mt-4 inline-block text-sm" style="padding:12px 28px;">Start Shopping</a>
        @endif
      </div>
    @else
      {{-- Accordion strips --}}
      <div class="border border-gray-100 overflow-hidden">
        @foreach($orders as $order)
        <div>
          {{-- Strip header --}}
          <button type="button"
                  onclick="openOrderSheet({{ $order->id }}, '{{ $order->order_number }}')"
                  class="w-full flex items-center gap-3 px-4 py-3 bg-white border-b border-gray-100 text-left">
            {{-- Thumbs --}}
            <div class="flex gap-1 shrink-0">
              @foreach($order->items->take(2) as $thumb)
                @if($thumb->product)
                <img src="{{ $thumb->product->image_url }}" alt=""
                     class="w-9 h-9 object-cover bg-gray-50 shrink-0"
                     style="width:36px;height:36px;object-fit:cover;flex-shrink:0;">
                @endif
              @endforeach
              @if($order->items->count() > 2)
              <div class="w-9 h-9 bg-gray-100 flex items-center justify-center text-[9px] font-bold text-gray-400"
                   style="width:36px;height:36px;flex-shrink:0;">+{{ $order->items->count()-2 }}</div>
              @endif
            </div>
            {{-- Meta --}}
            <div class="flex-1 min-w-0">
              <p class="text-xs font-bold text-primary font-mono">{{ $order->order_number }}</p>
              <p class="text-[10px] text-gray-400 mt-0.5">{{ $order->created_at->format('d M Y') }}</p>
            </div>
            {{-- Total + status stacked, chevron separate --}}
            <div class="shrink-0 flex flex-col items-end gap-1">
              <span class="text-xs font-bold" style="color:var(--secondary);">₹{{ number_format($order->total,0) }}</span>
              <span class="text-[9px] font-bold uppercase tracking-wide px-1.5 py-0.5
                @if($order->order_status==='Delivered') bg-green-50 text-green-700
                @elseif($order->order_status==='Cancelled') bg-red-50 text-red-600
                @elseif($order->order_status==='Shipped') bg-blue-50 text-blue-700
                @else bg-amber-50 text-amber-700 @endif">
                {{ $order->order_status }}
              </span>
            </div>
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                 class="shrink-0" style="color:#aaa;">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
          </button>

          {{-- Hidden data source for bottom sheet --}}
          <div id="order-data-{{ $order->id }}" hidden>

            {{-- Status + date --}}
            <div class="mb-4 flex items-center gap-2">
              <span class="text-[9px] font-bold uppercase tracking-wide px-2 py-1
                @if($order->order_status==='Delivered') bg-green-50 text-green-700
                @elseif($order->order_status==='Cancelled') bg-red-50 text-red-600
                @elseif($order->order_status==='Shipped') bg-blue-50 text-blue-700
                @else bg-amber-50 text-amber-700 @endif">
                {{ $order->order_status }}
              </span>
              <span class="text-[10px] text-gray-400">{{ $order->created_at->format('d M Y') }}</span>
            </div>

            {{-- Items --}}
            <p class="text-[9px] font-bold uppercase tracking-widest text-gray-400 mb-2">Items Ordered</p>
            <div class="space-y-2 mb-4">
              @foreach($order->items as $item)
              <div class="flex items-center gap-3 bg-gray-50 border border-gray-100 p-2.5">
                @if($item->product)
                  <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}"
                       class="w-12 h-12 object-cover bg-gray-100 shrink-0"
                       style="width:48px;height:48px;object-fit:cover;flex-shrink:0;">
                @else
                  <div class="w-12 h-12 bg-gray-100 shrink-0" style="width:48px;height:48px;flex-shrink:0;"></div>
                @endif
                <div class="flex-1 min-w-0">
                  @if($item->product)
                    <a href="{{ route('product.show', $item->product->slug) }}"
                       class="text-xs font-semibold text-primary block truncate">{{ $item->product_name }}</a>
                  @else
                    <p class="text-xs font-semibold text-primary truncate">{{ $item->product_name }}</p>
                  @endif
                  <p class="text-[10px] text-gray-400 mt-0.5">
                    @if($item->size)Size: <strong class="text-primary">{{ $item->size }}</strong> · @endif
                    Qty: <strong class="text-primary">{{ $item->quantity }}</strong>
                  </p>
                </div>
                <p class="text-xs font-bold text-primary shrink-0">₹{{ number_format($item->price * $item->quantity, 0) }}</p>
              </div>
              @endforeach
            </div>

            {{-- Totals --}}
            <div class="bg-gray-50 border border-gray-100 p-3 mb-4 space-y-1.5">
              <div class="flex justify-between text-[11px] text-gray-400">
                <span>Subtotal</span><span>₹{{ number_format($order->subtotal,0) }}</span>
              </div>
              @if($order->discount > 0)
              <div class="flex justify-between text-[11px] text-red-500">
                <span>Discount{{ $order->coupon_code ? ' ('.$order->coupon_code.')' : '' }}</span>
                <span>−₹{{ number_format($order->discount,0) }}</span>
              </div>
              @endif
              <div class="flex justify-between text-xs font-bold text-primary pt-1.5 border-t border-gray-100">
                <span>Total</span><span style="color:var(--secondary);">₹{{ number_format($order->total,0) }}</span>
              </div>
            </div>

            {{-- Receipt buttons --}}
            <div class="flex gap-2">
              <a href="{{ route('account.order.receipt', $order->id) }}" target="_blank"
                 class="flex-1 flex items-center justify-center gap-1.5 py-2.5 text-[10px] font-bold uppercase tracking-wide border border-gray-200 bg-white text-primary">
                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                View
              </a>
              <a href="{{ route('account.order.receipt', $order->id) }}?download=1" target="_blank"
                 class="flex-1 flex items-center justify-center gap-1.5 py-2.5 text-[10px] font-bold uppercase tracking-wide text-white" style="background:var(--primary);">
                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Download PDF
              </a>
            </div>

          </div>
        </div>
        @endforeach
      </div>

      @if($orders->hasPages())
      <div class="mt-4">{{ $orders->links() }}</div>
      @endif

      {{-- Bottom sheet backdrop --}}
      <div id="order-sheet-backdrop"
           onclick="closeOrderSheet()"
           style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:60;"></div>

      {{-- Bottom sheet --}}
      <div id="order-sheet"
           style="display:none;flex-direction:column;position:fixed;bottom:0;left:0;right:0;max-height:85vh;
                  background:#fff;z-index:61;
                  transform:translateY(100%);transition:transform 0.3s cubic-bezier(0.4,0,0.2,1);">
        <div style="flex-shrink:0;" class="flex items-center justify-between px-4 py-4 border-b border-gray-100 bg-white">
          <p id="order-sheet-title" class="text-xs font-bold font-mono text-primary"></p>
          <button onclick="closeOrderSheet()"
                  class="w-8 h-8 flex items-center justify-center text-gray-400 text-xl leading-none"
                  style="font-size:1.4rem;line-height:1;">×</button>
        </div>
        <div id="order-sheet-body" class="px-4 py-4"
             style="flex:1;overflow-y:auto;overflow-x:hidden;-webkit-overflow-scrolling:touch;padding-bottom:max(16px,env(safe-area-inset-bottom));"></div>
      </div>
    @endif
  </div>

  {{-- Wishlist Tab --}}
  <div id="tab-wishlist" class="tab-content hidden px-4 py-4">
    @if($wishlist->isEmpty())
      <div class="py-12 text-center">
        <p class="text-sm text-gray-500 mb-4">Your wishlist is empty.</p>
        <a href="{{ route('shop') }}" class="btn-primary text-sm" style="padding:12px 28px;">Discover Products</a>
      </div>
    @else
    <div class="grid grid-cols-2 gap-3">
      @foreach($wishlist as $item)
        @if(!$item->product) @continue @endif
        <a href="{{ route('product.show', $item->product->slug) }}" class="border border-gray-100 overflow-hidden block">
          <div class="aspect-[3/4] bg-gray-50">
            <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
          </div>
          <div class="p-2">
            <p class="text-xs font-semibold leading-snug line-clamp-2 mb-1">{{ $item->product->name }}</p>
            <p class="text-xs text-secondary font-bold">₹{{ number_format($item->product->price, 0) }}</p>
          </div>
        </a>
      @endforeach
    </div>
    @endif
  </div>

  {{-- Profile Tab --}}
  <div id="tab-profile" class="tab-content hidden px-4 py-6">
    <form method="POST" action="{{ route('account.update') }}" class="space-y-4">
      @csrf
      <div>
        <label class="text-[10px] font-bold tracking-wider uppercase text-gray-500 block mb-1">Full Name</label>
        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
               class="w-full border border-gray-200 px-3 py-3 text-sm outline-none focus:border-primary transition-colors">
      </div>
      <div>
        <label class="text-[10px] font-bold tracking-wider uppercase text-gray-500 block mb-1">Email</label>
        <input type="email" value="{{ $user->email }}" disabled
               class="w-full border border-gray-100 px-3 py-3 text-sm bg-gray-50 text-gray-400 cursor-not-allowed">
      </div>
      <button type="submit" class="w-full btn-primary py-3.5 text-xs font-bold tracking-widest uppercase" style="min-height:48px;">Save Changes</button>
    </form>

    <div class="mt-8 pt-6 border-t border-gray-100 space-y-3">
      @if($user->isAdmin())
        <a href="{{ route('admin.dashboard') }}"
           class="block w-full text-center text-xs font-bold tracking-widest uppercase text-white bg-primary py-3.5"
           style="min-height:48px;display:flex;align-items:center;justify-content:center;">Admin Dashboard</a>
      @endif
      <a href="{{ route('logout') }}"
         onclick="event.preventDefault();document.getElementById('logout-form').submit();"
         class="block w-full text-center text-xs font-bold tracking-wider uppercase text-red-500 border border-red-100 py-3.5"
         style="min-height:48px;display:flex;align-items:center;justify-content:center;">Sign Out</a>
      <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
    </div>
  </div>

</div>

@section('scripts')
<script>
  function switchTab(id) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(btn => {
      btn.style.borderBottomColor = 'transparent';
      btn.style.color = 'var(--muted, #888)';
    });
    document.getElementById('tab-' + id).classList.remove('hidden');
    const activeBtn = document.getElementById('tab-btn-' + id);
    activeBtn.style.borderBottomColor = 'var(--secondary)';
    activeBtn.style.color = 'var(--primary)';
  }
  document.addEventListener('DOMContentLoaded', () => switchTab('orders'));
  document.addEventListener('pjax:success', () => switchTab('orders'));

  function openOrderSheet(orderId, orderNum) {
    const data = document.getElementById('order-data-' + orderId);
    if (!data) return;
    document.getElementById('order-sheet-body').innerHTML = data.innerHTML;
    document.getElementById('order-sheet-title').textContent = orderNum;
    const sheet    = document.getElementById('order-sheet');
    const backdrop = document.getElementById('order-sheet-backdrop');
    sheet.style.display    = 'flex';
    backdrop.style.display = 'block';
    document.body.style.overflow = 'hidden';
    requestAnimationFrame(() => { sheet.style.transform = 'translateY(0)'; });
  }

  function closeOrderSheet() {
    const sheet    = document.getElementById('order-sheet');
    const backdrop = document.getElementById('order-sheet-backdrop');
    if (!sheet) return;
    sheet.style.transform = 'translateY(100%)';
    document.body.style.overflow = '';
    setTimeout(() => {
      sheet.style.display    = 'none';
      backdrop.style.display = 'none';
      document.getElementById('order-sheet-body').innerHTML = '';
    }, 310);
  }

  document.addEventListener('pjax:success', function() {
    document.body.style.overflow = '';
    const s = document.getElementById('order-sheet');
    const b = document.getElementById('order-sheet-backdrop');
    if (s) { s.style.display = 'none'; s.style.transform = 'translateY(100%)'; }
    if (b) { b.style.display = 'none'; }
  });

  function applyOrderParam(key, value) {
    const url = new URL(window.location.href);
    if (value) url.searchParams.set(key, value);
    else        url.searchParams.delete(key);
    url.searchParams.delete('page');
    window.location.href = url.toString();
  }
</script>
@endsection
@endsection
