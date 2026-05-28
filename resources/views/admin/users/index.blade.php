@extends('admin.layout')

@section('admin_content')
<div>
    {{-- Page Header --}}
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold uppercase tracking-widest text-primary">Registry & Users</h2>
            <p class="text-xs text-muted mt-1">Manage user profiles, change roles, track shopping bags, wishlists, and order histories.</p>
        </div>
    </div>

    {{-- Notifications --}}
    @if(session('success'))
        <div class="mb-6 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold rounded">
            ✓ {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 px-4 py-3 bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold rounded">
            ✕ {{ session('error') }}
        </div>
    @endif

    {{-- Analytics Row --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-silk/30 border border-gray-100 p-6 flex flex-col justify-between hover:shadow-sm transition-shadow">
            <span class="text-[10px] font-bold tracking-widest text-muted uppercase">Active Bags</span>
            <div class="flex items-baseline gap-2 mt-2">
                <span class="text-3xl font-display text-primary">{{ $totalCartsCount }}</span>
                <span class="text-[10px] text-muted">items total</span>
            </div>
            <span class="text-[9px] text-muted mt-3">✦ Items added to carts across the store</span>
        </div>

        <div class="bg-silk/30 border border-gray-100 p-6 flex flex-col justify-between hover:shadow-sm transition-shadow">
            <span class="text-[10px] font-bold tracking-widest text-muted uppercase">Saved Wishlists</span>
            <div class="flex items-baseline gap-2 mt-2">
                <span class="text-3xl font-display text-primary">{{ $totalWishlistCount }}</span>
                <span class="text-[10px] text-muted">bookmarks</span>
            </div>
            <span class="text-[9px] text-muted mt-3">✦ Products wishlisted by active shoppers</span>
        </div>

        <div class="bg-silk/30 border border-gray-100 p-6 flex flex-col justify-between hover:shadow-sm transition-shadow">
            <span class="text-[10px] font-bold tracking-widest text-muted uppercase">Most Coveted Piece</span>
            @if($mostWishlistedProduct)
                <div class="mt-2">
                    <span class="text-sm font-semibold text-primary block truncate">{{ $mostWishlistedProduct->name }}</span>
                    <span class="text-[10px] text-secondary font-bold block mt-0.5">Saved {{ $mostWishlistedProduct->wishlist_count }} times</span>
                </div>
            @else
                <div class="mt-2 text-sm text-muted">No wishlist items tracked yet.</div>
            @endif
            <span class="text-[9px] text-muted mt-3">✦ Most wishlisted product this season</span>
        </div>
    </div>

    {{-- Search & Filter Bar --}}
    <form action="{{ route('admin.users.index') }}" method="GET" class="bg-slate-50 border border-gray-200 p-5 mb-8 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        <div class="flex flex-col md:flex-row items-stretch md:items-center gap-4 flex-1">
            <div class="relative flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..." 
                       class="w-full px-4 py-3 bg-white border border-gray-200 focus:border-secondary focus:outline-none transition-colors text-sm">
            </div>
            <div class="w-full md:w-48">
                <select name="role" class="w-full px-4 py-3 bg-white border border-gray-200 focus:border-secondary focus:outline-none transition-colors text-sm cursor-pointer">
                    <option value="">All Roles</option>
                    <option value="customer" {{ request('role') === 'customer' ? 'selected' : '' }}>Customer</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="superadmin" {{ request('role') === 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                </select>
            </div>
            <div class="w-full md:w-64 flex items-center gap-2">
                <input type="number" name="min_spend" value="{{ request('min_spend') }}" placeholder="Min Spend" 
                       class="w-full px-4 py-3 bg-white border border-gray-200 focus:border-secondary focus:outline-none transition-colors text-sm">
                <span class="text-muted text-xs">-</span>
                <input type="number" name="max_spend" value="{{ request('max_spend') }}" placeholder="Max Spend" 
                       class="w-full px-4 py-3 bg-white border border-gray-200 focus:border-secondary focus:outline-none transition-colors text-sm">
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="btn-primary w-full md:w-auto !py-3.5 !px-8 text-[10px]">Filter Registry</button>
            @if(request()->anyFilled(['search', 'role', 'min_spend', 'max_spend']))
                <a href="{{ route('admin.users.index') }}" class="btn-secondary w-full md:w-auto text-center !py-3.5 !px-8 text-[10px]">Reset</a>
            @endif
        </div>
    </form>

    {{-- User List --}}
    @if($users->isEmpty())
        <div class="text-center py-20 border border-dashed border-gray-200 bg-slate-50/50">
            <span class="text-3xl text-gray-300">✦</span>
            <p class="text-sm text-muted mt-3">No user profiles found in the registry.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($users as $user)
                <div class="border border-gray-200 bg-white hover:border-gray-300 transition-colors">
                    {{-- User Summary Card --}}
                    <div class="p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                        {{-- User Avatar & Info --}}
                        <div class="flex items-start md:items-center gap-4 flex-1">
                            <div class="w-12 h-12 flex-shrink-0 bg-slate-100 border border-gray-200 flex items-center justify-center font-display font-light text-primary text-lg">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="flex items-center flex-wrap gap-2 mb-1">
                                    <h3 class="text-sm font-bold text-primary">{{ $user->name }}</h3>
                                    
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
                                    <span class="text-[9px] px-2 py-0.5 font-bold uppercase tracking-wider {{ $roleClass }}">
                                        {{ $roleLabel }}
                                    </span>
                                </div>
                                <p class="text-xs text-muted">{{ $user->email }} · Joined {{ $user->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>

                        {{-- Role Control (Inline) --}}
                        <div class="flex items-center gap-4">
                            @if($user->id !== auth()->id() && ($user->role !== 'superadmin' || auth()->user()->isSuperAdmin()))
                                <div class="flex flex-col gap-1.5">
                                    <label class="block text-[9px] font-bold tracking-widest uppercase text-muted">Assign Role</label>
                                    <form action="{{ route('admin.users.role', $user->id) }}" method="POST" class="inline">
                                        @csrf
                                        <select name="role" onchange="submitRoleAjax(this)" 
                                                class="text-xs text-primary bg-slate-50 border border-gray-200 px-3 py-2 outline-none cursor-pointer focus:border-secondary focus:bg-white transition-colors">
                                            <option value="customer" {{ ($user->role ?? 'customer') === 'customer' ? 'selected' : '' }}>Customer</option>
                                            <option value="admin" {{ ($user->role ?? '') === 'admin' ? 'selected' : '' }}>Admin</option>
                                            @if(auth()->user()->isSuperAdmin())
                                                <option value="superadmin" {{ ($user->role ?? '') === 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                                            @endif
                                        </select>
                                    </form>
                                </div>
                            @else
                                <div class="text-right">
                                    <span class="text-[9px] text-muted italic font-semibold uppercase tracking-wider block">Role Status</span>
                                    <span class="text-xs text-muted mt-1 block">
                                        {{ $user->id === auth()->id() ? 'Your Account' : 'Role Locked' }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex items-center gap-3">
                            <div class="text-right hidden md:block">
                                <span class="text-[9px] text-muted uppercase tracking-widest font-bold block mb-1">Total Spend</span>
                                <span class="text-sm font-semibold text-primary block">₹{{ number_format($user->orders->sum('total')) }}</span>
                                <span class="text-[9px] text-muted block mt-0.5">{{ $user->orders->count() }} orders</span>
                            </div>
                            <button onclick="toggleUserActivity('user-activity-{{ $user->id }}')" 
                                    class="btn-secondary flex items-center justify-center gap-2 !py-3 !px-5 text-[10px] w-full lg:w-auto">
                                <span>View Activity</span>
                                <span class="bg-primary text-white text-[9px] px-1.5 py-0.5 rounded-full font-bold">
                                    {{ $user->cartItems->sum('quantity') + $user->wishlistItems->count() }}
                                </span>
                                <svg class="w-3.5 h-3.5 transform transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" id="icon-user-activity-{{ $user->id }}">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Collapsible Activity Section --}}
                    <div id="user-activity-{{ $user->id }}" class="hidden border-t border-gray-100 bg-slate-50/50 transition-all duration-300">
                        <div class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-8">
                            {{-- Cart Activity Column --}}
                            <div>
                                <h4 class="text-[10px] font-bold tracking-widest text-primary uppercase flex items-center justify-between mb-4 pb-2 border-b border-gray-200">
                                    <span>🛒 Active Cart</span>
                                    <span class="text-muted font-normal">({{ $user->cartItems->sum('quantity') }} items)</span>
                                </h4>
                                @if($user->cartItems->isEmpty())
                                    <p class="text-xs text-muted italic py-4">Shopping bag is empty.</p>
                                @else
                                    <div class="space-y-3 max-h-64 overflow-y-auto pr-2">
                                        @foreach($user->cartItems as $item)
                                            <div class="flex items-center justify-between text-xs gap-3">
                                                <div class="flex items-center gap-2">
                                                    <img src="{{ $item->product->image_url ?? '' }}" 
                                                         alt="" class="w-8 h-10 object-cover border border-gray-200 flex-shrink-0"
                                                         onerror="this.src='https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=100&q=70'">
                                                    <div>
                                                        <span class="font-bold text-primary block truncate max-w-[120px]">{{ $item->product->name }}</span>
                                                        <span class="text-[9px] text-muted block mt-0.5">Size: {{ $item->size ?? 'One Size' }}</span>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <span class="font-semibold block">₹{{ number_format(($item->product->price ?? 0) * $item->quantity) }}</span>
                                                    <span class="text-[9px] text-muted block">Qty: {{ $item->quantity }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="mt-4 pt-3 border-t border-gray-200 flex justify-between items-center text-xs font-bold text-primary">
                                        <span>Total Value</span>
                                        <span>₹{{ number_format($user->cartItems->sum(fn($i) => ($i->product->price ?? 0) * $i->quantity)) }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Wishlist Activity Column --}}
                            <div>
                                <h4 class="text-[10px] font-bold tracking-widest text-primary uppercase flex items-center justify-between mb-4 pb-2 border-b border-gray-200">
                                    <span>💖 Saved Wishlist</span>
                                    <span class="text-muted font-normal">({{ $user->wishlistItems->count() }} items)</span>
                                </h4>
                                @if($user->wishlistItems->isEmpty())
                                    <p class="text-xs text-muted italic py-4">Wishlist is empty.</p>
                                @else
                                    <div class="grid grid-cols-1 gap-2.5 max-h-64 overflow-y-auto pr-2">
                                        @foreach($user->wishlistItems as $item)
                                            <div class="flex items-center justify-between text-xs p-2 bg-white border border-gray-200">
                                                <div class="flex items-center gap-2">
                                                    <img src="{{ $item->product->image_url ?? '' }}" 
                                                         alt="" class="w-8 h-10 object-cover border border-gray-200 flex-shrink-0"
                                                         onerror="this.src='https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=100&q=70'">
                                                    <span class="font-bold text-primary block truncate max-w-[120px]">{{ $item->product->name }}</span>
                                                </div>
                                                <span class="font-semibold text-secondary">₹{{ number_format($item->product->price) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- Orders Column --}}
                            <div>
                                <h4 class="text-[10px] font-bold tracking-widest text-primary uppercase flex items-center justify-between mb-4 pb-2 border-b border-gray-200">
                                    <span>📦 Total Orders</span>
                                    <span class="text-muted font-normal">({{ $user->orders->count() }} orders)</span>
                                </h4>
                                @if($user->orders->isEmpty())
                                    <p class="text-xs text-muted italic py-4">No order records placed yet.</p>
                                @else
                                    <div class="space-y-2.5 max-h-64 overflow-y-auto pr-2">
                                        @foreach($user->orders as $order)
                                            <div class="p-3 bg-white border border-gray-200 text-xs">
                                                <div class="flex justify-between items-center mb-1">
                                                    <span class="font-bold text-primary">{{ $order->order_number }}</span>
                                                    <span class="text-[8px] px-1.5 py-0.5 rounded font-bold uppercase tracking-wider bg-slate-50 border border-gray-200">{{ $order->order_status }}</span>
                                                </div>
                                                <div class="flex justify-between text-[9px] text-muted">
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
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $users->links() }}
        </div>
    @endif
</div>

<script>
    function toggleUserActivity(id) {
        const container = document.getElementById(id);
        const icon = document.getElementById('icon-' + id);
        if (container.classList.contains('hidden')) {
            container.classList.remove('hidden');
            if (icon) icon.classList.add('rotate-180');
        } else {
            container.classList.add('hidden');
            if (icon) icon.classList.remove('rotate-180');
        }
    }
</script>
@endsection
