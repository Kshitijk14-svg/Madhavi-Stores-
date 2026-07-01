@extends('layouts.app')

@section('title', 'Terms and Conditions | Madhavi Stores')
@section('meta_description', 'Terms and Conditions for using the Madhavi Stores website and placing orders.')

@section('content')

<section class="py-16 lg:py-24 bg-background">
  <div class="max-w-3xl mx-auto px-6 lg:px-8">

    <header class="text-center mb-12">
      <span class="text-secondary text-xs tracking-widest uppercase font-bold mb-4 block">Customer Care</span>
      <h1 class="font-display text-4xl lg:text-5xl font-light text-primary">Terms &amp; Conditions</h1>
    </header>

    <div class="space-y-8 text-muted text-[15px] font-light leading-relaxed">
      <div>
        <h2 class="font-display text-2xl text-primary mb-3">1. Introduction</h2>
        <p>Welcome to Madhavi Stores (madhavistores.in). These Terms and Conditions govern your use of our website and purchases of our products. By browsing this site or placing an order, you agree to comply with and be bound by these terms.</p>
      </div>

      <div>
        <h2 class="font-display text-2xl text-primary mb-3">2. Account Registration &amp; Security</h2>
        <p>To access certain features or place an order, you may need to register an account. You are responsible for maintaining the confidentiality of your account credentials and password. Any actions taken under your account are your sole responsibility.</p>
      </div>

      <div>
        <h2 class="font-display text-2xl text-primary mb-3">3. Product Descriptions &amp; Pricing</h2>
        <p>We focus exclusively on high-quality, curated retail women's ethnic and contemporary wear. While we make every effort to display product colors, fabrics, and descriptions as accurately as possible, slight variations may occur due to screen settings or artisanal dye batches. Prices and availability are subject to change without notice.</p>
      </div>

      <div>
        <h2 class="font-display text-2xl text-primary mb-3">4. Orders &amp; Payments</h2>
        <p>We reserve the right to refuse or cancel any order for reasons such as product unavailability, inaccuracies in pricing, or issues identified by our fraud prevention department. All payments are processed securely through our verified payment partners, and we do not store raw card/banking details on our servers.</p>
      </div>

      <div>
        <h2 class="font-display text-2xl text-primary mb-3">5. Cancellation &amp; Refund Policy</h2>
        <p>As specified in our <a href="{{ route('return.policy') }}" class="text-secondary hover:underline">Refund, Return, &amp; Cancellation Policy</a>, all sales are final once purchased. We maintain a strict No Cancellation, No Exchange, No Return, and No Refund policy with no exceptions.</p>
      </div>

      <div>
        <h2 class="font-display text-2xl text-primary mb-3">6. Governing Law</h2>
        <p>These Terms and Conditions and any transactions concluded on this website are governed by the laws of India. Any disputes arising out of or related to your use of this website shall be subject to the exclusive jurisdiction of the courts of Nanded, Maharashtra.</p>
      </div>

      <div>
        <h2 class="font-display text-2xl text-primary mb-3">7. Contact Information</h2>
        <p>For any inquiries regarding our terms, services, or products, please reach out to us:</p>
        <p class="font-medium text-primary mt-2">
          Phone: <a href="tel:+918799998770" class="text-secondary hover:underline">+91 87999 98770</a><br>
          Address: Madhavi Stores, GG Road, Opposite Laxmicycle, Nanded, 431601
        </p>
      </div>

      <p class="text-xs text-muted/70 pt-4">Last updated {{ date('F Y') }}. Madhavi Stores reserves the right to modify these terms at any time.</p>
    </div>

    <div class="text-center mt-14">
      <a href="{{ route('shop') }}" class="btn-primary">Continue Shopping</a>
    </div>

  </div>
</section>

@endsection
