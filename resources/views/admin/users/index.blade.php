@extends('admin.layout')

@section('admin_content')
<div>
    {{-- Page Header --}}
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-primary">User Management</h2>
        <p class="text-xs text-muted mt-1">View all registered customers and admins, manage cart/wishlist activity, and control roles.</p>
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
        <div class="bg-silk/30 border border-gray-200/50 p-6 flex flex-col justify-between">
            <span class="text-[10px] font-bold tracking-widest text-muted uppercase">Active Bags</span>
            <div class="flex items-baseline gap-2 mt-2">
                <span class="text-3xl font-display text-primary">{{ $totalCartsCount }}</span>
                <span class="text-[10px] text-muted">items in bags</span>
            </div>
            <span class="text-[9px] text-muted mt-3">✦ Tracked across all active shopping bags</span>
        </div>

        <div class="bg-silk/30 border border-gray-200/50 p-6 flex flex-col justify-between">
            <span class="text-[10px] font-bold tracking-widest text-muted uppercase">Saved Wishlists</span>
            <div class="flex items-baseline gap-2 mt-2">
                <span class="text-3xl font-display text-primary">{{ $totalWishlistCount }}</span>
                <span class="text-[10px] text-muted">items saved</span>
            </div>
            <span class="text-[9px] text-muted mt-3">✦ Total bookmarks across all collections</span>
        </div>

        <div class="bg-silk/30 border border-gray-200/50 p-6 flex flex-col justify-between">
            <span class="text-[10px] font-bold tracking-widest text-muted uppercase">Most Coveted Piece</span>
            @if($mostWishlistedProduct)
                <div class="mt-2">
                    <span class="text-sm font-semibold text-primary block truncate">{{ $mostWishlistedProduct->name }}</span>
                    <span class="text-[10px] text-secondary font-bold block mt-0.5">Saved {{ $mostWishlistedProduct->wishlist_count }} times</span>
                </div>
            @else
                <div class="mt-2 text-sm text-muted">No wishlist items tracked yet.</div>
            @endif
            <span class="text-[9px] text-muted mt-3">✦ Most wishlisted product of this season</span>
        </div>
    </div>

    {{-- User List --}}
    @if($users->isEmpty())
        <div class="text-center py-16 border border-dashed border-gray-100 bg-silk/30">
            <span class="text-3xl">✦</span>
            <p class="text-sm text-muted mt-3">No registered user profiles found in the registry.</p>
        </div>
    @else
        <div class="space-y-6">
            @foreach($users as $user)
                <div class="border border-gray-100 p-6 bg-white hover:shadow-md transition-shadow">
                    {{-- Customer Details Header --}}
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-start border-b border-gray-100 pb-4 mb-4 gap-3">
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <h3 class="text-sm font-bold text-primary">{{ $user->name }}</h3>

                                {{-- Role Badge --}}
                                @php
                                    $roleClass = match($user->role ?? 'customer') {
                                        'superadmin' => 'bg-violet-100 text-violet-800 border border-violet-200',
                                        'admin'      => 'bg-indigo-50 text-indigo-700 border border-indigo-200',
                                        default      => 'bg-gray-100 text-gray-600 border border-gray-200',
                                    };
                                    $roleLabel = match($user->role ?? 'customer') {
                                        'superadmin' => '⭐ Super Admin',
                                        'admin'      => '🔑 Admin',
                                        default      => '👤 Customer',
                                    };
                                @endphp
                                <span class="text-[9px] px-2 py-0.5 font-bold uppercase tracking-wider {{ $roleClass }}">
                                    {{ $roleLabel }}
                                </span>
                            </div>
                            <p class="text-[10px] text-muted">{{ $user->email }} · Joined {{ $user->created_at->format('M d, Y') }}</p>
                        </div>

                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                            <div class="flex gap-4 text-[10px]">
                                <span class="text-muted">Cart: <strong class="text-primary">{{ $user->cartItems->sum('quantity') }}</strong></span>
                                <span class="text-muted">Wishlist: <strong class="text-primary">{{ $user->wishlistItems->count() }}</strong></span>
                            </div>

                            {{-- Role Change Form (SuperAdmin Only) --}}
                            @if(auth()->user()->isSuperAdmin() && $user->id !== auth()->id())
                                <form action="{{ route('admin.users.role', $user->id) }}" method="POST" class="flex items-center gap-2">
                                    @csrf
                                    <select name="role"
                                            onchange="this.form.submit()"
                                            class="text-[10px] text-primary bg-silk border border-gray-200 px-2 py-1.5 outline-none cursor-pointer">
                                        <option value="customer" {{ ($user->role ?? 'customer') === 'customer' ? 'selected' : '' }}>👤 Customer</option>
                                        <option value="admin"    {{ ($user->role ?? '') === 'admin' ? 'selected' : '' }}>🔑 Admin</option>
                                        <option value="superadmin" {{ ($user->role ?? '') === 'superadmin' ? 'selected' : '' }}>⭐ Super Admin</option>
                                    </select>
                                </form>
                            @elseif($user->id === auth()->id())
                                <span class="text-[9px] text-muted italic">(Your account)</span>
                            @elseif(!auth()->user()->isSuperAdmin() && $user->isAdmin())
                                <span class="text-[9px] text-muted italic">Role: locked</span>
                            @endif
                        </div>
                    </div>

                    {{-- Cart + Wishlist Grid --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        {{-- Cart Items --}}
                        <div class="space-y-3">
                            <h4 class="text-[10px] font-bold tracking-widest text-primary uppercase flex items-center gap-1.5 mb-3">
                                🛒 Active Cart
                                <span class="font-normal text-muted">({{ $user->cartItems->count() }} items)</span>
                            </h4>

                            @if($user->cartItems->isEmpty())
                                <p class="text-[11px] text-muted italic p-3 bg-silk/20 border border-dotted border-gray-200/60">
                                    No active items in shopping bag.
                                </p>
                            @else
                                <div class="divide-y divide-gray-100/50 max-h-56 overflow-y-auto pr-2">
                                    @foreach($user->cartItems as $cartItem)
                                        <div class="flex items-center justify-between py-2 text-xs">
                                            <div class="flex items-center gap-3">
                                                <img src="{{ $cartItem->product->image_url ?? '' }}"
                                                     alt="{{ $cartItem->product->name ?? 'Product' }}"
                                                     class="w-10 h-10 object-cover border border-gray-100 flex-shrink-0"
                                                     onerror="this.src='https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=100&q=70'">
                                                <div>
                                                    <span class="font-semibold text-primary block">{{ $cartItem->product->name ?? 'Deleted Product' }}</span>
                                                    <span class="text-[9px] text-muted block mt-0.5">
                                                        Size: {{ $cartItem->size ?? '—' }} · ₹{{ number_format($cartItem->product->price ?? 0) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-primary font-bold block">₹{{ number_format(($cartItem->product->price ?? 0) * $cartItem->quantity) }}</span>
                                                <span class="text-[9px] text-muted block mt-0.5">Qty: {{ $cartItem->quantity }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="pt-2 border-t border-gray-100 text-right text-[10px] font-semibold text-primary">
                                    Cart Total: ₹{{ number_format($user->cartItems->sum(fn($i) => ($i->product->price ?? 0) * $i->quantity)) }}
                                </div>
                            @endif
                        </div>

                        {{-- Wishlist Items --}}
                        <div class="space-y-3">
                            <h4 class="text-[10px] font-bold tracking-widest text-primary uppercase flex items-center gap-1.5 mb-3">
                                💖 Wishlist
                                <span class="font-normal text-muted">({{ $user->wishlistItems->count() }} saved)</span>
                            </h4>

                            @if($user->wishlistItems->isEmpty())
                                <p class="text-[11px] text-muted italic p-3 bg-silk/20 border border-dotted border-gray-200/60">
                                    Customer wishlist is empty.
                                </p>
                            @else
                                <div class="grid grid-cols-2 gap-3 max-h-56 overflow-y-auto pr-2">
                                    @foreach($user->wishlistItems as $wishItem)
                                        <div class="flex items-center gap-2 p-2 bg-silk/15 border border-gray-100">
                                            <img src="{{ $wishItem->product->image_url ?? '' }}"
                                                 alt="{{ $wishItem->product->name ?? 'Product' }}"
                                                 class="w-8 h-8 object-cover border border-gray-100 flex-shrink-0"
                                                 onerror="this.src='https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=100&q=70'">
                                            <div class="min-w-0 flex-1">
                                                <span class="text-[11px] font-semibold text-primary block truncate">{{ $wishItem->product->name ?? 'Deleted' }}</span>
                                                <span class="text-[9px] text-muted block">₹{{ number_format($wishItem->product->price ?? 0) }}</span>
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

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
