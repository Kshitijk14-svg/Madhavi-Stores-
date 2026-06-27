@extends('layouts.app')
@section('title', 'My Account | Madhavi Stores')

@section('content')

<section style="padding: 64px 0; background: var(--white); min-height: 80vh;">
  <div class="wrap" style="max-width: 1000px; margin: 0 auto;">
    
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 40px; border-bottom: 1px solid var(--border); padding-bottom: 24px;">
      <div>
        <h1 style="font-family:'Cormorant Garamond',serif; font-size: 3rem; font-weight: 300; font-style: italic; color: var(--primary);">My Atelier</h1>
        <p style="color: var(--muted); margin-top: 8px;">Welcome back, {{ $user->name }}</p>
      </div>
      <div style="display: flex; gap: 16px;">
        @if($user->is_admin)
          <a href="{{ route('admin.dashboard') }}" class="btn-secondary" style="background: var(--primary); color: var(--white);">Admin Dashboard</a>
        @endif
        <form action="{{ route('logout') }}" method="POST">
          @csrf
          <button type="submit" class="btn-secondary">Sign Out</button>
        </form>
      </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 48px;">
      
      {{-- Sidebar Navigation --}}
      <div style="display: flex; flex-direction: column; gap: 16px;">
        <button class="account-tab active" onclick="switchTab('orders')" id="tab-orders">Order History</button>
        <button class="account-tab" onclick="switchTab('wishlist')" id="tab-wishlist">Wishlist</button>
        <button class="account-tab" onclick="switchTab('profile')" id="tab-profile">Profile Settings</button>
      </div>

      {{-- Tab Content --}}
      <div style="background: var(--silk); padding: 40px; border-radius: 8px;">
        
        {{-- ORDERS TAB --}}
        <div id="content-orders" class="account-content">
          <h2 style="font-family:'Cormorant Garamond',serif; font-size: 2rem; font-weight: 300; margin-bottom: 24px;">Order History</h2>
          @if($orders->isEmpty())
            <p style="color: var(--muted);">You haven't placed any orders yet.</p>
            <a href="{{ route('shop') }}" class="btn-primary" style="display: inline-block; margin-top: 16px;">Explore Collections</a>
          @else
            <div style="display:flex;flex-direction:column;gap:12px;">
              @foreach($orders as $order)
              <div style="border:1px solid var(--border);padding:20px;background:var(--white);">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
                  <div>
                    <p style="font-weight:700;font-family:monospace;color:var(--primary);font-size:13px;">{{ $order->order_number }}</p>
                    <p style="font-size:11px;color:var(--muted);margin-top:4px;">{{ $order->created_at->format('d M Y') }}</p>
                  </div>
                  <div style="display:flex;gap:8px;align-items:center;">
                    <span style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;padding:4px 10px;
                      @if($order->order_status === 'Delivered') background:#f0fdf4;color:#15803d;
                      @elseif($order->order_status === 'Cancelled') background:#fff1f2;color:#e11d48;
                      @else background:#fffbeb;color:#b45309; @endif">
                      {{ $order->order_status }}
                    </span>
                    <span style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;padding:4px 10px;border:1px solid var(--border);color:var(--muted);">{{ $order->payment_status }}</span>
                  </div>
                </div>
                <div style="display:flex;gap:8px;overflow-x:auto;padding-bottom:4px;margin-bottom:12px;">
                  @foreach($order->items->take(4) as $item)
                    @if($item->product)
                    <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}"
                         style="width:48px;height:48px;object-fit:cover;flex-shrink:0;background:#f5f5f5;">
                    @endif
                  @endforeach
                  @if($order->items->count() > 4)
                    <div style="width:48px;height:48px;background:#f5f5f5;display:flex;align-items:center;justify-content:center;font-size:11px;color:var(--muted);font-weight:700;flex-shrink:0;">+{{ $order->items->count() - 4 }}</div>
                  @endif
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;border-top:1px solid var(--border);padding-top:12px;">
                  <p style="font-weight:700;color:var(--secondary);">₹{{ number_format($order->total, 0) }}</p>
                  <p style="font-size:11px;color:var(--muted);">{{ $order->items->sum('quantity') }} {{ $order->items->sum('quantity') === 1 ? 'item' : 'items' }}</p>
                </div>
              </div>
              @endforeach
            </div>
            @if($orders->hasPages())
            <div style="margin-top:16px;">{{ $orders->links() }}</div>
            @endif
          @endif
        </div>

        {{-- WISHLIST TAB --}}
        <div id="content-wishlist" class="account-content" style="display: none;">
          <h2 style="font-family:'Cormorant Garamond',serif; font-size: 2rem; font-weight: 300; margin-bottom: 24px;">My Wishlist</h2>
          @if($wishlist->isEmpty())
            <p style="color: var(--muted);">Your wishlist is empty. Save your favorite pieces here.</p>
            <a href="{{ route('shop') }}" class="btn-primary" style="display: inline-block; margin-top: 16px;">Explore Collections</a>
          @else
            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;">
              @foreach($wishlist as $item)
                @if(!$item->product) @continue @endif
                <div style="border:1px solid var(--border);">
                  <a href="{{ route('product.show', $item->product->slug) }}" style="display:block;">
                    <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}"
                         style="width:100%;aspect-ratio:3/4;object-fit:cover;">
                  </a>
                  <div style="padding:10px;">
                    <a href="{{ route('product.show', $item->product->slug) }}" style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;color:var(--primary);">{{ $item->product->name }}</a>
                    <p style="font-size:12px;color:var(--secondary);font-weight:700;margin-bottom:8px;">₹{{ number_format($item->product->price, 0) }}</p>
                    <form action="{{ route('wishlist.toggle', $item->product->id) }}" method="POST">
                      @csrf
                      <button type="submit" style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;border:1px solid var(--border);padding:8px;width:100%;background:transparent;cursor:pointer;color:var(--muted);">Remove</button>
                    </form>
                  </div>
                </div>
              @endforeach
            </div>
          @endif
        </div>

        {{-- PROFILE TAB --}}
        <div id="content-profile" class="account-content" style="display: none;">
          <h2 style="font-family:'Cormorant Garamond',serif; font-size: 2rem; font-weight: 300; margin-bottom: 24px;">Profile Settings</h2>
          <form action="{{ route('account.update') }}" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
            @csrf
            <div>
              <label style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--muted);">Full Name</label>
              <input type="text" name="name" value="{{ $user->name }}" style="width: 100%; padding: 12px 16px; border: 1px solid var(--border); background: var(--white); margin-top: 8px;">
            </div>
            <div>
              <label style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--muted);">Email Address</label>
              <input type="email" name="email" value="{{ $user->email }}" disabled style="width: 100%; padding: 12px 16px; border: 1px solid var(--border); background: #f9f9f9; color: var(--muted); margin-top: 8px;">
            </div>
            <div>
              <button type="submit" class="btn-primary">Save Changes</button>
            </div>
          </form>
        </div>

      </div>
    </div>
  </div>
</section>

<style>
  .account-tab {
    background: none;
    border: none;
    text-align: left;
    padding: 16px 24px;
    font-size: 14px;
    font-weight: 500;
    color: var(--muted);
    cursor: pointer;
    transition: all 0.3s ease;
    border-left: 2px solid transparent;
  }
  .account-tab:hover {
    color: var(--primary);
    background: var(--silk);
  }
  .account-tab.active {
    color: var(--primary);
    border-left: 2px solid var(--secondary);
    font-weight: 600;
  }
</style>

<script>
  function switchTab(tabId) {
    document.querySelectorAll('.account-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.account-tab').forEach(el => el.classList.remove('active'));
    
    document.getElementById('content-' + tabId).style.display = 'block';
    document.getElementById('tab-' + tabId).classList.add('active');
  }
</script>

@endsection
