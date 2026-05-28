@extends('layouts.app')
@section('title', 'Lookbook 2026 | Madhavi Stores')

@section('content')

@foreach($lookbookSections as $section)
  @if(!isset($section['visible']) || $section['visible'])
    
    {{-- ── CINEMATIC COVER ──────────────────────────────── --}}
    @if($section['id'] === 'cover')
      <div class="relative h-screen min-h-[640px] overflow-hidden flex items-end">
        <img src="{{ $lookbook['cover_image'] ?? 'https://images.unsplash.com/photo-1485230895905-31d1d1ebc325?w=1920&q=85&auto=format&fit=crop' }}"
             alt="Lookbook 2026" class="absolute inset-0 w-full h-full object-cover object-top">
        <div class="absolute inset-0 bg-gradient-to-t from-primary/90 via-primary/30 to-transparent"></div>
        <div class="relative wrap pb-20 text-white">
          <p class="label text-secondary mb-6">{!! \App\Models\Setting::format($lookbook['cover_eyebrow'] ?? '2026 Seasonal Editorial') !!}</p>
          <h1 class="font-display font-light text-[clamp(4rem,10vw,11rem)] leading-[0.82]">
            {!! \App\Models\Setting::format($lookbook['cover_title'] ?? "The\n*Modern*\nMuse") !!}
          </h1>
          <div class="mt-10 flex items-center gap-6">
            <div class="w-12 h-px bg-secondary"></div>
            <p class="text-white/60 text-sm font-light">Scroll to explore the story</p>
          </div>
        </div>
      </div>
    @endif

    {{-- ── DYNAMIC CAMPAIGN CHAPTERS CRUD LOOP ──────────────── --}}
    @if($section['id'] === 'chapters')
      @php
        // Retrieve dynamic chapters, fallback to seed chapters if empty
        $chapters = $lookbook['chapters'] ?? [];
        if (empty($chapters)) {
            $chapters = [
                [
                    'eyebrow' => 'Chapter 01',
                    'title' => "Silk &\n*Shadow*",
                    'description' => 'Exploring the delicate interplay between light and hand-woven textures. Our signature silk collection captures the fleeting moments of golden hour, where craft becomes poetry.',
                    'image_url' => $lookbook['chapter1_image'] ?? 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=1200&q=80&auto=format&fit=crop',
                    'inset_image_url' => $lookbook['chapter1_inset_image'] ?? 'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?w=600&q=80&auto=format&fit=crop',
                    'link_url' => route('shop')
                ],
                [
                    'eyebrow' => 'Chapter 02',
                    'title' => "Earthly\n*Grace*",
                    'description' => 'A tribute to natural dyes and organic cotton. This chapter celebrates beauty found in imperfection — the grace of simple silhouettes and the richness of undyed earth.',
                    'image_url' => $lookbook['chapter2_image'] ?? 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=1200&q=80&auto=format&fit=crop',
                    'inset_image_url' => '',
                    'link_url' => route('shop')
                ]
            ];
        }
      @endphp
      @foreach($chapters as $index => $chapter)
        @php
          $isEven = $index % 2 === 0;
        @endphp
        <section class="py-32 {{ $isEven ? 'bg-background' : 'bg-silk' }} overflow-hidden">
          <div class="wrap">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-24 items-center">
              
              {{-- Text column --}}
              <div class="space-y-8 lg:pr-12 {{ !$isEven ? 'lg:order-2 lg:pl-12 lg:pr-0' : 'lg:order-1' }} order-1">
                <p class="label">{!! \App\Models\Setting::format($chapter['eyebrow'] ?? '') !!}</p>
                <h2 class="font-display font-light text-[clamp(3rem,5vw,5rem)] leading-[0.88]">{!! \App\Models\Setting::format($chapter['title'] ?? '') !!}</h2>
                <p class="text-muted font-light leading-relaxed text-lg max-w-md">
                  {!! \App\Models\Setting::format($chapter['description'] ?? '') !!}
                </p>
                <a href="{{ $chapter['link_url'] ?: route('shop') }}" class="btn-ghost inline-flex">Explore Chapter</a>
              </div>

              {{-- Image column --}}
              <div class="relative {{ !$isEven ? 'lg:order-1' : 'lg:order-2' }} order-2">
                <div class="aspect-[3/4] overflow-hidden shadow-2xl">
                  <img src="{{ $chapter['image_url'] ?: 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=1200&q=80&auto=format&fit=crop' }}"
                       alt="Lookbook details" class="w-full h-full object-cover hover:scale-[1.04] transition-transform duration-[2500ms]">
                </div>
                {{-- Floating inset detail --}}
                @if(!empty($chapter['inset_image_url']))
                  <div class="absolute -bottom-10 {{ $isEven ? '-left-6 lg:-left-16' : '-right-6 lg:-right-16' }} w-44 lg:w-52 aspect-[2/3] overflow-hidden shadow-xl border-4 border-background">
                    <img src="{{ $chapter['inset_image_url'] }}"
                         alt="Detail" class="w-full h-full object-cover">
                  </div>
                @endif
              </div>

            </div>
          </div>
        </section>
      @endforeach
    @endif

    {{-- ── FULL BLEED IMAGE ─────────────────────────────── --}}
    @if($section['id'] === 'interlude')
      <div class="h-[60vh] lg:h-[80vh] overflow-hidden">
        <img src="{{ $lookbook['middle_image'] ?? 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=1920&q=80&auto=format&fit=crop' }}"
             alt="Editorial spread" class="w-full h-full object-cover object-center hover:scale-[1.02] transition-transform duration-[3000ms]">
      </div>
    @endif

    {{-- ── IMAGE MOSAIC ─────────────────────────────────── --}}
    @if($section['id'] === 'bts')
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
    @endif

    {{-- ── CTA ──────────────────────────────────────────── --}}
    @if($section['id'] === 'cta')
      <section class="py-24 bg-primary text-white text-center">
        <div class="wrap max-w-xl mx-auto">
          <p class="label text-secondary mb-6">Shop the Look</p>
          <h2 class="font-display font-light text-[clamp(2.5rem,4vw,4rem)] leading-none mb-10">Wear the <em>Story</em></h2>
          <a href="{{ route('shop') }}" class="btn-outline border-white/30 text-white hover:bg-white hover:text-primary">
            Discover the Collection
          </a>
        </div>
      </section>
    @endif

  @endif
@endforeach

@endsection
