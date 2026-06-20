@extends('layouts.app')

@section('content')

<section class="py-20 min-h-[70vh]">
    <div class="max-w-7xl mx-auto px-6 lg:px-12" id="cart-container-wrapper">
        <h1 class="font-display text-5xl font-light text-primary mb-12">Shopping Bag</h1>

        @if($cartItems->isEmpty())
            <div class="text-center py-24 border border-primary/5 bg-silk/10">
                <span class="text-4xl text-secondary">✦</span>
                <p class="text-sm text-muted mt-6 uppercase tracking-widest">Your shopping bag is empty.</p>
                <a href="{{ route('shop') }}" class="btn-primary inline-block mt-8 !py-4 !px-10 uppercase tracking-widest text-[10px] font-semibold">
                    Explore Collections
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-12 gap-10 lg:gap-16">
                
                <!-- Cart Items List -->
                <div class="md:col-span-7 lg:col-span-8">
                    <div class="space-y-10 border-t border-primary/10 pt-10" id="cart-items-list">
                        @foreach($cartItems as $item)
                        <div class="flex flex-row gap-4 lg:gap-8 pb-8 lg:pb-10 border-b border-primary/10 transition-all duration-300" id="cart-item-{{ $item->id }}">
                            <div class="w-24 lg:w-32 aspect-[3/4] flex-shrink-0 bg-background overflow-hidden border border-gray-100">
                                <img src="{{ $item->product->image_url ?? 'https://images.unsplash.com/photo-1583391733958-6c78278104ba?w=400&q=80' }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                            </div>
                            
                            <div class="flex-grow flex flex-col justify-between py-2">
                                <div>
                                    <div class="flex justify-between items-start mb-2">
                                        <h3 class="font-display text-xl font-light text-primary">
                                            <a href="{{ route('product.show', $item->product->id) }}" class="hover:text-secondary transition-colors">{{ $item->product->name }}</a>
                                        </h3>
                                        <span class="text-base font-semibold text-primary" id="item-price-{{ $item->id }}">
                                            ₹{{ number_format($item->product->price * $item->quantity) }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-muted space-y-1 mb-4 uppercase tracking-wider">
                                        @if($item->size)
                                            <p>Size: <span class="text-primary font-semibold">{{ $item->size }}</span></p>
                                        @endif
                                        <p>Unit Price: <span class="text-primary font-semibold">₹{{ number_format($item->product->price) }}</span></p>
                                    </div>
                                </div>
                                
                                <div class="flex justify-between items-center">
                                    {{-- Quantity Selector --}}
                                    <div class="flex items-center border border-primary/10 px-4 py-2 space-x-6">
                                        <button class="cart-qty-btn text-primary hover:text-secondary transition-colors font-bold select-none outline-none" data-id="{{ $item->id }}" data-action="decrease">-</button>
                                        <span class="text-sm font-semibold text-primary select-none w-4 text-center" id="item-qty-{{ $item->id }}">{{ $item->quantity }}</span>
                                        <button class="cart-qty-btn text-primary hover:text-secondary transition-colors font-bold select-none outline-none" data-id="{{ $item->id }}" data-action="increase">+</button>
                                    </div>
                                    
                                    {{-- Remove Button --}}
                                    <button class="cart-remove-btn text-[10px] tracking-widest uppercase font-bold text-muted hover:text-accent transition-colors underline select-none outline-none" data-id="{{ $item->id }}">
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-12">
                        <a href="{{ route('shop') }}" class="text-[10px] tracking-widest uppercase font-bold text-secondary hover:text-primary transition-colors flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 19l-7-7m0 0l-7-7m7-7H3" /></svg>
                            Continue Shopping
                        </a>
                    </div>
                </div>

                <!-- Summary Panel -->
                <div class="md:col-span-5 lg:col-span-4">
                    <div class="bg-background p-8 lg:p-10 sticky top-32 border border-primary/5">
                        <h2 class="text-[10px] tracking-widest uppercase font-bold text-primary mb-8 pb-4 border-b border-primary/10">Order Summary</h2>
                        
                        <div class="space-y-4 mb-8 text-xs uppercase tracking-wider">
                            <div class="flex justify-between text-muted">
                                <span>Bag Subtotal</span>
                                <span class="text-primary font-semibold" id="cart-subtotal">₹{{ number_format($subtotal) }}</span>
                            </div>
                            
                            @if(isset($discount) && $discount > 0)
                                <div class="flex justify-between text-rose-600 font-semibold" id="cart-discount-row">
                                    <span>Discount ({{ session('applied_coupon') }})</span>
                                    <span id="cart-discount">- ₹{{ number_format($discount) }}</span>
                                </div>
                            @endif
                            

                            
                            <div class="pt-4 border-t border-primary/10 flex justify-between text-base font-bold text-primary">
                                <span>Grand Total</span>
                                <span id="cart-total">₹{{ number_format($total) }}</span>
                            </div>
                        </div>

                        {{-- Checkout --}}
                        <a href="{{ route('checkout') }}" class="w-full inline-block py-5 bg-primary text-white text-center text-[10px] tracking-widest uppercase font-bold hover:bg-secondary transition-all duration-300 mb-6">
                            Proceed to Checkout
                        </a>
                        
                        {{-- Coupon application --}}
                        <div class="border-t border-primary/10 pt-6 mt-6">
                            <h3 class="text-[9px] font-bold uppercase tracking-wider text-muted mb-3">Promo Coupon Code</h3>
                            @if(session('applied_coupon'))
                                <div class="flex items-center justify-between bg-emerald-50 border border-emerald-100 p-3 text-xs">
                                    <div class="text-emerald-800">
                                        <span class="font-mono font-bold">{{ session('applied_coupon') }}</span> applied
                                    </div>
                                    <form action="{{ route('coupon.remove') }}" method="POST" id="remove-coupon-form">
                                        @csrf
                                        <button type="submit" class="text-red-600 hover:text-red-800 font-bold text-[10px] uppercase tracking-wider underline">Remove</button>
                                    </form>
                                </div>
                            @else
                                <form action="{{ route('coupon.apply') }}" method="POST" class="flex gap-2" id="apply-coupon-form">
                                    @csrf
                                    <input type="text" name="code" required placeholder="ENTER CODE" class="text-xs uppercase tracking-wider font-mono border border-primary/15 px-3 py-2 bg-white outline-none focus:border-primary flex-grow">
                                    <button type="submit" class="btn-primary !py-2 !px-4 text-[9px] font-bold uppercase tracking-wider">Apply</button>
                                </form>
                            @endif
                        </div>
                        
                        <div class="text-[9px] text-muted text-center leading-relaxed mt-6 pt-6 border-t border-primary/5">
                            Shipping, taxes, and coupons will be finalized during secure checkout processing.
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>

@endsection
