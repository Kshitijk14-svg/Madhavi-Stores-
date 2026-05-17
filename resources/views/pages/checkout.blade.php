@extends('layouts.app')

@section('content')

<section class="py-20">
    <div class="max-w-5xl mx-auto px-6 lg:px-12">
        <h1 class="font-display text-4xl font-light text-primary mb-12 text-center italic">Checkout</h1>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16">
            
            <!-- Checkout Form -->
            <div class="space-y-12">
                <!-- Shipping Info -->
                <section>
                    <h2 class="text-xs tracking-widest uppercase font-bold text-primary mb-8 flex items-center">
                        <span class="w-6 h-6 rounded-full bg-primary text-white flex items-center justify-center text-[10px] mr-3">1</span>
                        Shipping Information
                    </h2>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <input type="text" placeholder="First Name" class="w-full bg-transparent border border-primary/10 px-4 py-3 text-sm focus:outline-none focus:border-secondary transition-colors">
                            <input type="text" placeholder="Last Name" class="w-full bg-transparent border border-primary/10 px-4 py-3 text-sm focus:outline-none focus:border-secondary transition-colors">
                        </div>
                        <input type="email" placeholder="Email Address" class="w-full bg-transparent border border-primary/10 px-4 py-3 text-sm focus:outline-none focus:border-secondary transition-colors">
                        <input type="text" placeholder="Address" class="w-full bg-transparent border border-primary/10 px-4 py-3 text-sm focus:outline-none focus:border-secondary transition-colors">
                        <div class="grid grid-cols-2 gap-4">
                            <input type="text" placeholder="City" class="w-full bg-transparent border border-primary/10 px-4 py-3 text-sm focus:outline-none focus:border-secondary transition-colors">
                            <input type="text" placeholder="Postal Code" class="w-full bg-transparent border border-primary/10 px-4 py-3 text-sm focus:outline-none focus:border-secondary transition-colors">
                        </div>
                    </div>
                </section>

                <!-- Payment Method -->
                <section>
                    <h2 class="text-xs tracking-widest uppercase font-bold text-primary mb-8 flex items-center">
                        <span class="w-6 h-6 rounded-full bg-primary text-white flex items-center justify-center text-[10px] mr-3">2</span>
                        Payment Method
                    </h2>
                    <div class="space-y-4">
                        <label class="flex items-center p-4 border border-primary/20 bg-background cursor-pointer hover:border-secondary transition-colors group">
                            <input type="radio" name="payment" checked class="accent-secondary">
                            <span class="ml-4 text-sm font-medium">Credit / Debit Card</span>
                        </label>
                        <label class="flex items-center p-4 border border-primary/10 cursor-pointer hover:border-secondary transition-colors group">
                            <input type="radio" name="payment" class="accent-secondary">
                            <span class="ml-4 text-sm font-medium">UPI / Net Banking</span>
                        </label>
                        <label class="flex items-center p-4 border border-primary/10 cursor-pointer hover:border-secondary transition-colors group">
                            <input type="radio" name="payment" class="accent-secondary">
                            <span class="ml-4 text-sm font-medium">Cash on Delivery</span>
                        </label>
                    </div>
                </section>

                <button class="w-full py-5 bg-primary text-white text-xs tracking-widest uppercase font-bold hover:bg-secondary transition-all duration-300">
                    Complete Order
                </button>
            </div>

            <!-- Order Summary Sidebar -->
            <aside>
                <div class="bg-background p-8 lg:p-10">
                    <h3 class="text-xs tracking-widest uppercase font-bold text-primary mb-8 border-b border-primary/10 pb-4">Your Order</h3>
                    <div class="space-y-6 mb-8">
                        <div class="flex gap-4">
                            <div class="w-16 h-20 bg-white overflow-hidden flex-shrink-0">
                                <img src="https://images.unsplash.com/photo-1583391733958-6c78278104ba?w=200&q=80" alt="Item" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-grow flex flex-col justify-center">
                                <p class="text-sm font-medium">Ivory Chanderi Kurta</p>
                                <p class="text-xs text-muted">Size: M × 1</p>
                                <p class="text-sm mt-1">₹4,500</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-3 pt-6 border-t border-primary/10">
                        <div class="flex justify-between text-sm text-muted">
                            <span>Subtotal</span>
                            <span>₹9,000</span>
                        </div>
                        <div class="flex justify-between text-sm text-muted">
                            <span>Shipping</span>
                            <span class="text-secondary uppercase text-[10px] font-bold">Free</span>
                        </div>
                        <div class="flex justify-between text-lg font-medium text-primary pt-3 border-t border-primary/10 mt-3">
                            <span>Total</span>
                            <span>₹10,620</span>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>

@endsection
