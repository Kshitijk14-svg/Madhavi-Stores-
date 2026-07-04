@extends('layouts.app')
@section('title', 'Track Your Order | Madhavi Stores')

@section('content')

<section class="py-20">
    <div class="max-w-3xl mx-auto px-6 lg:px-12">
        <h1 class="font-display text-4xl font-light text-primary mb-4 text-center italic">Track Your Order</h1>
        <p class="text-sm text-muted text-center mb-12">
            Enter your order number and the email address you used at checkout.
        </p>

        @guest
        <p class="text-xs text-muted text-center mb-10">
            You can <a href="{{ route('login') }}" class="underline">sign in</a> to keep your order history in one place
            for this and future orders.
        </p>
        @endguest

        <form action="{{ route('track-order.lookup') }}" method="POST" class="space-y-4 mb-16">
            @csrf
            <input type="text" name="order_number" placeholder="Order Number (e.g. MS-XXXXXXXX)" value="{{ $orderNumber }}" required
                   class="w-full bg-transparent border border-primary/10 px-4 py-3 text-sm focus:outline-none focus:border-secondary transition-colors">
            <input type="email" name="email" placeholder="Email Address" value="{{ $email }}" required
                   class="w-full bg-transparent border border-primary/10 px-4 py-3 text-sm focus:outline-none focus:border-secondary transition-colors">
            <button type="submit" class="w-full py-4 bg-primary text-white text-xs tracking-widest uppercase font-bold hover:bg-secondary transition-all duration-300">
                Check Status
            </button>
        </form>

        @if($searched)
            @if(!$order)
                <p class="text-sm text-center text-rose-600">
                    We couldn't find a matching order. Please double-check your order number and email.
                </p>
            @else
                <div class="border border-primary/10 p-8 lg:p-10">
                    <div class="flex items-center justify-between flex-wrap gap-3 mb-6 pb-6 border-b border-primary/10">
                        <div>
                            <p class="text-xs tracking-widest uppercase text-muted mb-1">Order</p>
                            <p class="font-mono text-sm font-bold text-primary">{{ $order->order_number }}</p>
                            <p class="text-xs text-muted mt-1">Placed {{ $order->created_at->format('d M Y') }}</p>
                        </div>
                        <span class="text-[10px] font-bold tracking-widest uppercase px-3 py-1.5
                            @if($order->order_status === 'Delivered') bg-green-50 text-green-700
                            @elseif($order->order_status === 'Cancelled') bg-rose-50 text-rose-600
                            @elseif($order->order_status === 'Shipped') bg-blue-50 text-blue-700
                            @else bg-amber-50 text-amber-700 @endif">
                            {{ $order->order_status }}
                        </span>
                    </div>

                    <div class="space-y-4 mb-8">
                        @foreach($order->items as $item)
                        <div class="flex gap-4">
                            <div class="w-14 h-16 bg-background overflow-hidden flex-shrink-0 border border-primary/10">
                                @if($item->product)
                                <img src="{{ $item->product->image_url }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                                @endif
                            </div>
                            <div class="flex-grow flex flex-col justify-center">
                                <p class="text-sm font-medium text-primary">{{ $item->product_name }}</p>
                                <p class="text-xs text-muted">
                                    @if($item->size) Size: {{ $item->size }} &times; @endif Qty: {{ $item->quantity }}
                                </p>
                            </div>
                            <p class="text-sm text-primary self-center">₹{{ number_format($item->price * $item->quantity) }}</p>
                        </div>
                        @endforeach
                    </div>

                    <div class="space-y-2 pt-6 border-t border-primary/10 mb-2">
                        <div class="flex justify-between text-sm text-muted">
                            <span>Subtotal</span><span>₹{{ number_format($order->subtotal) }}</span>
                        </div>
                        @if($order->discount > 0)
                        <div class="flex justify-between text-sm text-rose-600">
                            <span>Discount{{ $order->coupon_code ? ' ('.$order->coupon_code.')' : '' }}</span>
                            <span>-₹{{ number_format($order->discount) }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between text-lg font-medium text-primary pt-3 border-t border-primary/10 mt-3">
                            <span>Total Paid</span><span>₹{{ number_format($order->total) }}</span>
                        </div>
                    </div>

                    <p class="text-xs text-muted mt-6">
                        Shipping to {{ $order->first_name }} {{ $order->last_name }}, {{ $order->address }}, {{ $order->city }} {{ $order->postal_code }}
                    </p>
                </div>

                @guest
                @if(!$order->user_id)
                <div class="border border-primary/10 p-8 lg:p-10 mt-8">
                    <h3 class="text-xs tracking-widest uppercase font-bold text-primary mb-2">Save This Order To An Account</h3>
                    <p class="text-xs text-muted mb-6">Create a password to keep this and future orders in one place. We'll email you a quick code to confirm it's really you.</p>
                    @error('email')
                    <p class="text-xs text-rose-600 mb-4">{{ $message }}</p>
                    @enderror
                    @error('password')
                    <p class="text-xs text-rose-600 mb-4">{{ $message }}</p>
                    @enderror
                    <form action="{{ route('track-order.claim') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="order_number" value="{{ $order->order_number }}">
                        <input type="hidden" name="email" value="{{ $order->email }}">
                        <input type="text" name="name" value="{{ old('name', $order->first_name . ' ' . $order->last_name) }}" required
                               class="w-full bg-transparent border border-primary/10 px-4 py-3 text-sm focus:outline-none focus:border-secondary transition-colors">
                        <input type="password" name="password" placeholder="Create a password" required minlength="8"
                               class="w-full bg-transparent border border-primary/10 px-4 py-3 text-sm focus:outline-none focus:border-secondary transition-colors">
                        <input type="password" name="password_confirmation" placeholder="Confirm password" required minlength="8"
                               class="w-full bg-transparent border border-primary/10 px-4 py-3 text-sm focus:outline-none focus:border-secondary transition-colors">
                        <button type="submit" class="w-full py-4 bg-primary text-white text-xs tracking-widest uppercase font-bold hover:bg-secondary transition-all duration-300">
                            Create Account &amp; Save Order History
                        </button>
                    </form>
                </div>
                @endif
                @endguest
            @endif
        @endif
    </div>
</section>

@endsection
