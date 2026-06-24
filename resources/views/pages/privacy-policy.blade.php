@extends('layouts.app')

@section('title', 'Privacy Policy | Madhavi Stores')
@section('meta_description', 'How Madhavi Stores collects, uses, and protects your personal information.')

@section('content')

<section class="py-16 lg:py-24 bg-background">
  <div class="max-w-3xl mx-auto px-6 lg:px-8">

    <header class="text-center mb-12">
      <span class="text-secondary text-xs tracking-widest uppercase font-bold mb-4 block">Customer Care</span>
      <h1 class="font-display text-4xl lg:text-5xl font-light text-primary">Privacy Policy</h1>
    </header>

    <div class="space-y-8 text-muted text-[15px] font-light leading-relaxed">
      <div>
        <h2 class="font-display text-2xl text-primary mb-3">Information We Collect</h2>
        <p>We collect the details you provide when you create an account, place an order, or contact us — such as your name, phone number, email, and delivery address. Payment information is processed securely by our payment partner and is never stored on our servers.</p>
      </div>

      <div>
        <h2 class="font-display text-2xl text-primary mb-3">How We Use It</h2>
        <p>Your information is used solely to process orders, arrange delivery, provide customer support, and — only with your consent — to share offers and new collection updates.</p>
      </div>

      <div>
        <h2 class="font-display text-2xl text-primary mb-3">Data Protection</h2>
        <p>We apply reasonable technical and organisational measures to safeguard your data. We do not sell or rent your personal information to third parties.</p>
      </div>

      <div>
        <h2 class="font-display text-2xl text-primary mb-3">Your Choices</h2>
        <p>You may request access to, correction of, or deletion of your personal data at any time by contacting us at
          <a href="tel:+918799998770" class="text-secondary hover:underline">+91 87999 98770</a> or
          <a href="https://wa.me/918799998770" target="_blank" rel="noopener" class="text-secondary hover:underline">WhatsApp</a>.</p>
      </div>

      <p class="text-xs text-muted/70 pt-4">Last updated {{ date('F Y') }}. Madhavi Stores reserves the right to update this policy at any time.</p>
    </div>

    <div class="text-center mt-14">
      <a href="{{ route('shop') }}" class="btn-primary">Continue Shopping</a>
    </div>

  </div>
</section>

@endsection
