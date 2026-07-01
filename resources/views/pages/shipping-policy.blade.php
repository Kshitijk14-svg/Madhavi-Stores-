@extends('layouts.app')

@section('title', 'Shipping Policy | Madhavi Stores')
@section('meta_description', 'Shipping timelines, charges, and delivery information for orders placed with Madhavi Stores.')

@section('content')

<section class="py-16 lg:py-24 bg-background">
  <div class="max-w-3xl mx-auto px-6 lg:px-8">

    <header class="text-center mb-12">
      <span class="text-secondary text-xs tracking-widest uppercase font-bold mb-4 block">Customer Care</span>
      <h1 class="font-display text-4xl lg:text-5xl font-light text-primary">Shipping Policy</h1>
    </header>

    <div class="space-y-8 text-muted text-[15px] font-light leading-relaxed">
      <div>
        <h2 class="font-display text-2xl text-primary mb-3">Processing Time</h2>
        <p>Orders are processed within 1–3 business days. Once dispatched, you will receive a tracking link via SMS, WhatsApp, or email. Festive and sale periods may add a short delay.</p>
      </div>

      <div>
        <h2 class="font-display text-2xl text-primary mb-3">Delivery Timelines</h2>
        <p>Orders are delivered within a minimum of 4–5 days and a maximum of 5–6 days depending on your location. Remote pin codes may require additional time.</p>
      </div>

      <div>
        <h2 class="font-display text-2xl text-primary mb-3">Shipping Charges</h2>
        <p>We are pleased to offer free shipping on all orders all over India. There are no shipping charges, regardless of the order value or payment method.</p>
      </div>

      <div>
        <h2 class="font-display text-2xl text-primary mb-3">Need Help?</h2>
        <p>For any questions about your shipment, reach us at
          <a href="tel:+918799998770" class="text-secondary hover:underline">+91 87999 98770</a> or
          <a href="https://wa.me/918799998770" target="_blank" rel="noopener" class="text-secondary hover:underline">chat on WhatsApp</a>.
          You may also visit us at <strong class="text-primary font-medium">Opposite Laxmicycle, GG Road, Nanded, Maharashtra 431601</strong>.
        </p>
      </div>

      <p class="text-xs text-muted/70 pt-4">Last updated {{ date('F Y') }}. Madhavi Stores reserves the right to update this policy at any time.</p>
    </div>

  </div>
</section>

@endsection
