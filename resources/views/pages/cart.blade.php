@extends('layouts.app')

@section('content')

<section class="py-20">
    <div class="max-w-7xl mx-auto px-6 lg:px-12">
        <h1 class="font-display text-5xl font-light text-primary mb-12">Shopping Bag</h1>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-16">
            
            <!-- Cart Items -->
            <div class="lg:col-span-8">
                <div class="space-y-10 border-t border-primary/10 pt-10">
                    @for($i = 0; $i < 2; $i++)
                    <div class="flex flex-col sm:flex-row gap-8 pb-10 border-b border-primary/10">
                        <div class="w-32 aspect-[3/4] flex-shrink-0 bg-background overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1583391733958-6c78278104ba?w=400&q=80" alt="Product" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-grow flex flex-col justify-between py-2">
                            <div>
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="font-display text-xl font-light text-primary">Ivory Chanderi Kurta</h3>
                                    <span class="text-lg font-medium">₹4,500</span>
                                </div>
                                <div class="text-sm text-muted space-y-1 mb-4">
                                    <p>Size: M</p>
                                    <p>Color: Ivory White</p>
                                </div>
                            </div>
                            <div class="flex justify-between items-center">
                                <div class="flex items-center border border-primary/10 px-4 py-2 space-x-6">
                                    <button class="text-primary hover:text-secondary transition-colors">-</button>
                                    <span class="text-sm font-medium">1</span>
                                    <button class="text-primary hover:text-secondary transition-colors">+</button>
                                </div>
                                <button class="text-xs tracking-widest uppercase font-medium text-muted hover:text-accent transition-colors underline">Remove</button>
                            </div>
                        </div>
                    </div>
                    @endfor
                </div>

                <div class="mt-12">
                    <a href="{{ route('collection') }}" class="text-sm tracking-widest uppercase font-medium text-secondary hover:text-primary transition-colors flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 19l-7-7m0 0l-7-7m7-7H3" /></svg>
                        Continue Shopping
                    </a>
                </div>
            </div>

            <!-- Summary -->
            <div class="lg:col-span-4">
                <div class="bg-background p-8 lg:p-10 sticky top-32">
                    <h2 class="text-xs tracking-widest uppercase font-bold text-primary mb-8 pb-4 border-b border-primary/10">Order Summary</h2>
                    
                    <div class="space-y-4 mb-8">
                        <div class="flex justify-between text-sm text-muted">
                            <span>Subtotal</span>
                            <span>₹9,000</span>
                        </div>
                        <div class="flex justify-between text-sm text-muted">
                            <span>Shipping</span>
                            <span>Calculated at next step</span>
                        </div>
                        <div class="flex justify-between text-sm text-muted">
                            <span>Tax</span>
                            <span>₹1,620</span>
                        </div>
                        <div class="pt-4 border-t border-primary/10 flex justify-between text-lg font-medium text-primary">
                            <span>Total</span>
                            <span>₹10,620</span>
                        </div>
                    </div>

                    <button class="w-full py-5 bg-primary text-white text-xs tracking-widest uppercase font-bold hover:bg-secondary transition-all duration-300 mb-6">
                        Proceed to Checkout
                    </button>
                    
                    <div class="text-[10px] text-muted text-center leading-relaxed">
                        Shipping, taxes, and discounts will be calculated during checkout. Secure payment processing.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
