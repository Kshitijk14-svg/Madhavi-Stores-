@extends('layouts.app')

@section('title', 'Exchange & Return Policy | Madhavi Stores')
@section('meta_description', 'Our easy exchange and return policy for ethnic wear and home decor purchased from Madhavi Stores.')

@section('content')

<section class="py-16 lg:py-24 bg-background">
  <div class="max-w-3xl mx-auto px-6 lg:px-8">

    <header class="text-center mb-12">
      <span class="text-secondary text-xs tracking-widest uppercase font-bold mb-4 block">Customer Care</span>
      <h1 class="font-display text-4xl lg:text-5xl font-light text-primary">Exchange &amp; Return Policy</h1>
    </header>

    <div class="space-y-8 text-muted text-[15px] font-light leading-relaxed">
      <div>
        <h2 class="font-display text-2xl text-primary mb-3">Easy 30-Day Returns</h2>
        <p>We want you to love your purchase. If something isn't right, you may request a return or exchange within 30 days of delivery, provided the item is unused, unwashed, and in its original condition with tags intact.</p>
      </div>

      <div>
        <h2 class="font-display text-2xl text-primary mb-3">How to Request</h2>
        <p>Contact us at <a href="tel:+918799998770" class="text-secondary hover:underline">+91 87999 98770</a> or
          <a href="https://wa.me/918799998770" target="_blank" rel="noopener" class="text-secondary hover:underline">WhatsApp</a>
          with your order number and reason. Our team will guide you through pickup or drop-off and process your exchange or refund promptly.</p>
      </div>

      <div>
        <h2 class="font-display text-2xl text-primary mb-3">Non-Returnable Items</h2>
        <p>For hygiene and craftsmanship reasons, customised pieces, and certain home decor accents such as torans and latkans may not be eligible for return unless they arrive damaged or defective.</p>
      </div>

      <div>
        <h2 class="font-display text-2xl text-primary mb-3">Refunds</h2>
        <p>Approved refunds are issued to the original payment method within 5–7 business days after the returned item passes our quality check.</p>
      </div>

      <p class="text-xs text-muted/70 pt-4">Last updated {{ date('F Y') }}. Madhavi Stores reserves the right to update this policy at any time.</p>
    </div>

    <div class="text-center mt-14">
      <a href="{{ route('shop') }}" class="btn-primary">Continue Shopping</a>
    </div>

  </div>
</section>

@endsection
