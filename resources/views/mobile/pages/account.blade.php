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
    @if($orders->isEmpty())
      <div class="py-12 text-center">
        <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24" class="mx-auto text-gray-200 mb-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
        <p class="text-sm text-gray-500">No orders yet.</p>
        <a href="{{ route('shop') }}" class="btn-primary mt-4 inline-block text-sm" style="padding:12px 28px;">Start Shopping</a>
      </div>
    @else
      <div class="space-y-3">
        @foreach($orders as $order)
        <div class="border border-gray-100 p-4" style="border-radius:2px;">
          <div class="flex items-start justify-between mb-3">
            <div>
              <p class="text-xs font-bold text-primary">{{ $order->order_number }}</p>
              <p class="text-[10px] text-gray-400 mt-0.5">{{ $order->created_at->format('d M Y') }}</p>
            </div>
            <div class="text-right">
              <span class="inline-block text-[9px] font-bold tracking-wider uppercase px-2 py-1
                @if($order->order_status === 'Delivered') bg-green-50 text-green-700
                @elseif($order->order_status === 'Cancelled') bg-red-50 text-red-600
                @else bg-amber-50 text-amber-700 @endif">
                {{ $order->order_status }}
              </span>
            </div>
          </div>
          <div class="flex gap-2 mb-3 overflow-x-auto hide-scrollbar pb-1">
            @foreach($order->items->take(4) as $item)
              @if($item->product)
              <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}"
                   class="w-12 h-12 object-cover shrink-0 bg-gray-50">
              @endif
            @endforeach
            @if($order->items->count() > 4)
              <div class="w-12 h-12 bg-gray-100 shrink-0 flex items-center justify-center text-[10px] text-gray-400 font-bold">
                +{{ $order->items->count() - 4 }}
              </div>
            @endif
          </div>
          <div class="flex items-center justify-between">
            <p class="text-xs font-bold text-secondary">₹{{ number_format($order->total, 0) }}</p>
            <span class="text-[10px] font-bold tracking-wider uppercase text-gray-400 px-2 py-1 border border-gray-100">{{ $order->payment_status }}</span>
          </div>
        </div>
        @endforeach
      </div>
      @if($orders->hasPages())
      <div class="mt-4">{{ $orders->links() }}</div>
      @endif
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

    <div class="mt-8 pt-6 border-t border-gray-100">
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
</script>
@endsection
@endsection
