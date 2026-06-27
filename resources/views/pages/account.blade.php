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
          {{-- Header + Sort Controls --}}
          <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
            <h2 style="font-family:'Cormorant Garamond',serif; font-size: 2rem; font-weight: 300;">Order History</h2>
            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;padding-top:6px;">
              <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--muted);">Sort:</span>
              <button onclick="applyOrderParam('order_sort','latest')"
                      style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;padding:5px 12px;border:1px solid var(--border);cursor:pointer;transition:all 0.2s;
                             {{ request('order_sort','latest')==='latest' ? 'background:var(--primary);color:var(--white);border-color:var(--primary);' : 'background:var(--white);color:var(--muted);' }}">
                Latest
              </button>
              <button onclick="applyOrderParam('order_sort','oldest')"
                      style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;padding:5px 12px;border:1px solid var(--border);cursor:pointer;transition:all 0.2s;
                             {{ request('order_sort')==='oldest' ? 'background:var(--primary);color:var(--white);border-color:var(--primary);' : 'background:var(--white);color:var(--muted);' }}">
                Oldest
              </button>
              <input type="month" id="order-month-input" value="{{ request('order_month','') }}"
                     onchange="applyOrderParam('order_month', this.value)"
                     title="Filter by month"
                     style="font-size:10px;border:1px solid var(--border);padding:5px 8px;background:var(--white);outline:none;color:var(--primary);cursor:pointer;{{ request('order_month') ? 'border-color:var(--secondary);' : '' }}">
              @if(request('order_month'))
              <button onclick="applyOrderParam('order_month','')"
                      style="font-size:10px;color:var(--muted);background:none;border:none;cursor:pointer;text-decoration:underline;padding:0;">Clear</button>
              @endif
            </div>
          </div>

          @if($orders->isEmpty())
            <p style="color: var(--muted);">
              {{ request('order_month') ? 'No orders found for this period.' : "You haven't placed any orders yet." }}
            </p>
            @if(!request('order_month'))
            <a href="{{ route('shop') }}" class="btn-primary" style="display: inline-block; margin-top: 16px;">Explore Collections</a>
            @endif
          @else
            {{-- Accordion strip list --}}
            <div style="border:1px solid var(--border);overflow:hidden;">
              @foreach($orders as $i => $order)
              <div>
                {{-- Strip header (clickable) --}}
                <button type="button"
                        onclick="toggleOrderStrip({{ $order->id }})"
                        style="width:100%;display:flex;align-items:center;gap:14px;padding:14px 18px;background:var(--white);border:none;border-bottom:1px solid var(--border);cursor:pointer;text-align:left;">
                  {{-- Tiny thumbnails --}}
                  <div style="display:flex;gap:3px;flex-shrink:0;">
                    @foreach($order->items->take(2) as $thumb)
                      @if($thumb->product)
                      <img src="{{ $thumb->product->image_url }}" alt=""
                           style="width:34px;height:34px;object-fit:cover;background:#f5f5f5;flex-shrink:0;">
                      @endif
                    @endforeach
                    @if($order->items->count() > 2)
                    <div style="width:34px;height:34px;background:#f0ebe3;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:700;color:var(--muted);">+{{ $order->items->count() - 2 }}</div>
                    @endif
                  </div>
                  {{-- Order meta --}}
                  <div style="flex:1;min-width:0;">
                    <p style="font-size:12px;font-weight:700;font-family:monospace;color:var(--primary);">{{ $order->order_number }}</p>
                    <p style="font-size:10px;color:var(--muted);margin-top:1px;">{{ $order->created_at->format('d M Y') }} · {{ $order->items->sum('quantity') }} {{ \Illuminate\Support\Str::plural('item', $order->items->sum('quantity')) }}</p>
                  </div>
                  {{-- Total + status + chevron --}}
                  <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">
                    <span style="font-size:13px;font-weight:700;color:var(--secondary);">₹{{ number_format($order->total, 0) }}</span>
                    <span style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;padding:3px 8px;
                      @if($order->order_status==='Delivered') background:#f0fdf4;color:#15803d;
                      @elseif($order->order_status==='Cancelled') background:#fff1f2;color:#e11d48;
                      @elseif($order->order_status==='Shipped') background:#eff6ff;color:#1d4ed8;
                      @else background:#fffbeb;color:#b45309; @endif">
                      {{ $order->order_status }}
                    </span>
                    <svg id="chevron-{{ $order->id }}" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0;transition:transform 0.25s;color:var(--muted);">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                  </div>
                </button>

                {{-- Expandable detail panel --}}
                <div id="order-body-{{ $order->id }}"
                     style="max-height:0;overflow:hidden;transition:max-height 0.35s ease;">
                  <div style="padding:20px;background:var(--silk);border-bottom:1px solid var(--border);">

                    {{-- Products list --}}
                    <p style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.12em;color:var(--muted);margin-bottom:10px;">Items Ordered</p>
                    <div style="display:flex;flex-direction:column;gap:6px;margin-bottom:16px;">
                      @foreach($order->items as $item)
                      <div style="display:flex;align-items:center;gap:12px;padding:10px 12px;background:var(--white);border:1px solid var(--border);">
                        @if($item->product)
                          <a href="{{ route('product.show', $item->product->slug) }}" class="no-pjax" style="flex-shrink:0;display:block;">
                            <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}"
                                 style="width:54px;height:54px;object-fit:cover;background:#f5f5f5;display:block;"
                                 onerror="this.style.background='#f0ebe3'">
                          </a>
                        @else
                          <div style="width:54px;height:54px;background:#f0ebe3;flex-shrink:0;"></div>
                        @endif
                        <div style="flex:1;min-width:0;">
                          @if($item->product)
                            <a href="{{ route('product.show', $item->product->slug) }}" class="no-pjax"
                               style="font-size:12px;font-weight:600;color:var(--primary);display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;text-decoration:none;">
                              {{ $item->product_name }}
                            </a>
                          @else
                            <p style="font-size:12px;font-weight:600;color:var(--primary);">{{ $item->product_name }}</p>
                          @endif
                          <p style="font-size:10px;color:var(--muted);margin-top:2px;">
                            @if($item->size)Size: <strong style="color:var(--primary);">{{ $item->size }}</strong> &nbsp;·&nbsp; @endif
                            Qty: <strong style="color:var(--primary);">{{ $item->quantity }}</strong>
                          </p>
                        </div>
                        <p style="font-size:12px;font-weight:700;color:var(--primary);flex-shrink:0;">₹{{ number_format($item->price * $item->quantity, 0) }}</p>
                      </div>
                      @endforeach
                    </div>

                    {{-- Order totals --}}
                    <div style="padding:12px 14px;background:var(--white);border:1px solid var(--border);margin-bottom:14px;display:flex;flex-direction:column;gap:4px;">
                      <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--muted);">
                        <span>Subtotal</span><span>₹{{ number_format($order->subtotal, 0) }}</span>
                      </div>
                      @if($order->discount > 0)
                      <div style="display:flex;justify-content:space-between;font-size:11px;color:#dc2626;">
                        <span>Discount{{ $order->coupon_code ? ' ('.$order->coupon_code.')' : '' }}</span>
                        <span>−₹{{ number_format($order->discount, 0) }}</span>
                      </div>
                      @endif
                      @if($order->tax > 0)
                      <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--muted);">
                        <span>Tax (18% GST)</span><span>₹{{ number_format($order->tax, 0) }}</span>
                      </div>
                      @endif
                      <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:700;color:var(--primary);border-top:1px solid var(--border);padding-top:8px;margin-top:2px;">
                        <span>Total Paid</span><span style="color:var(--secondary);">₹{{ number_format($order->total, 0) }}</span>
                      </div>
                    </div>

                    {{-- Receipt action buttons --}}
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                      <a href="{{ route('account.order.receipt', $order->id) }}"
                         target="_blank"
                         style="flex:1;min-width:130px;display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:10px 14px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.09em;border:1px solid var(--border);background:var(--white);color:var(--primary);text-decoration:none;white-space:nowrap;">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        View Receipt
                      </a>
                      <a href="{{ route('account.order.receipt', $order->id) }}?download=1"
                         target="_blank"
                         style="flex:1;min-width:130px;display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:10px 14px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.09em;background:var(--primary);color:var(--white);text-decoration:none;white-space:nowrap;">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        Download PDF
                      </a>
                    </div>

                  </div>
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

  function toggleOrderStrip(orderId) {
    const body    = document.getElementById('order-body-'  + orderId);
    const chevron = document.getElementById('chevron-'     + orderId);
    if (!body) return;
    const isOpen = body.style.maxHeight && body.style.maxHeight !== '0px';
    if (isOpen) {
      body.style.maxHeight    = '0';
      chevron.style.transform = 'rotate(0deg)';
    } else {
      body.style.maxHeight    = body.scrollHeight + 'px';
      chevron.style.transform = 'rotate(180deg)';
    }
  }

  function applyOrderParam(key, value) {
    const url = new URL(window.location.href);
    if (value) url.searchParams.set(key, value);
    else        url.searchParams.delete(key);
    url.searchParams.delete('page');
    if (typeof navigateToPage === 'function') navigateToPage(url.toString(), true);
    else window.location.href = url.toString();
  }
</script>

@endsection
