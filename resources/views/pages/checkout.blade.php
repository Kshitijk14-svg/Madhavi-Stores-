@extends('layouts.app')

@section('content')

<section class="py-20">
    <div class="max-w-5xl mx-auto px-6 lg:px-12">
        <h1 class="font-display text-4xl font-light text-primary mb-12 text-center italic">Checkout</h1>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16">
            
            <!-- Checkout Form -->
            <div>
                <form id="checkout-form" action="{{ route('checkout.store') }}" method="POST" class="space-y-12">
                    @csrf
                    <!-- Shipping Info -->
                    <section>
                        <h2 class="text-xs tracking-widest uppercase font-bold text-primary mb-8 flex items-center">
                            <span class="w-6 h-6 rounded-full bg-primary text-white flex items-center justify-center text-[10px] mr-3">1</span>
                            Shipping Information
                        </h2>
                        @php
                            $nameParts = explode(' ', auth()->user()->name ?? '', 2);
                            $firstName = $nameParts[0] ?? '';
                            $lastName = $nameParts[1] ?? '';
                            $email = auth()->user()->email ?? '';
                        @endphp
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <input type="text" name="first_name" id="first_name" placeholder="First Name" value="{{ $firstName }}" required class="w-full bg-transparent border border-primary/10 px-4 py-3 text-sm focus:outline-none focus:border-secondary transition-colors">
                                <input type="text" name="last_name" id="last_name" placeholder="Last Name" value="{{ $lastName }}" required class="w-full bg-transparent border border-primary/10 px-4 py-3 text-sm focus:outline-none focus:border-secondary transition-colors">
                            </div>
                            <input type="email" name="email" id="email" placeholder="Email Address" value="{{ $email }}" required class="w-full bg-transparent border border-primary/10 px-4 py-3 text-sm focus:outline-none focus:border-secondary transition-colors">
                            <input type="text" name="address" id="address" placeholder="Address" required class="w-full bg-transparent border border-primary/10 px-4 py-3 text-sm focus:outline-none focus:border-secondary transition-colors">
                            <div class="grid grid-cols-2 gap-4">
                                <input type="text" name="city" id="city" placeholder="City" required class="w-full bg-transparent border border-primary/10 px-4 py-3 text-sm focus:outline-none focus:border-secondary transition-colors">
                                <input type="text" name="postal_code" id="postal_code" placeholder="Postal Code" required class="w-full bg-transparent border border-primary/10 px-4 py-3 text-sm focus:outline-none focus:border-secondary transition-colors">
                            </div>
                        </div>
                    </section>

                    <!-- Payment -->
                    <section>
                        <h2 class="text-xs tracking-widest uppercase font-bold text-primary mb-4 flex items-center">
                            <span class="w-6 h-6 rounded-full bg-primary text-white flex items-center justify-center text-[10px] mr-3">2</span>
                            Payment
                        </h2>
                        <p class="text-xs text-muted leading-relaxed pl-9">
                            You'll choose how to pay — Card, UPI, Net Banking or Wallet — in the secure Razorpay window after you place your order.
                        </p>
                    </section>

                    <button type="submit" id="submit-btn" class="w-full py-5 bg-primary text-white text-xs tracking-widest uppercase font-bold hover:bg-secondary transition-all duration-300">
                        Pay Securely
                    </button>
                </form>
            </div>

            <!-- Order Summary Sidebar -->
            <aside>
                <div class="bg-background p-8 lg:p-10">
                    <h3 class="text-xs tracking-widest uppercase font-bold text-primary mb-8 border-b border-primary/10 pb-4">Your Order</h3>
                    <div class="space-y-6 mb-8">
                        @foreach($cartItems as $item)
                        <div class="flex gap-4">
                            <div class="w-16 h-20 bg-white overflow-hidden flex-shrink-0 border border-primary/10">
                                <img src="{{ $item->product->image_url ?? 'https://images.unsplash.com/photo-1583391733958-6c78278104ba?w=200&q=80' }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-grow flex flex-col justify-center">
                                <p class="text-sm font-medium">{{ $item->product->name }}</p>
                                <p class="text-xs text-muted">Size: {{ $item->size ?? 'Standard' }} &times; {{ $item->quantity }}</p>
                                <p class="text-sm mt-1">₹{{ number_format($item->product->final_price * $item->quantity) }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <div class="space-y-3 pt-6 border-t border-primary/10">
                        <div class="flex justify-between text-sm text-muted">
                            <span>Subtotal</span>
                            <span>₹{{ number_format($subtotal) }}</span>
                        </div>
                        @if($discount > 0)
                        <div class="flex justify-between text-sm text-rose-600">
                            <span>Discount ({{ $coupon->code ?? 'Coupon' }})</span>
                            <span>-₹{{ number_format($discount) }}</span>
                        </div>
                        @endif

                        <div class="flex justify-between text-lg font-medium text-primary pt-3 border-t border-primary/10 mt-3">
                            <span>Total</span>
                            <span>₹{{ number_format($total) }}</span>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>

<!-- Mock Payment Modal for Sandboxed/Test Environments -->
<div id="mock-payment-modal" class="fixed inset-0 z-[100] flex items-center justify-center bg-primary/40 backdrop-blur-sm hidden">
    <div class="bg-background border border-secondary/20 p-8 max-w-sm w-full text-center space-y-6">
        <h3 class="font-display text-2xl font-light text-primary italic">Secure Sandbox Payment</h3>
        <p class="text-xs text-muted tracking-wide uppercase">Razorpay is in Mock/Test Mode. Simulate a transaction outcome:</p>
        <div class="flex gap-4 justify-center">
            <button id="mock-payment-success" class="px-6 py-3 bg-primary text-white text-[10px] tracking-widest uppercase font-bold hover:bg-secondary transition-all">Success</button>
            <button id="mock-payment-fail" class="px-6 py-3 bg-red-950 text-red-200 border border-red-500/20 text-[10px] tracking-widest uppercase font-bold hover:bg-red-900 transition-all">Cancel</button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    (function() {
        function initCheckout() {
            const form = document.getElementById('checkout-form');
            if (!form || form.dataset.initialized) return;
            form.dataset.initialized = 'true';

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = document.getElementById('submit-btn');
                const originalText = submitBtn.innerHTML;
                
                // Form validation check
                const fields = ['first_name', 'last_name', 'email', 'address', 'city', 'postal_code'];
                let hasError = false;
                fields.forEach(field => {
                    const input = document.getElementById(field);
                    if (!input || !input.value.trim()) {
                        hasError = true;
                        input.classList.add('border-rose-500');
                    } else {
                        input.classList.remove('border-rose-500');
                    }
                });

                if (hasError) {
                    showToast("Please fill in all shipping fields.", "error");
                    return;
                }

                // Disable submit button
                submitBtn.disabled = true;
                submitBtn.innerHTML = `<span class="inline-block animate-spin mr-2">✦</span> Processing...`;

                const formData = new FormData(form);
                const data = {};
                formData.forEach((value, key) => data[key] = value);

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json().catch(() => ({
                    success: false,
                    message: 'Server returned an unreadable response (status ' + response.status + ').'
                })))
                .then(res => {
                    if (res.errors) {
                        const errMsgs = Object.values(res.errors).flat().join(' ');
                        showToast(errMsgs || "Validation failed.", "error");
                        enableBtn();
                        return;
                    }

                    if (!res.success) {
                        showToast(res.message || "Failed to initiate order.", "error");
                        enableBtn();
                        return;
                    }

                    if (res.is_mock) {
                        showMockModal(res, data);
                    } else {
                        launchRazorpay(res, data);
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast("An unexpected error occurred during checkout.", "error");
                    enableBtn();
                });

                function enableBtn() {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }

                function launchRazorpay(rzpData, customerData) {
                    const options = {
                        "key": rzpData.razorpay_key,
                        "amount": rzpData.amount,
                        "currency": rzpData.currency,
                        "name": rzpData.company_name,
                        "description": "Secure Payment - Madhavi Stores",
                        "order_id": rzpData.razorpay_order_id,
                        "handler": function (response) {
                            verifyServerPayment(response, customerData);
                        },
                        "prefill": {
                            "name": rzpData.customer.name,
                            "email": rzpData.customer.email,
                        },
                        "theme": {
                            "color": "#181818"
                        },
                        "modal": {
                            "ondismiss": function() {
                                showToast("Payment cancelled by customer.", "error");
                                enableBtn();
                            }
                        }
                    };

                    try {
                        const rzp = new Razorpay(options);
                        rzp.on('payment.failed', function (response) {
                            showToast("Payment failed: " + response.error.description, "error");
                            enableBtn();
                        });
                        rzp.open();
                    } catch (e) {
                        console.error("Razorpay SDK error", e);
                        showToast("Failed to load Razorpay payment window.", "error");
                        enableBtn();
                    }
                }

                function showMockModal(rzpData, customerData) {
                    const modal = document.getElementById('mock-payment-modal');
                    if (!modal) return;
                    modal.classList.remove('hidden');

                    const successBtn = document.getElementById('mock-payment-success');
                    const failBtn = document.getElementById('mock-payment-fail');

                    const newSuccessBtn = successBtn.cloneNode(true);
                    const newFailBtn = failBtn.cloneNode(true);
                    successBtn.parentNode.replaceChild(newSuccessBtn, successBtn);
                    failBtn.parentNode.replaceChild(newFailBtn, failBtn);

                    newSuccessBtn.addEventListener('click', function() {
                        modal.classList.add('hidden');
                        verifyServerPayment({
                            razorpay_order_id: rzpData.razorpay_order_id,
                            razorpay_payment_id: 'pay_mock_' + Math.random().toString(36).substring(2, 12),
                            razorpay_signature: 'sig_mock_' + Math.random().toString(36).substring(2, 12)
                        }, customerData);
                    });

                    newFailBtn.addEventListener('click', function() {
                        modal.classList.add('hidden');
                        showToast("Mock payment transaction cancelled.", "error");
                        enableBtn();
                    });
                }

                function verifyServerPayment(rzpResponse, customerData) {
                    submitBtn.innerHTML = `<span class="inline-block animate-spin mr-2">✦</span> Verifying Payment...`;
                    
                    const verifyData = Object.assign({}, customerData, {
                        razorpay_order_id: rzpResponse.razorpay_order_id,
                        razorpay_payment_id: rzpResponse.razorpay_payment_id,
                        razorpay_signature: rzpResponse.razorpay_signature
                    });

                    fetch('/checkout/verify', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(verifyData)
                    })
                    .then(response => response.json())
                    .then(res => {
                        if (res.success) {
                            showToast(res.message || "Order placed successfully!", "success");
                            setTimeout(() => {
                                if (typeof navigateToPage === 'function') {
                                    navigateToPage(res.redirect);
                                } else {
                                    window.location.href = res.redirect;
                                }
                            }, 1000);
                        } else {
                            showToast(res.message || "Payment verification failed.", "error");
                            enableBtn();
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        showToast("Verification request failed.", "error");
                        enableBtn();
                    });
                }
            });
        }

        // Initialize immediately
        initCheckout();

        // Re-initialize for dynamic PJAX swaps
        if (typeof bindDynamicContentListeners === 'function') {
            const originalBind = bindDynamicContentListeners;
            bindDynamicContentListeners = function() {
                originalBind();
                initCheckout();
            };
        }
    })();
</script>
@endsection
