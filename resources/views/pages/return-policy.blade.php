@extends('layouts.app')

@section('title', 'Refund, Return, & Cancellation Policy | Madhavi Stores')
@section('meta_description', 'Read about the Refund, Return, & Cancellation Policy at Madhavi Stores.')

@section('content')

<section class="py-16 lg:py-24 bg-background">
  <div class="max-w-3xl mx-auto px-6 lg:px-8">

    <header class="text-center mb-12">
      <span class="text-secondary text-xs tracking-widest uppercase font-bold mb-4 block">Customer Care</span>
      <h1 class="font-display text-4xl lg:text-5xl font-light text-primary">Refund, Return, &amp; Cancellation Policy</h1>
    </header>

    <div class="space-y-8 text-muted text-[15px] font-light leading-relaxed">
      <p class="font-medium text-primary italic">
        Please Note: By placing an order on madhavistores.in, you explicitly agree to the following terms and conditions regarding cancellations, returns, and refunds.
      </p>

      <div>
        <h2 class="font-display text-2xl text-primary mb-3">Strict No-Exception Policy</h2>
        <p class="mb-4">At Madhavi Stores, we maintain a strict No Exchange, No Return, No Refund, and No Cancellation policy.</p>
        <p class="mb-4">Because we deal in premium, curated ethnic apparel, coordsets, and delicate traditional suits, all sales are considered final once an order is placed or purchased.</p>
        
        <ul class="list-disc pl-5 space-y-2 text-muted">
          <li><strong>No Cancellations:</strong> Orders cannot be canceled, modified, or altered once they have been submitted through our system. Please review your garment choices carefully before completing your purchase.</li>
          <li><strong>No Returns:</strong> We do not accept returns on any of our apparel items under any circumstances.</li>
          <li><strong>No Exchanges:</strong> We do not offer size, color, or style exchanges. We strongly recommend referring to our specific size charts and product descriptions before finalizing your order.</li>
          <li><strong>No Refunds:</strong> No monetary refunds or store credits will be issued for any completed transactions.</li>
        </ul>
      </div>

      <div>
        <h2 class="font-display text-2xl text-primary mb-3">Quality Assurance</h2>
        <p>To ensure your complete satisfaction, every suit and set undergoes a rigorous quality check before it is packaged and shipped. We take immense pride in ensuring that our clothing leaves our facility in perfect condition.</p>
      </div>

      <div>
        <h2 class="font-display text-2xl text-primary mb-3">Customer Support</h2>
        <p class="mb-4">If you have any questions regarding the fabric, sizing, or fit of a garment before making your purchase, please feel free to reach out to our team. We are always happy to help you make the right choice!</p>
        <p class="font-medium text-primary">
          Phone: <a href="tel:+918799998770" class="text-secondary hover:underline">+91 87999 98770</a><br>
          Address: Madhavi Stores, GG Road, Opposite Laxmicycle, Nanded, 431601
        </p>
      </div>

      <p class="text-xs text-muted/70 pt-4">Last updated {{ date('F Y') }}. Madhavi Stores reserves the right to update this policy at any time.</p>
    </div>

    <div class="text-center mt-14">
      <a href="{{ route('shop') }}" class="btn-primary">Continue Shopping</a>
    </div>

  </div>
</section>

@endsection
