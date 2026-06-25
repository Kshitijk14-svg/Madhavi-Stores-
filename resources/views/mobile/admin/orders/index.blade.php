@extends('admin.layout')
@section('admin_title', 'Orders')

@section('admin_content')
<div class="mb-4">
  <h2 class="font-display text-2xl text-primary">Customer Orders</h2>
  <p class="text-[11px] text-muted mt-0.5">Review purchases and update fulfillment.</p>
</div>

{{-- Filters --}}
<button type="button" onclick="document.getElementById('order-filters').classList.toggle('hidden')"
        class="w-full text-center text-[10px] font-bold tracking-widest uppercase text-primary border border-gray-300 py-3 mb-5">Filters</button>

<div id="order-filters" class="hidden mb-6">
  <form action="{{ route('admin.orders.index') }}" method="GET" class="bg-white border border-gray-200 p-4 space-y-3">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Order #, name, email..."
           class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2.5 outline-none">
    <select name="status" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2.5 outline-none">
      <option value="">All Order Statuses</option>
      @foreach(['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'] as $st)
        <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
      @endforeach
    </select>
    <select name="payment_status" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2.5 outline-none">
      <option value="">All Payment Statuses</option>
      @foreach(['Pending', 'Paid', 'Refunded'] as $pst)
        <option value="{{ $pst }}" {{ request('payment_status') === $pst ? 'selected' : '' }}>{{ $pst }}</option>
      @endforeach
    </select>
    <div class="flex items-center gap-2">
      <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min ₹"
             class="flex-1 text-xs text-primary bg-white border border-gray-200 px-3 py-2.5 outline-none">
      <span class="text-xs text-muted">-</span>
      <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max ₹"
             class="flex-1 text-xs text-primary bg-white border border-gray-200 px-3 py-2.5 outline-none">
    </div>
    <select name="sort_by" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2.5 outline-none">
      <option value="latest" {{ request('sort_by') === 'latest' || !request('sort_by') ? 'selected' : '' }}>Latest</option>
      <option value="oldest" {{ request('sort_by') === 'oldest' ? 'selected' : '' }}>Oldest</option>
      <option value="total_high_low" {{ request('sort_by') === 'total_high_low' ? 'selected' : '' }}>Total: High to Low</option>
      <option value="total_low_high" {{ request('sort_by') === 'total_low_high' ? 'selected' : '' }}>Total: Low to High</option>
    </select>
    <div class="flex gap-2">
      <button type="submit" class="flex-1 btn-primary !py-2.5 text-[10px] uppercase tracking-wider">Apply</button>
      <a href="{{ route('admin.orders.index') }}" class="flex-1 text-center text-[10px] uppercase tracking-wider border border-gray-200 text-muted py-2.5">Clear</a>
    </div>
  </form>
</div>

@if($orders->isEmpty())
  <div class="text-center py-12 border border-dashed border-gray-200 bg-white">
    <span class="text-2xl">✦</span>
    <p class="text-sm text-muted mt-2">No matching orders found.</p>
  </div>
@else
  {{-- Order cards --}}
  <div class="space-y-3">
    @foreach($orders as $order)
      <div class="border border-gray-100 bg-white p-4">
        <div class="flex items-start justify-between gap-2">
          <div>
            <p class="font-mono text-sm font-bold text-primary">{{ $order->order_number }}</p>
            <p class="text-[10px] text-muted mt-0.5">{{ $order->created_at->format('M d, Y · H:i') }}</p>
          </div>
          <p class="text-sm font-semibold text-primary">₹{{ number_format($order->total, 2) }}</p>
        </div>

        <div class="mt-2 text-xs">
          <p class="font-semibold text-primary">{{ $order->first_name }} {{ $order->last_name }}</p>
          <p class="text-[10px] text-muted truncate">{{ $order->email }}</p>
        </div>

        <div class="flex items-center flex-wrap gap-2 mt-3">
          <span class="text-[8px] px-2 py-0.5 font-bold uppercase tracking-wider
            {{ $order->payment_status === 'Paid' ? 'bg-emerald-50 text-emerald-700' : '' }}
            {{ $order->payment_status === 'Pending' ? 'bg-amber-50 text-amber-700' : '' }}
            {{ $order->payment_status === 'Refunded' ? 'bg-rose-50 text-rose-700' : '' }}">{{ $order->payment_status }}</span>
          <span class="text-[8px] px-2 py-0.5 font-bold uppercase tracking-wider
            {{ $order->order_status === 'Pending' ? 'bg-amber-50 text-amber-700' : '' }}
            {{ $order->order_status === 'Processing' ? 'bg-purple-50 text-purple-700' : '' }}
            {{ $order->order_status === 'Shipped' ? 'bg-indigo-50 text-indigo-700' : '' }}
            {{ $order->order_status === 'Delivered' ? 'bg-emerald-50 text-emerald-700' : '' }}
            {{ $order->order_status === 'Cancelled' ? 'bg-rose-50 text-rose-700' : '' }}">{{ $order->order_status }}</span>
          <span class="text-[10px] text-muted ml-auto">{{ $order->items->sum('quantity') }} items</span>
        </div>

        <button type="button" onclick="toggleOrderModal('{{ $order->id }}', true)"
                class="w-full mt-3 text-[10px] font-bold tracking-widest uppercase text-white bg-primary py-2.5">Manage</button>
      </div>

      {{-- Full-screen Manage sheet --}}
      <div id="order-modal-{{ $order->id }}" class="fixed inset-0 z-[1000] hidden bg-white flex flex-col">
        <div class="sticky top-0 bg-white border-b border-gray-100 flex items-center justify-between px-4" style="height:56px;padding-top:env(safe-area-inset-top);">
          <div>
            <span class="text-[9px] text-muted uppercase tracking-wider font-semibold">Order Detail</span>
            <h3 class="text-sm font-bold text-primary leading-none mt-0.5">{{ $order->order_number }}</h3>
          </div>
          <button type="button" onclick="toggleOrderModal('{{ $order->id }}', false)"
                  class="w-9 h-9 -mr-1 flex items-center justify-center text-muted text-lg">✕</button>
        </div>

        <div class="flex-1 overflow-y-auto px-4 py-5 space-y-6" style="padding-bottom:calc(env(safe-area-inset-bottom) + 32px);">
          {{-- Items --}}
          <div>
            <h4 class="text-[10px] font-bold tracking-widest text-primary uppercase mb-3">Purchased Pieces</h4>
            <div class="space-y-2">
              @foreach($order->items as $item)
                <div class="flex justify-between items-center border border-gray-100 p-3 bg-silk/10">
                  <div class="min-w-0">
                    <div class="font-semibold text-primary text-xs truncate">{{ $item->product_name }}</div>
                    <div class="text-[10px] text-muted mt-0.5">
                      Size: <span class="font-semibold text-primary">{{ $item->size ?? 'M' }}</span>
                      @if($item->color) · Color: <span class="font-semibold text-primary">{{ $item->color }}</span>@endif
                    </div>
                  </div>
                  <div class="text-right text-xs shrink-0 ml-2">
                    <div class="font-semibold text-primary">₹{{ number_format($item->price * $item->quantity, 2) }}</div>
                    <div class="text-[10px] text-muted mt-0.5">₹{{ number_format($item->price, 2) }} × {{ $item->quantity }}</div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>

          {{-- Totals --}}
          <div class="border-t border-gray-100 pt-4 space-y-2 text-xs">
            <div class="flex justify-between text-muted"><span>Subtotal</span><span class="text-primary font-medium">₹{{ number_format($order->subtotal, 2) }}</span></div>
            @if($order->discount > 0)
              <div class="flex justify-between text-rose-600"><span>Discount ({{ $order->coupon_code }})</span><span>- ₹{{ number_format($order->discount, 2) }}</span></div>
            @endif
            <div class="flex justify-between text-muted"><span>Tax</span><span class="text-primary font-medium">₹{{ number_format($order->tax, 2) }}</span></div>
            <div class="flex justify-between font-bold text-sm text-primary pt-2 border-t border-gray-100"><span>Total Bill</span><span>₹{{ number_format($order->total, 2) }}</span></div>
          </div>

          {{-- Shipping --}}
          <div>
            <h4 class="text-[10px] font-bold tracking-widest text-primary uppercase mb-2">Shipping Profile</h4>
            <div class="text-xs space-y-1 text-muted">
              <div class="font-semibold text-primary">{{ $order->first_name }} {{ $order->last_name }}</div>
              <div>{{ $order->email }}</div>
              <div class="pt-1.5 border-t border-gray-100 mt-1.5">{{ $order->address }}</div>
              <div>{{ $order->city }} — {{ $order->postal_code }}</div>
              <div class="pt-1.5 text-primary font-semibold">Method: {{ $order->payment_method }}</div>
            </div>
          </div>

          {{-- Status editor --}}
          <div class="bg-silk/20 border border-gray-100 p-4">
            <h4 class="text-[10px] font-bold tracking-widest text-primary uppercase mb-3">Update Status</h4>
            <form action="{{ route('admin.orders.update', $order->id) }}" method="POST" class="space-y-3">
              @csrf
              <div>
                <label class="text-[9px] font-semibold text-muted uppercase tracking-wider block mb-1">Order Status</label>
                <select name="order_status" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2.5 outline-none">
                  @foreach(['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'] as $st)
                    <option value="{{ $st }}" {{ $order->order_status === $st ? 'selected' : '' }}>{{ $st }}</option>
                  @endforeach
                </select>
              </div>
              <div>
                <label class="text-[9px] font-semibold text-muted uppercase tracking-wider block mb-1">Payment Status</label>
                <select name="payment_status" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2.5 outline-none">
                  @foreach(['Pending', 'Paid', 'Refunded'] as $pst)
                    <option value="{{ $pst }}" {{ $order->payment_status === $pst ? 'selected' : '' }}>{{ $pst }}</option>
                  @endforeach
                </select>
              </div>
              <button type="submit" class="w-full btn-primary !py-2.5 text-[10px] uppercase tracking-wider">Save Changes</button>
            </form>
          </div>

          {{-- Invoice + delete --}}
          <div class="space-y-2">
            <a href="{{ route('admin.orders.invoice', $order->id) }}" target="_blank"
               class="block w-full text-center py-2.5 text-[10px] text-primary bg-silk border border-gray-200 uppercase font-semibold tracking-wider">⬇ Download Invoice PDF</a>
            <form action="{{ route('admin.orders.send_invoice', $order->id) }}" method="POST" class="invoice-email-form">
              @csrf
              <button type="submit" class="w-full py-2.5 text-[10px] text-secondary bg-white border border-secondary uppercase font-semibold tracking-wider">✉ Email Invoice to Customer</button>
            </form>
            <form action="{{ route('admin.orders.delete', $order->id) }}" method="POST"
                  onsubmit="return confirm('Permanently delete this order record?')">
              @csrf
              <button type="submit" class="w-full py-2.5 text-[10px] text-red-600 bg-red-50 border border-red-200 uppercase font-semibold tracking-wider">Delete Order Record</button>
            </form>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  <div class="mt-6">
    {{ $orders->appends(request()->query())->links() }}
  </div>
@endif
@endsection

@section('scripts')
<script>
  function toggleOrderModal(orderId, show) {
    const modal = document.getElementById('order-modal-' + orderId);
    if (!modal) return;
    modal.classList.toggle('hidden', !show);
    document.body.style.overflow = show ? 'hidden' : '';
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.invoice-email-form').forEach(form => {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        const btn = form.querySelector('button');
        const original = btn.innerHTML;
        btn.innerHTML = 'Sending...';
        btn.disabled = true;
        fetch(form.action, {
          method: 'POST',
          body: new FormData(form),
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        })
          .then(r => r.json())
          .then(d => showToast(d.message || 'Invoice emailed.', 'success'))
          .catch(() => showToast('Error sending invoice.', 'error'))
          .finally(() => { btn.innerHTML = original; btn.disabled = false; });
      });
    });
  });
</script>
@endsection
