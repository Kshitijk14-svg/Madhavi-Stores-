@extends('layouts.app')
@section('title', 'Lookbook 2026 | Madhavi Stores')

@section('content')

{{-- ── CINEMATIC COVER ──────────────────────────────── --}}
<div class="relative h-screen min-h-[640px] overflow-hidden flex items-end">
  <img src="{{ $lookbook['cover_image'] ?? 'https://images.unsplash.com/photo-1485230895905-31d1d1ebc325?w=1920&q=85&auto=format&fit=crop' }}"
       alt="Lookbook 2026" class="absolute inset-0 w-full h-full object-cover object-top">
  <div class="absolute inset-0 bg-gradient-to-t from-primary/90 via-primary/30 to-transparent"></div>
  <div class="relative wrap pb-20 text-white">
    <p class="label text-secondary mb-6">{{ $lookbook['cover_eyebrow'] ?? '2026 Seasonal Editorial' }}</p>
    <h1 class="font-display font-light text-[clamp(4rem,10vw,11rem)] leading-[0.82]">
      {!! $lookbook['cover_title'] ?? 'The<br><em>Modern</em><br>Muse' !!}
    </h1>
    <div class="mt-10 flex items-center gap-6">
      <div class="w-12 h-px bg-secondary"></div>
      <p class="text-white/60 text-sm font-light">Scroll to explore the story</p>
    </div>
  </div>
</div>


{{-- ── CHAPTER 01 ───────────────────────────────────── --}}
<section class="py-32 bg-background overflow-hidden">
  <div class="wrap">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-24 items-center">
      
      {{-- Text left --}}
      <div class="space-y-8 lg:pr-12">
        <p class="label">{{ $lookbook['chapter1_eyebrow'] ?? 'Chapter 01' }}</p>
        <h2 class="font-display font-light text-[clamp(3rem,5vw,5rem)] leading-[0.88]">{!! $lookbook['chapter1_title'] ?? 'Silk &<br><em>Shadow</em>' !!}</h2>
        <p class="text-muted font-light leading-relaxed text-lg max-w-md">
          {{ $lookbook['chapter1_description'] ?? 'Exploring the delicate interplay between light and hand-woven textures. Our signature silk collection captures the fleeting moments of golden hour, where craft becomes poetry.' }}
        </p>
        <a href="{{ route('collection') }}" class="btn-ghost inline-flex">Shop the Chapter</a>
      </div>

      {{-- Image right (with floating detail) --}}
      <div class="relative">
        <div class="aspect-[3/4] overflow-hidden shadow-2xl">
          <img src="{{ $lookbook['chapter1_image'] ?? 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=1200&q=80&auto=format&fit=crop' }}"
               alt="Silk & Shadow" class="w-full h-full object-cover hover:scale-[1.04] transition-transform duration-[2500ms]">
        </div>
        {{-- Floating inset --}}
        <div class="absolute -bottom-10 -left-6 lg:-left-16 w-44 lg:w-52 aspect-[2/3] overflow-hidden shadow-xl border-4 border-background">
          <img src="{{ $lookbook['chapter1_inset_image'] ?? 'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?w=600&q=80&auto=format&fit=crop' }}"
               alt="Detail" class="w-full h-full object-cover">
        </div>
      </div>
    </div>
  </div>
</section>


{{-- ── FULL BLEED IMAGE ─────────────────────────────── --}}
<div class="h-[60vh] lg:h-[80vh] overflow-hidden">
  <img src="{{ $lookbook['middle_image'] ?? 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=1920&q=80&auto=format&fit=crop' }}"
       alt="Editorial spread" class="w-full h-full object-cover object-center hover:scale-[1.02] transition-transform duration-[3000ms]">
</div>


{{-- ── CHAPTER 02 ───────────────────────────────────── --}}
<section class="py-32 bg-silk overflow-hidden">
  <div class="wrap">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-24 items-center">

      {{-- Image left --}}
      <div class="aspect-[3/4] overflow-hidden shadow-2xl lg:order-1 order-2">
        <img src="{{ $lookbook['chapter2_image'] ?? 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=1200&q=80&auto=format&fit=crop' }}"
             alt="Earthly Grace" class="w-full h-full object-cover hover:scale-[1.04] transition-transform duration-[2500ms]">
      </div>

      {{-- Text right --}}
      <div class="space-y-8 lg:pl-12 lg:order-2 order-1">
        <p class="label">{{ $lookbook['chapter2_eyebrow'] ?? 'Chapter 02' }}</p>
        <h2 class="font-display font-light text-[clamp(3rem,5vw,5rem)] leading-[0.88]">{!! $lookbook['chapter2_title'] ?? 'Earthly<br><em>Grace</em>' !!}</h2>
        <p class="text-muted font-light leading-relaxed text-lg max-w-md">
          {{ $lookbook['chapter2_description'] ?? 'A tribute to natural dyes and organic cotton. This chapter celebrates beauty found in imperfection — the grace of simple silhouettes and the richness of undyed earth.' }}
        </p>
        <a href="{{ route('collection') }}" class="btn-ghost inline-flex">Explore Collection</a>
      </div>
    </div>
  </div>
</section>


{{-- ── IMAGE MOSAIC ─────────────────────────────────── --}}
<section class="py-20 bg-white">
  <div class="wrap">
    <p class="label text-center mb-16">Behind the Scenes</p>
    <div class="grid grid-cols-3 lg:grid-cols-6 gap-3">
      @foreach($lookbook['bts_images'] ?? [
        'https://images.unsplash.com/photo-1596455607563-ad6193f76b17?w=400&q=75',
        'https://images.unsplash.com/photo-1583391733958-6c78278104ba?w=400&q=75',
        'https://images.unsplash.com/photo-1613915617430-8ab0fd7c6baf?w=400&q=75',
        'https://images.unsplash.com/photo-1604928141064-207cea6f5722?w=400&q=75',
        'https://images.unsplash.com/photo-1551488831-00ddcb6c6bd3?w=400&q=75',
        'https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=400&q=75',
      ] as $img)
        <div class="aspect-square overflow-hidden group">
          <img src="{{ $img }}" alt="BTS" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" loading="lazy">
        </div>
      @endforeach
    </div>
  </div>
</section>


{{-- ── CTA ──────────────────────────────────────────── --}}
<section class="py-24 bg-primary text-white text-center">
  <div class="wrap max-w-xl mx-auto">
    <p class="label text-secondary mb-6">Shop the Look</p>
    <h2 class="font-display font-light text-[clamp(2.5rem,4vw,4rem)] leading-none mb-10">Wear the <em>Story</em></h2>
    <a href="{{ route('collection') }}" class="btn-outline border-white/30 text-white hover:bg-white hover:text-primary">
      Discover the Collection
    </a>
  </div>
</section>

@endsection
