@extends('admin.layout')
@section('admin_title', 'Carts & Wishlists')

@section('admin_content')
<div class="mb-4">
  <h2 class="font-display text-2xl text-primary">Registry & Users</h2>
  <p class="text-[11px] text-muted mt-0.5">Roles, shopping bags, wishlists, and order history.</p>
</div>

{{-- Analytics --}}
<div class="grid grid-cols-2 gap-3 mb-5">
  <div class="border border-gray-100 bg-white p-4">
    <span class="text-[9px] font-bold tracking-widest text-muted uppercase">Active Bags</span>
    <div class="flex items-baseline gap-1 mt-1">
      <span class="text-2xl font-display text-primary leading-none">{{ $totalCartsCount }}</span>
      <span class="text-[9px] text-muted">items</span>
    </div>
  </div>
  <div class="border border-gray-100 bg-white p-4">
    <span class="text-[9px] font-bold tracking-widest text-muted uppercase">Wishlists</span>
    <div class="flex items-baseline gap-1 mt-1">
      <span class="text-2xl font-display text-primary leading-none">{{ $totalWishlistCount }}</span>
      <span class="text-[9px] text-muted">saved</span>
    </div>
  </div>
  <div class="border border-gray-100 bg-white p-4 col-span-2">
    <span class="text-[9px] font-bold tracking-widest text-muted uppercase">Most Coveted Piece</span>
    @if($mostWishlistedProduct)
      <div class="mt-1">
        <span class="text-sm font-semibold text-primary block truncate">{{ $mostWishlistedProduct->name }}</span>
        <span class="text-[10px] text-secondary font-bold">Saved {{ $mostWishlistedProduct->wishlist_count }} times</span>
      </div>
    @else
      <div class="mt-1 text-sm text-muted">No wishlist items yet.</div>
    @endif
  </div>
</div>

{{-- Filters --}}
<button type="button" onclick="document.getElementById('user-filters').classList.toggle('hidden')"
        class="w-full text-center text-[10px] font-bold tracking-widest uppercase text-primary border border-gray-300 py-3 mb-5">Filters</button>

<div id="user-filters" class="hidden mb-6">
  <form action="{{ route('admin.users.index') }}" method="GET" class="bg-white border border-gray-200 p-4 space-y-3">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or email..."
           class="w-full text-sm bg-white border border-gray-200 px-3 py-2.5 outline-none">
    <select name="role" class="w-full text-sm bg-white border border-gray-200 px-3 py-2.5 outline-none">
      <option value="">All Roles</option>
      <option value="customer" {{ request('role') === 'customer' ? 'selected' : '' }}>Customer</option>
      <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
      <option value="superadmin" {{ request('role') === 'superadmin' ? 'selected' : '' }}>Super Admin</option>
    </select>
    <div class="flex items-center gap-2">
      <input type="number" name="min_spend" value="{{ request('min_spend') }}" placeholder="Min Spend"
             class="flex-1 text-sm bg-white border border-gray-200 px-3 py-2.5 outline-none">
      <span class="text-xs text-muted">-</span>
      <input type="number" name="max_spend" value="{{ request('max_spend') }}" placeholder="Max Spend"
             class="flex-1 text-sm bg-white border border-gray-200 px-3 py-2.5 outline-none">
    </div>
    <div class="flex gap-2">
      <button type="submit" class="flex-1 btn-primary !py-2.5 text-[10px] uppercase tracking-wider">Filter</button>
      @if(request()->anyFilled(['search', 'role', 'min_spend', 'max_spend']))
        <a href="{{ route('admin.users.index') }}" class="flex-1 text-center text-[10px] uppercase tracking-wider border border-gray-200 text-muted py-2.5">Reset</a>
      @endif
    </div>
  </form>
</div>

@if($users->isEmpty())
  <div class="text-center py-12 border border-dashed border-gray-200 bg-white">
    <span class="text-2xl text-gray-300">✦</span>
    <p class="text-sm text-muted mt-2">No user profiles found.</p>
  </div>
@else
  <div class="space-y-3">
    @foreach($users as $user)
      @php
        $roleClass = match($user->role ?? 'customer') {
          'superadmin' => 'bg-violet-50 text-violet-700 border border-violet-100',
          'admin'      => 'bg-slate-100 text-primary border border-gray-200',
          default      => 'bg-gray-50 text-muted border border-gray-200',
        };
        $roleLabel = match($user->role ?? 'customer') {
          'superadmin' => 'Super Admin',
          'admin'      => 'Admin',
          default      => 'Customer',
        };
      @endphp
      <div class="border border-gray-100 bg-white">
        <div class="p-4">
          <div class="flex items-start gap-3">
            <div class="w-11 h-11 shrink-0 bg-slate-100 border border-gray-200 flex items-center justify-center font-display text-primary text-lg">
              {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center flex-wrap gap-2">
                <h3 class="text-sm font-bold text-primary truncate">{{ $user->name }}</h3>
                <span id="role-badge-{{ $user->id }}" class="text-[8px] px-2 py-0.5 font-bold uppercase tracking-wider {{ $roleClass }}">{{ $roleLabel }}</span>
              </div>
              <p class="text-[10px] text-muted mt-0.5 truncate">{{ $user->email }}</p>
              <p class="text-[10px] text-muted">Joined {{ $user->created_at->format('M d, Y') }}</p>
            </div>
          </div>

          <div class="flex items-center justify-between gap-3 mt-3 pt-3 border-t border-gray-100">
            <div>
              <span class="text-[9px] text-muted uppercase tracking-widest font-bold block">Total Spend</span>
              <span class="text-sm font-semibold text-primary">₹{{ number_format($user->orders->sum('total')) }}</span>
              <span class="text-[9px] text-muted">· {{ $user->orders->count() }} orders</span>
            </div>
            <button onclick="toggleUserActivity('user-activity-{{ $user->id }}')"
                    class="text-[10px] font-bold tracking-wider uppercase text-primary border border-gray-300 px-3 py-2 flex items-center gap-1.5">
              Activity
              <span class="bg-primary text-white text-[8px] px-1.5 py-0.5 rounded-full">{{ $user->cartItems->sum('quantity') + $user->wishlistItems->count() }}</span>
            </button>
          </div>

          @if($user->id !== auth()->id() && ($user->role !== 'superadmin' || auth()->user()->isSuperAdmin()))
            <div class="mt-3">
              <label class="text-[9px] font-bold tracking-widest uppercase text-muted block mb-1">Assign Role</label>
              <form action="{{ route('admin.users.role', $user->id) }}" method="POST">
                @csrf
                <select name="role" data-user="{{ $user->id }}" onchange="submitRoleAjax(this)"
                        class="w-full text-xs text-primary bg-slate-50 border border-gray-200 px-3 py-2.5 outline-none">
                  <option value="customer" {{ ($user->role ?? 'customer') === 'customer' ? 'selected' : '' }}>Customer</option>
                  <option value="admin" {{ ($user->role ?? '') === 'admin' ? 'selected' : '' }}>Admin</option>
                  @if(auth()->user()->isSuperAdmin())
                    <option value="superadmin" {{ ($user->role ?? '') === 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                  @endif
                </select>
              </form>
            </div>
          @else
            <div class="mt-3 text-[10px] text-muted italic">
              {{ $user->id === auth()->id() ? 'Your account' : 'Role locked' }}
            </div>
          @endif
        </div>

        {{-- Activity --}}
        <div id="user-activity-{{ $user->id }}" class="hidden border-t border-gray-100 bg-slate-50/50 p-4 space-y-5">
          {{-- Cart --}}
          <div>
            <h4 class="text-[10px] font-bold tracking-widest text-primary uppercase mb-2 pb-1.5 border-b border-gray-200 flex justify-between">
              <span>🛒 Active Cart</span><span class="text-muted font-normal">{{ $user->cartItems->sum('quantity') }} items</span>
            </h4>
            @if($user->cartItems->isEmpty())
              <p class="text-xs text-muted italic py-2">Shopping bag is empty.</p>
            @else
              <div class="space-y-2">
                @foreach($user->cartItems as $item)
                  <div class="flex items-center justify-between text-xs gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                      <img src="{{ $item->product->image_url ?? '' }}" alt="" class="w-8 h-10 object-cover border border-gray-200 shrink-0"
                           onerror="this.src='https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=100&q=70'">
                      <div class="min-w-0">
                        <span class="font-bold text-primary block truncate">{{ $item->product->name }}</span>
                        <span class="text-[9px] text-muted">Size: {{ $item->size ?? 'One Size' }} · Qty: {{ $item->quantity }}</span>
                      </div>
                    </div>
                    <span class="font-semibold shrink-0">₹{{ number_format(($item->product->price ?? 0) * $item->quantity) }}</span>
                  </div>
                @endforeach
              </div>
            @endif
          </div>

          {{-- Wishlist --}}
          <div>
            <h4 class="text-[10px] font-bold tracking-widest text-primary uppercase mb-2 pb-1.5 border-b border-gray-200 flex justify-between">
              <span>💖 Wishlist</span><span class="text-muted font-normal">{{ $user->wishlistItems->count() }} items</span>
            </h4>
            @if($user->wishlistItems->isEmpty())
              <p class="text-xs text-muted italic py-2">Wishlist is empty.</p>
            @else
              <div class="space-y-2">
                @foreach($user->wishlistItems as $item)
                  <div class="flex items-center justify-between text-xs p-2 bg-white border border-gray-200">
                    <div class="flex items-center gap-2 min-w-0">
                      <img src="{{ $item->product->image_url ?? '' }}" alt="" class="w-8 h-10 object-cover border border-gray-200 shrink-0"
                           onerror="this.src='https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=100&q=70'">
                      <span class="font-bold text-primary block truncate">{{ $item->product->name }}</span>
                    </div>
                    <span class="font-semibold text-secondary shrink-0">₹{{ number_format($item->product->price) }}</span>
                  </div>
                @endforeach
              </div>
            @endif
          </div>

          {{-- Orders --}}
          <div>
            <h4 class="text-[10px] font-bold tracking-widest text-primary uppercase mb-2 pb-1.5 border-b border-gray-200 flex justify-between">
              <span>📦 Orders</span><span class="text-muted font-normal">{{ $user->orders->count() }}</span>
            </h4>
            @if($user->orders->isEmpty())
              <p class="text-xs text-muted italic py-2">No orders yet.</p>
            @else
              <div class="space-y-2">
                @foreach($user->orders as $order)
                  <div class="p-2.5 bg-white border border-gray-200 text-xs">
                    <div class="flex justify-between items-center">
                      <span class="font-bold text-primary">{{ $order->order_number }}</span>
                      <span class="text-[8px] px-1.5 py-0.5 font-bold uppercase tracking-wider bg-slate-50 border border-gray-200">{{ $order->order_status }}</span>
                    </div>
                    <div class="flex justify-between text-[9px] text-muted mt-1">
                      <span>{{ $order->created_at->format('M d, Y') }}</span>
                      <span class="font-semibold text-primary">₹{{ number_format($order->total) }}</span>
                    </div>
                  </div>
                @endforeach
              </div>
            @endif
          </div>
        </div>
      </div>
    @endforeach
  </div>

  <div class="mt-6">
    {{ $users->links() }}
  </div>
@endif
@endsection

@section('scripts')
<script>
  function toggleUserActivity(id) {
    const c = document.getElementById(id);
    if (c) c.classList.toggle('hidden');
  }

  function submitRoleAjax(selectElement) {
    const form = selectElement.form;
    const userId = selectElement.getAttribute('data-user');
    const roleValue = selectElement.value;

    selectElement.disabled = true;
    selectElement.style.opacity = '0.5';

    fetch(form.action, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({ role: roleValue })
    })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          showToast(data.message, 'success');
          const badge = document.getElementById('role-badge-' + userId);
          if (badge) {
            badge.innerText = roleValue === 'superadmin' ? 'Super Admin' : (roleValue === 'admin' ? 'Admin' : 'Customer');
            badge.className = 'text-[8px] px-2 py-0.5 font-bold uppercase tracking-wider ' +
              (roleValue === 'superadmin' ? 'bg-violet-50 text-violet-700 border border-violet-100'
                : (roleValue === 'admin' ? 'bg-slate-100 text-primary border border-gray-200'
                  : 'bg-gray-50 text-muted border border-gray-200'));
          }
        } else {
          showToast(data.message || 'Error updating role.', 'error');
          window.location.reload();
        }
      })
      .catch(() => showToast('Error communicating with server.', 'error'))
      .finally(() => { selectElement.disabled = false; selectElement.style.opacity = '1'; });
  }
</script>
@endsection
