@extends('admin.layout')

@section('admin_content')
<div>
    @if(session('success'))
        <div class="mb-6 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
            ✓ {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 px-4 py-3 bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold">
            ✕ {{ session('error') }}
        </div>
    @endif
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <h2 class="text-xl font-semibold text-primary">Customer Orders</h2>
            <p class="text-xs text-muted mt-1">Review purchases, update fulfillment progress, and process payment actions.</p>
        </div>
    </div>

    {{-- Search and Filter Form --}}
    <form action="{{ route('admin.orders.index') }}" method="GET" class="bg-white border border-gray-150 p-6 mb-8">
        <div class="flex flex-wrap gap-4 items-center justify-between">
            <div class="flex flex-wrap gap-3 items-center flex-1 max-w-5xl">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Search order #, client name, email..." 
                       class="text-xs text-primary bg-white border border-gray-200 px-4 py-2.5 outline-none w-full sm:w-64">

                <select name="status" class="text-xs text-primary bg-white border border-gray-200 px-4 py-2.5 outline-none">
                    <option value="">All Order Statuses</option>
                    @foreach(['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'] as $st)
                        <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>

                <select name="payment_status" class="text-xs text-primary bg-white border border-gray-200 px-4 py-2.5 outline-none">
                    <option value="">All Payment Statuses</option>
                    @foreach(['Pending', 'Paid', 'Refunded'] as $pst)
                        <option value="{{ $pst }}" {{ request('payment_status') === $pst ? 'selected' : '' }}>{{ $pst }}</option>
                    @endforeach
                </select>

                <div class="flex items-center gap-2">
                    <input type="number" 
                           name="min_price" 
                           value="{{ request('min_price') }}" 
                           placeholder="Min ₹" 
                           class="text-xs text-primary bg-white border border-gray-200 px-3 py-2.5 outline-none w-20">
                    <span class="text-xs text-muted">-</span>
                    <input type="number" 
                           name="max_price" 
                           value="{{ request('max_price') }}" 
                           placeholder="Max ₹" 
                           class="text-xs text-primary bg-white border border-gray-200 px-3 py-2.5 outline-none w-20">
                </div>

                <select name="sort_by" class="text-xs text-primary bg-white border border-gray-200 px-4 py-2.5 outline-none">
                    <option value="latest" {{ request('sort_by') === 'latest' || !request('sort_by') ? 'selected' : '' }}>Latest</option>
                    <option value="oldest" {{ request('sort_by') === 'oldest' ? 'selected' : '' }}>Oldest</option>
                    <option value="total_high_low" {{ request('sort_by') === 'total_high_low' ? 'selected' : '' }}>Total: High to Low</option>
                    <option value="total_low_high" {{ request('sort_by') === 'total_low_high' ? 'selected' : '' }}>Total: Low to High</option>
                </select>
            </div>
            
            <div class="flex items-center gap-2">
                <button type="submit" class="btn-primary !py-2.5 !px-5 text-[9px] tracking-[0.15em] font-semibold uppercase">
                    Apply Filters
                </button>
                <a href="{{ route('admin.orders.index') }}" class="btn-secondary !py-2.5 !px-5 text-[9px] tracking-[0.15em] font-semibold uppercase text-center">
                    Clear
                </a>
            </div>
        </div>
    </form>

    {{-- Order ledger --}}
    @if($orders->isEmpty())
        <div class="text-center py-16 border border-dashed border-gray-100 bg-silk/30">
            <span class="text-3xl">✦</span>
            <p class="text-sm text-muted mt-3">No matching order records found in the ledger.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs tracking-wide">
                <thead>
                    <tr class="border-b border-gray-100 text-[10px] font-bold text-primary uppercase bg-silk/40">
                        <th class="py-4 px-4">Order Code</th>
                        <th class="py-4 px-4">Purchased Date</th>
                        <th class="py-4 px-4">Client Name</th>
                        <th class="py-4 px-4">Items Count</th>
                        <th class="py-4 px-4">Total Amount</th>
                        <th class="py-4 px-4 text-center">Payment Status</th>
                        <th class="py-4 px-4 text-center">Order Status</th>
                        <th class="py-4 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100/60">
                    @foreach($orders as $order)
                        <tr class="hover:bg-silk/10 transition-colors">
                            <td class="py-4 px-4 font-mono font-semibold text-primary">
                                {{ $order->order_number }}
                            </td>
                            <td class="py-4 px-4 text-muted">
                                {{ $order->created_at->format('M d, Y · H:i') }}
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-semibold text-primary">{{ $order->first_name }} {{ $order->last_name }}</div>
                                <div class="text-[10px] text-muted">{{ $order->email }}</div>
                            </td>
                            <td class="py-4 px-4 text-primary font-semibold">
                                {{ $order->items->sum('quantity') }}
                            </td>
                            <td class="py-4 px-4 text-primary font-semibold">
                                ₹{{ number_format($order->total, 2) }}
                            </td>
                            
                            {{-- Payment Status Tag --}}
                            <td class="py-4 px-4 text-center">
                                <span class="inline-block px-2.5 py-1 text-[9px] font-bold uppercase tracking-wider
                                    {{ $order->payment_status === 'Paid' ? 'bg-emerald-50 text-emerald-700' : '' }}
                                    {{ $order->payment_status === 'Pending' ? 'bg-amber-50 text-amber-700' : '' }}
                                    {{ $order->payment_status === 'Refunded' ? 'bg-rose-50 text-rose-700' : '' }}
                                ">
                                    {{ $order->payment_status }}
                                </span>
                            </td>

                            {{-- Order Status Tag --}}
                            <td class="py-4 px-4 text-center">
                                <span class="inline-block px-2.5 py-1 text-[9px] font-bold uppercase tracking-wider
                                    {{ $order->order_status === 'Pending' ? 'bg-amber-50 text-amber-700' : '' }}
                                    {{ $order->order_status === 'Processing' ? 'bg-purple-50 text-purple-700' : '' }}
                                    {{ $order->order_status === 'Shipped' ? 'bg-indigo-50 text-indigo-700' : '' }}
                                    {{ $order->order_status === 'Delivered' ? 'bg-emerald-50 text-emerald-700' : '' }}
                                    {{ $order->order_status === 'Cancelled' ? 'bg-rose-50 text-rose-700' : '' }}
                                ">
                                    {{ $order->order_status }}
                                </span>
                            </td>
                            
                            <td class="py-4 px-4 text-right">
                                <button type="button" 
                                        onclick="toggleOrderModal('{{ $order->id }}', true)"
                                        class="btn-secondary !py-1.5 !px-3 text-[10px] hover:bg-primary hover:text-white transition-colors">
                                    Manage
                                </button>
                            </td>
                        </tr>

                        {{-- Order Details Modal --}}
                        <div id="order-modal-{{ $order->id }}" class="fixed inset-0 z-[1000] hidden flex items-center justify-center bg-black/50 backdrop-blur-sm">
                            <div class="bg-white border border-gray-100 w-full max-w-4xl p-6 md:p-8 max-h-[90vh] overflow-y-auto shadow-xl flex flex-col gap-6">
                                <div class="flex justify-between items-center pb-4 border-b border-gray-100">
                                    <div>
                                        <span class="text-[10px] text-muted uppercase tracking-wider font-semibold">Order Detail View</span>
                                        <h3 class="text-lg font-display text-primary mt-0.5">Order {{ $order->order_number }}</h3>
                                    </div>
                                    <button type="button" 
                                            onclick="toggleOrderModal('{{ $order->id }}', false)" 
                                            class="text-muted hover:text-primary transition-colors text-lg font-semibold">
                                        ✕
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                                    {{-- Info Left --}}
                                    <div class="lg:col-span-2 space-y-6">
                                        <div>
                                            <h4 class="text-[10px] font-bold tracking-widest text-primary uppercase mb-3">Purchased Pieces</h4>
                                            <div class="space-y-3">
                                                @foreach($order->items as $item)
                                                    <div class="flex justify-between items-center border border-gray-100 p-3 bg-silk/10">
                                                        <div>
                                                            <div class="font-semibold text-primary text-xs">{{ $item->product_name }}</div>
                                                            <div class="text-[10px] text-muted mt-0.5">
                                                                Size: <span class="font-semibold text-primary">{{ $item->size ?? 'M' }}</span>
                                                                @if($item->color)
                                                                    · Color: <span class="font-semibold text-primary">{{ $item->color }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="text-right text-xs">
                                                            <div class="font-semibold text-primary">₹{{ number_format($item->price * $item->quantity, 2) }}</div>
                                                            <div class="text-[10px] text-muted mt-0.5">₹{{ number_format($item->price, 2) }} × {{ $item->quantity }}</div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- Payment Totals Summary --}}
                                        <div class="border-t border-gray-100 pt-4 space-y-2 text-xs">
                                            <div class="flex justify-between text-muted">
                                                <span>Subtotal</span>
                                                <span class="text-primary font-medium">₹{{ number_format($order->subtotal, 2) }}</span>
                                            </div>
                                            @if($order->discount > 0)
                                                <div class="flex justify-between text-rose-600">
                                                    <span>Discount Applied ({{ $order->coupon_code }})</span>
                                                    <span>- ₹{{ number_format($order->discount, 2) }}</span>
                                                </div>
                                            @endif
                                            <div class="flex justify-between text-muted">
                                                <span>Tax (18% GST)</span>
                                                <span class="text-primary font-medium">₹{{ number_format($order->tax, 2) }}</span>
                                            </div>
                                            <div class="flex justify-between font-bold text-sm text-primary pt-2 border-t border-gray-100">
                                                <span>Total Bill</span>
                                                <span>₹{{ number_format($order->total, 2) }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Status Forms Right --}}
                                    <div class="space-y-6 border-l border-gray-100 lg:pl-6">
                                        <div>
                                            <h4 class="text-[10px] font-bold tracking-widest text-primary uppercase mb-3">Shipping Profile</h4>
                                            <div class="text-xs space-y-1.5 text-muted">
                                                <div class="font-semibold text-primary">{{ $order->first_name }} {{ $order->last_name }}</div>
                                                <div>{{ $order->email }}</div>
                                                <div class="pt-1.5 border-t border-gray-100/60 mt-1.5">{{ $order->address }}</div>
                                                <div>{{ $order->city }} — {{ $order->postal_code }}</div>
                                                <div class="pt-2 text-primary font-semibold mt-1">Method: {{ $order->payment_method }}</div>
                                            </div>
                                        </div>

                                        {{-- Status Editor --}}
                                        <div class="bg-silk/20 border border-gray-100 p-4">
                                            <h4 class="text-[10px] font-bold tracking-widest text-primary uppercase mb-3">Update Status</h4>
                                            <form action="{{ route('admin.orders.update', $order->id) }}" method="POST" class="space-y-4 order-update-form">
                                                @csrf
                                                <div>
                                                    <label class="text-[9px] font-semibold text-muted uppercase tracking-wider block mb-1.5">Order Status</label>
                                                    <select name="order_status" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                                                        @foreach(['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'] as $st)
                                                            <option value="{{ $st }}" {{ $order->order_status === $st ? 'selected' : '' }}>{{ $st }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div>
                                                    <label class="text-[9px] font-semibold text-muted uppercase tracking-wider block mb-1.5">Payment Status</label>
                                                    <select name="payment_status" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                                                        @foreach(['Pending', 'Paid', 'Refunded'] as $pst)
                                                            <option value="{{ $pst }}" {{ $order->payment_status === $pst ? 'selected' : '' }}>{{ $pst }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <button type="submit" class="w-full btn-primary !py-2 text-[10px] text-center uppercase tracking-wider">
                                                    Save Changes
                                                </button>
                                            </form>
                                        </div>

                                        {{-- Download Invoice --}}
                                        <div class="pt-3">
                                            <a href="{{ route('admin.orders.invoice', $order->id) }}"
                                               target="_blank"
                                               class="w-full flex items-center justify-center gap-2 py-2 text-[10px] text-primary bg-silk border border-gray-200 hover:bg-gray-50 transition-colors uppercase font-semibold tracking-wider">
                                                ⬇ Download Invoice PDF
                                            </a>
                                            <form action="{{ route('admin.orders.send_invoice', $order->id) }}" method="POST" class="mt-2 invoice-email-form">
                                                @csrf
                                                <button type="submit" class="w-full py-2 text-[10px] text-secondary bg-white border border-secondary hover:bg-secondary hover:text-white transition-colors uppercase font-semibold tracking-wider">
                                                    ✉ Email Invoice to Customer
                                                </button>
                                            </form>
                                        </div>

                                        {{-- Delete Order --}}
                                        <div class="pt-2 border-t border-gray-100">
                                            <form action="{{ route('admin.orders.delete', $order->id) }}" method="POST" onsubmit="return confirm('Caution! Are you sure you want to permanently delete this order record?')" class="inline">
                                                @csrf
                                                <button type="submit" class="w-full py-2 text-[10px] text-red-600 bg-red-50 hover:bg-red-100 border border-red-200 transition-colors uppercase font-semibold tracking-wider">
                                                    Delete Order Record
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $orders->appends(request()->query())->links() }}
        </div>
    @endif
</div>

<script>
    function toggleOrderModal(orderId, show) {
        const modal = document.getElementById('order-modal-' + orderId);
        if (show) {
            modal.classList.remove('hidden');
        } else {
            modal.classList.add('hidden');
        }
    }

    // Bind invoice email forms directly (not inside DOMContentLoaded — this script
    // is re-executed by PJAX after content swap, so the forms are already in the DOM).
    document.querySelectorAll('.invoice-email-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = form.querySelector('button');
            const originalText = btn.innerHTML;
            btn.innerHTML = 'Sending...';
            btn.disabled = true;

            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const headers = { 'X-Requested-With': 'XMLHttpRequest' };
            if (csrfMeta) headers['X-CSRF-TOKEN'] = csrfMeta.getAttribute('content');

            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: headers
            })
            .then(res => res.json())
            .then(data => {
                if (typeof showToast === 'function') {
                    showToast(data.message, data.success ? 'success' : 'error');
                } else {
                    alert(data.message);
                }
            })
            .catch(() => {
                if (typeof showToast === 'function') {
                    showToast('Error sending invoice. Please check mail configuration.', 'error');
                } else {
                    alert('Error sending invoice.');
                }
            })
            .finally(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        });
    });
</script>
@endsection
