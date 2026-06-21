@extends('mobile.layouts.app')
@section('title', 'Madhavi Stores | Quiet Luxury. Indian Heritage.')

@section('content')

@foreach($homepageSections as $section)
  @if(!isset($section['visible']) || $section['visible'])
    
    @if($section['id'] === 'hero')
      {{-- ═══ 1. MOBILE HERO ═══ --}}
      <section style="position:relative;overflow:hidden;">
        <div class="swiper hero-swiper">
          <div class="swiper-wrapper">
            @foreach($heroSlides as $i => $slide)
              <div class="swiper-slide">
                <div class="hero-slide" style="height:calc(var(--vh,1vh)*70);">
                  <img src="{{ $slide['mobile_image_url'] ?? $slide['image_url'] }}" alt="{{ strip_tags($slide['title']) }}" style="width:100%;height:100%;object-fit:cover;object-position:center;">
                  <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(24,24,24,0.85) 0%,rgba(24,24,24,0.2) 60%,transparent);"></div>
                  <div style="position:absolute;inset:0;display:flex;align-items:flex-end;padding:32px 24px;">
                    <div style="color:#fff;width:100%;text-align:left;">
                      <p class="eyebrow" style="color:rgba(184,152,110,0.9);margin-bottom:12px;">{!! \App\Models\Setting::format($slide['eyebrow']) !!}</p>
                      <h1 style="font-family:'Cormorant Garamond',serif;font-size:2.75rem;font-weight:300;line-height:0.95;margin-bottom:16px;">
                        {!! \App\Models\Setting::format($slide['title']) !!}
                      </h1>
                      @if(!empty($slide['subtitle']))
                        <p style="color:rgba(255,255,255,0.75);font-size:14px;font-weight:300;line-height:1.5;margin-bottom:24px;">
                          {!! \App\Models\Setting::format($slide['subtitle']) !!}
                        </p>
                      @endif
                      <div style="display:flex;flex-direction:column;gap:12px;">
                        @if(!empty($slide['button_text']))
                          <a href="{{ $slide['button_url'] ?: route('shop') }}" class="btn-white text-center">{{ $slide['button_text'] }}</a>
                        @endif
                        @if(!empty($slide['has_second_button']) && !empty($slide['second_button_text']))
                          <a href="{{ $slide['second_button_url'] ?: route('shop') }}" style="display:block;text-align:center;padding:14px 28px;border:1px solid rgba(255,255,255,0.3);color:#fff;font-size:11px;font-weight:700;letter-spacing:0.25em;text-transform:uppercase;">{{ $slide['second_button_text'] }}</a>
                        @endif
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
          <div class="swiper-pagination hero-dots" style="position:absolute;bottom:16px;left:50%;transform:translateX(-50%);z-index:10;display:flex;gap:6px;"></div>
        </div>
      </section>
    @endif

    @if($section['id'] === 'categories')
      {{-- ═══ 2. MOBILE CATEGORIES SCROLLER ═══ --}}
      <section style="padding:32px 0 24px;border-bottom:1px solid #f0f0f0;">
        <div class="px-4">
          <p class="eyebrow" style="margin-bottom:16px;">Shop by Category</p>
          <div class="flex overflow-x-auto gap-4 pb-4 snap-x hide-scrollbar" style="-webkit-overflow-scrolling:touch;scrollbar-width:none;">
            @foreach($categories as $category)
              <a href="{{ route('shop', ['category'=>$category->slug]) }}" class="snap-start flex flex-col items-center gap-2 min-w-[72px]">
                <div class="w-16 h-16 rounded-full overflow-hidden border border-gray-100 shadow-sm shrink-0">
                  <img src="{{ $category->image_url }}" alt="{{ $category->name }}" loading="lazy" class="w-full h-full object-cover">
                </div>
                <span class="text-[10px] font-bold tracking-wider uppercase text-center w-full truncate">{{ $category->name }}</span>
              </a>
            @endforeach
          </div>
        </div>
      </section>
    @endif

    @if($section['id'] === 'dual_banners')
      {{-- ═══ 3. MOBILE BANNERS (STACKED) ═══ --}}
      <section style="padding:24px 16px;">
        <div class="flex flex-col gap-4">
          <a href="{{ $dualBanners['banner1']['link'] ?? route('shop') }}" class="relative block overflow-hidden" style="aspect-ratio:3/4;">
            <img src="{{ $dualBanners['banner1']['image_url'] }}" alt="{{ $dualBanners['banner1']['title'] }}" class="w-full h-full object-cover">
            <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(24,24,24,0.8),transparent);"></div>
            <div style="position:absolute;inset:0;display:flex;flex-direction:column;justify-content:flex-end;padding:24px;color:#fff;">
              <p class="eyebrow" style="margin-bottom:6px;">{!! \App\Models\Setting::format($dualBanners['banner1']['eyebrow']) !!}</p>
              <h3 style="font-family:'Cormorant Garamond',serif;font-size:2rem;font-style:italic;font-weight:300;margin-bottom:12px;">{!! \App\Models\Setting::format($dualBanners['banner1']['title']) !!}</h3>
              <span style="font-size:10px;font-weight:700;letter-spacing:0.25em;text-transform:uppercase;border-bottom:1px solid rgba(255,255,255,0.5);align-self:flex-start;">Explore →</span>
            </div>
          </a>
          
          <div class="grid grid-cols-2 gap-4">
            <a href="{{ $dualBanners['banner2']['link'] ?? route('shop') }}" class="relative block overflow-hidden" style="aspect-ratio:1/1;">
              <img src="{{ $dualBanners['banner2']['image_url'] }}" alt="{{ $dualBanners['banner2']['title'] }}" class="w-full h-full object-cover">
              <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(24,24,24,0.75),transparent);"></div>
              <div style="position:absolute;inset:0;display:flex;flex-direction:column;justify-content:flex-end;padding:16px;color:#fff;">
                <p class="text-[8px] tracking-widest uppercase font-bold text-secondary mb-1">{!! \App\Models\Setting::format($dualBanners['banner2']['eyebrow']) !!}</p>
                <h3 style="font-family:'Cormorant Garamond',serif;font-size:1.25rem;line-height:1.1;">{!! \App\Models\Setting::format($dualBanners['banner2']['title']) !!}</h3>
              </div>
            </a>
            <a href="{{ $dualBanners['banner3']['link'] ?? route('shop') }}" class="relative block overflow-hidden" style="aspect-ratio:1/1;">
              <img src="{{ $dualBanners['banner3']['image_url'] }}" alt="{{ $dualBanners['banner3']['title'] }}" class="w-full h-full object-cover">
              <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(24,24,24,0.75),transparent);"></div>
              <div style="position:absolute;inset:0;display:flex;flex-direction:column;justify-content:flex-end;padding:16px;color:#fff;">
                <p class="text-[8px] tracking-widest uppercase font-bold text-secondary mb-1">{!! \App\Models\Setting::format($dualBanners['banner3']['eyebrow']) !!}</p>
                <h3 style="font-family:'Cormorant Garamond',serif;font-size:1.25rem;line-height:1.1;">{!! \App\Models\Setting::format($dualBanners['banner3']['title']) !!}</h3>
              </div>
            </a>
          </div>
        </div>
      </section>
    @endif

    @if($section['id'] === 'new_arrivals')
      {{-- ═══ 4. MOBILE HORIZONTAL PRODUCTS ═══ --}}
      <section style="padding:32px 0 16px;background:#fff;">
        <div class="px-4 mb-6">
          <p class="eyebrow" style="margin-bottom:4px;">Fresh In</p>
          <h2 style="font-family:'Cormorant Garamond',serif;font-size:2rem;font-style:italic;">New Arrivals</h2>
        </div>
        
        <div class="flex overflow-x-auto gap-4 px-4 pb-8 snap-x" style="-webkit-overflow-scrolling:touch;scrollbar-width:none;">
          @foreach($newArrivals->take(6) as $product)
            <div class="snap-start shrink-0" style="width: 70vw;">
              <div class="pcard">
                <div class="pcard-img" style="aspect-ratio:3/4;">
                  <a href="{{ route('product.show', $product['slug']) }}" class="block w-full h-full">
                    <img src="{{ $product['image_url'] }}" alt="{{ $product['name'] }}" loading="lazy" class="w-full h-full object-cover">
                  </a>
                  @if($product['badge'] ?? false)
                    <span class="pcard-badge text-[9px]">{{ $product['badge'] }}</span>
                  @endif
                  <form action="{{ route('wishlist.toggle', $product->id) }}" method="POST" class="absolute top-2 right-2 z-10">
                    @csrf
                    <button type="submit" class="w-8 h-8 rounded-full bg-white/90 shadow-sm flex items-center justify-center {{ in_array($product->id, $wishlistIds) ? 'text-red-500' : 'text-primary' }}">
                      <svg class="w-4 h-4" fill="{{ in_array($product->id, $wishlistIds) ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                    </button>
                  </form>
                </div>
                <div class="mt-3">
                  <h3 class="text-xs font-bold truncate"><a href="{{ route('product.show', $product['slug']) }}">{{ $product['name'] }}</a></h3>
                  <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs font-bold text-primary tracking-wider">₹{{ number_format($product['price']) }}</span>
                    @if($product['original_price'] ?? false)
                      <span class="text-[10px] text-muted line-through">₹{{ number_format($product['original_price']) }}</span>
                    @endif
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </section>
    @endif
  @endif
@endforeach

<div style="padding: 32px 16px; text-align: center; background: var(--background);">
  <a href="{{ route('shop') }}" class="btn-dark w-full py-4 text-xs">View Full Collection</a>
</div>

@endsection

@section('scripts')
<script>
  if(document.querySelector('.hero-swiper')) {
    new Swiper('.hero-swiper', {
      loop: true, autoplay: { delay: 4500, disableOnInteraction: false },
      effect: 'fade', fadeEffect: { crossFade: true },
      pagination: { el: '.hero-dots', clickable: true }
    });
  }
</script>
<style>
  .hide-scrollbar::-webkit-scrollbar { display: none; }
</style>
@endsection
