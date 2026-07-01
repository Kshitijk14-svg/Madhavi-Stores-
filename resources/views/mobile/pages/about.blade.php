@extends('mobile.layouts.app')
@section('title', 'Our Story — Madhavi Stores')

@section('content')
<div class="pb-24">

  {{-- Header (No Image) --}}
  <section class="py-12 bg-background text-center border-b border-gray-100">
    <div class="px-6">
      <p class="eyebrow" style="color:var(--secondary);margin-bottom:8px;font-size:11px;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;">Our Story</p>
      <h1 style="font-family:'Cormorant Garamond',serif;font-size:2.25rem;font-weight:300;line-height:1.2;color:var(--primary);margin-bottom:4px;">Welcome to Madhavi Stores</h1>
      <p style="font-size:10px;font-weight:600;letter-spacing:0.15em;text-transform:uppercase;color:var(--secondary);">The Ethnic Brand</p>
    </div>
  </section>

  {{-- Introduction --}}
  <section class="px-6 py-10">
    <p style="font-size:15px;font-weight:300;line-height:1.7;color:var(--primary);text-align:center;">
      At <strong style="font-weight:600;color:var(--secondary);">Madhavi Stores</strong>, we believe that clothing is a powerful celebration of culture, modern style, and timeless elegance. Located in the heart of Nanded at GG Road (Opposite Laxmicycle), we are dedicated to bringing you a curated collection of premium women's ethnic and contemporary wear.
    </p>
  </section>

  {{-- Collection --}}
  <section class="bg-background px-6 py-10 border-t border-b border-gray-100">
    <div class="text-center mb-8">
      <p class="eyebrow" style="color:var(--secondary);margin-bottom:4px;font-size:11px;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;">Curated Design</p>
      <h2 style="font-family:'Cormorant Garamond',serif;font-size:1.75rem;font-weight:300;">Our Collection</h2>
      <p style="font-size:13px;font-weight:300;color:var(--muted);margin-top:8px;line-height:1.5;">
        We focus exclusively on high-quality, beautifully crafted outfits designed to make you stand out on every occasion.
      </p>
    </div>

    <div class="space-y-6">
      <div style="background:#fff;padding:24px;border:1px solid rgba(0,0,0,0.05);text-align:center;">
        <span style="font-family:'Cormorant Garamond',serif;font-size:1.35rem;font-weight:400;color:var(--primary);display:block;margin-bottom:6px;">Kurti Sets</span>
        <p style="font-size:12px;font-weight:300;color:var(--muted);line-height:1.6;">Elegant, perfectly coordinated traditional sets that blend heritage with everyday comfort.</p>
      </div>

      <div style="background:#fff;padding:24px;border:1px solid rgba(0,0,0,0.05);text-align:center;">
        <span style="font-family:'Cormorant Garamond',serif;font-size:1.35rem;font-weight:400;color:var(--primary);display:block;margin-bottom:6px;">Coordsets</span>
        <p style="font-size:12px;font-weight:300;color:var(--muted);line-height:1.6;">Trendy, contemporary matching sets designed for the modern woman who loves effortless style.</p>
      </div>

      <div style="background:#fff;padding:24px;border:1px solid rgba(0,0,0,0.05);text-align:center;">
        <span style="font-family:'Cormorant Garamond',serif;font-size:1.35rem;font-weight:400;color:var(--primary);display:block;margin-bottom:6px;">Bandhani Suits</span>
        <p style="font-size:12px;font-weight:300;color:var(--muted);line-height:1.6;">Timeless, vibrant traditional suits featuring classic Bandhani print artistry.</p>
      </div>
    </div>
  </section>

  {{-- Philosophy --}}
  <section class="px-6 py-10 text-center">
    <p class="eyebrow" style="color:var(--secondary);margin-bottom:8px;font-size:11px;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;">Quality Assurance</p>
    <h2 style="font-family:'Cormorant Garamond',serif;font-size:1.75rem;font-weight:300;margin-bottom:12px;">Our Philosophy</h2>
    <p style="font-size:13px;font-weight:300;color:var(--muted);line-height:1.7;">
      As an authentic apparel brand, our goal is simple: to offer premium, meticulously selected outfits that deliver the rich heritage of Indian textiles with a modern touch. Every single piece at Madhavi Stores undergoes a strict quality check before it reaches you, ensuring your shopping experience is nothing short of exceptional.
    </p>
  </section>
</div>
@endsection
