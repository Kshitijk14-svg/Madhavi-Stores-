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
                  <img src="{{ $slide['mobile_image_url'] ?? $slide['image_url'] }}" alt="{{ strip_tags($slide['title']) }}" decoding="async" {{ $i === 0 ? 'fetchpriority=high' : 'loading=lazy' }} style="width:100%;height:100%;object-fit:cover;object-position:center;">
                  <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(24,24,24,0.85) 0%,rgba(24,24,24,0.2) 60%,transparent);"></div>
                  <div style="position:absolute;inset:0;display:flex;align-items:flex-end;padding:32px 24px;">
                    <div style="color:#fff;width:100%;text-align:left;">
                      <p class="eyebrow" style="color:rgba(235, 184, 41,0.9);margin-bottom:12px;">{!! \App\Models\Setting::format($slide['eyebrow']) !!}</p>
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
            <img src="{{ $dualBanners['banner1']['image_url'] }}" alt="{{ $dualBanners['banner1']['title'] }}" loading="lazy" decoding="async" class="w-full h-full object-cover">
            <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(24,24,24,0.8),transparent);"></div>
            <div style="position:absolute;inset:0;display:flex;flex-direction:column;justify-content:flex-end;padding:24px;color:#fff;">
              <p class="eyebrow" style="margin-bottom:6px;">{!! \App\Models\Setting::format($dualBanners['banner1']['eyebrow']) !!}</p>
              <h3 style="font-family:'Cormorant Garamond',serif;font-size:2rem;font-style:italic;font-weight:300;margin-bottom:12px;">{!! \App\Models\Setting::format($dualBanners['banner1']['title']) !!}</h3>
              <span style="font-size:10px;font-weight:700;letter-spacing:0.25em;text-transform:uppercase;border-bottom:1px solid rgba(255,255,255,0.5);align-self:flex-start;">Explore →</span>
            </div>
          </a>

          <div class="grid grid-cols-2 gap-4">
            <a href="{{ $dualBanners['banner2']['link'] ?? route('shop') }}" class="relative block overflow-hidden" style="aspect-ratio:1/1;">
              <img src="{{ $dualBanners['banner2']['image_url'] }}" alt="{{ $dualBanners['banner2']['title'] }}" loading="lazy" decoding="async" class="w-full h-full object-cover">
              <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(24,24,24,0.75),transparent);"></div>
              <div style="position:absolute;inset:0;display:flex;flex-direction:column;justify-content:flex-end;padding:16px;color:#fff;">
                <p class="text-[8px] tracking-widest uppercase font-bold text-secondary mb-1">{!! \App\Models\Setting::format($dualBanners['banner2']['eyebrow']) !!}</p>
                <h3 style="font-family:'Cormorant Garamond',serif;font-size:1.25rem;line-height:1.1;">{!! \App\Models\Setting::format($dualBanners['banner2']['title']) !!}</h3>
              </div>
            </a>
            <a href="{{ $dualBanners['banner3']['link'] ?? route('shop') }}" class="relative block overflow-hidden" style="aspect-ratio:1/1;">
              <img src="{{ $dualBanners['banner3']['image_url'] }}" alt="{{ $dualBanners['banner3']['title'] }}" loading="lazy" decoding="async" class="w-full h-full object-cover">
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
      {{-- ═══ 4. MOBILE NEW ARRIVALS ═══ --}}
      <section style="padding:32px 0 16px;background:#fff;">
        <div class="flex items-end justify-between px-4 mb-6">
          <div>
            <p class="eyebrow" style="margin-bottom:4px;">Fresh In</p>
            <h2 style="font-family:'Cormorant Garamond',serif;font-size:2rem;font-style:italic;">New Arrivals</h2>
          </div>
          <a href="{{ route('shop', ['filter' => 'new-arrivals']) }}" class="text-[10px] font-bold tracking-widest uppercase border-b border-primary pb-0.5">View All</a>
        </div>

        <div class="flex overflow-x-auto gap-4 pb-8 hide-scrollbar" style="padding-left:20px;padding-right:20px;-webkit-overflow-scrolling:touch;scrollbar-width:none;">
          @foreach($newArrivals->take(6) as $product)
            <div class="shrink-0" style="width:70vw;">
              <div class="pcard">
                <div class="pcard-img" style="aspect-ratio:3/4;position:relative;">
                  <a href="{{ route('product.show', $product['slug']) }}" class="block w-full h-full">
                    <img src="{{ $product['thumb_url'] }}" @if($product['card_srcset']) srcset="{{ $product['card_srcset'] }}" sizes="70vw" @endif alt="{{ $product['name'] }}" loading="lazy" decoding="async" class="w-full h-full object-cover">
                  </a>
                  @if($product['badge'] ?? false)
                    <span class="pcard-badge text-[9px]">{{ $product['badge'] }}</span>
                  @endif
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

    @if($section['id'] === 'promo_banner' && !empty($promoBanner['image_url']))
      {{-- ═══ 5. PROMO BANNER ═══ --}}
      <section style="margin:8px 16px;position:relative;overflow:hidden;aspect-ratio:16/9;">
        <img src="{{ $promoBanner['image_url'] }}" alt="{{ $promoBanner['title'] ?? '' }}" loading="lazy" decoding="async" class="w-full h-full object-cover">
        <div style="position:absolute;inset:0;background:rgba(24,24,24,0.5);"></div>
        <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:24px;color:#fff;">
          @if(!empty($promoBanner['eyebrow']))
            <p class="eyebrow" style="color:rgba(235, 184, 41,0.9);margin-bottom:10px;">{{ $promoBanner['eyebrow'] }}</p>
          @endif
          <h2 style="font-family:'Cormorant Garamond',serif;font-size:1.75rem;font-style:italic;font-weight:300;margin-bottom:16px;">{{ $promoBanner['title'] ?? '' }}</h2>
          @if(!empty($promoBanner['button_text']))
            <a href="{{ $promoBanner['button_link'] ?? route('shop') }}" class="btn-white" style="font-size:10px;">{{ $promoBanner['button_text'] }}</a>
          @endif
        </div>
      </section>
    @endif

    @if($section['id'] === 'bestsellers')
      {{-- ═══ 6. MOBILE BESTSELLERS ═══ --}}
      <section style="padding:32px 0 16px;background:#fff;">
        <div class="flex items-end justify-between px-4 mb-6">
          <div>
            <p class="eyebrow" style="margin-bottom:4px;">Most Loved</p>
            <h2 style="font-family:'Cormorant Garamond',serif;font-size:2rem;font-style:italic;">Bestsellers</h2>
          </div>
          <a href="{{ route('shop', ['filter' => 'bestsellers']) }}" class="text-[10px] font-bold tracking-widest uppercase border-b border-primary pb-0.5">View All</a>
        </div>

        <div class="flex overflow-x-auto gap-4 pb-8 hide-scrollbar" style="padding-left:20px;padding-right:20px;-webkit-overflow-scrolling:touch;scrollbar-width:none;">
          @foreach($bestSellers->take(6) as $product)
            <div class="shrink-0" style="width:70vw;">
              <div class="pcard">
                <div class="pcard-img" style="aspect-ratio:3/4;position:relative;">
                  <a href="{{ route('product.show', $product->slug) }}" class="block w-full h-full">
                    <img src="{{ $product->thumb_url }}" @if($product->card_srcset) srcset="{{ $product->card_srcset }}" sizes="70vw" @endif alt="{{ $product->name }}" loading="lazy" decoding="async" class="w-full h-full object-cover">
                  </a>
                  @if($product->badge)
                    <span class="pcard-badge text-[9px]">{{ $product->badge }}</span>
                  @endif
                </div>
                <div class="mt-3">
                  <h3 class="text-xs font-bold truncate"><a href="{{ route('product.show', $product->slug) }}">{{ $product->name }}</a></h3>
                  <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs font-bold text-primary tracking-wider">₹{{ number_format($product->price) }}</span>
                    @if($product->original_price)
                      <span class="text-[10px] text-muted line-through">₹{{ number_format($product->original_price) }}</span>
                    @endif
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </section>
    @endif

    @if($section['id'] === 'instagram_feed')
      {{-- ═══ INSTAGRAM REELS ═══ --}}
      @php
        $igHandle = $instagram['handle'] ?? 'madhavi_stores';
        $igPosts  = !empty($instaPosts) ? $instaPosts : array_map(fn($u) => [
          'image' => $u, 'video_url' => null, 'media_type' => 'IMAGE', 'permalink' => null, 'caption' => ''
        ], [
          'https://images.unsplash.com/photo-1610030469983-98e550d6193c?w=300&q=60',
          'https://images.unsplash.com/photo-1613915617430-8ab0fd7c6baf?w=300&q=60',
          'https://images.unsplash.com/photo-1596455607563-ad6193f76b17?w=300&q=60',
          'https://images.unsplash.com/photo-1551488831-00ddcb6c6bd3?w=300&q=60',
          'https://images.unsplash.com/photo-1583391733956-6c78276477e2?w=300&q=60',
          'https://images.unsplash.com/photo-1617137968427-85924c800a22?w=300&q=60',
        ]);
      @endphp
      <section style="padding:36px 0;background:#fff;">
        <div style="display:flex;align-items:flex-end;justify-content:space-between;padding:0 16px;margin-bottom:20px;">
          <div>
            <p class="eyebrow" style="margin-bottom:4px;">@madhavi_stores</p>
            <h2 style="font-family:'Cormorant Garamond',serif;font-size:2rem;font-style:italic;">Reels &amp; Stories</h2>
          </div>
          <a href="https://instagram.com/{{ $igHandle }}" target="_blank" rel="noopener"
             class="text-[10px] font-bold tracking-widest uppercase border-b border-primary pb-0.5">Follow</a>
        </div>
        <div class="swiper ig-reels-swiper-m" style="padding:0 16px;box-sizing:border-box;">
          <div class="swiper-wrapper">
            @foreach($igPosts as $post)
              <div class="swiper-slide" style="height:auto;">
                <a href="{{ $post['permalink'] ?? 'https://instagram.com/'.$igHandle }}" target="_blank" rel="noopener"
                   style="display:block;position:relative;aspect-ratio:9/16;overflow:hidden;background:#111;">
                  @if(($post['media_type'] ?? 'IMAGE') === 'VIDEO' && !empty($post['video_url']))
                    <video src="{{ $post['video_url'] }}" poster="{{ $post['image'] }}"
                           autoplay muted playsinline loop preload="metadata"
                           style="width:100%;height:100%;object-fit:cover;display:block;"></video>
                  @else
                    <img src="{{ $post['image'] }}" alt="{{ $post['caption'] ?: 'Madhavi Stores on Instagram' }}"
                         loading="lazy" style="width:100%;height:100%;object-fit:cover;display:block;">
                  @endif
                  @if(!empty($post['caption']))
                    <div style="position:absolute;bottom:0;left:0;right:0;background:linear-gradient(transparent,rgba(0,0,0,0.72));padding:24px 10px 10px;color:#fff;font-size:10px;line-height:1.4;">
                      {{ $post['caption'] }}
                    </div>
                  @endif
                </a>
              </div>
            @endforeach
          </div>
        </div>
      </section>
    @endif

    @if($section['id'] === 'newsletter' && !empty($newsletter))
      {{-- ═══ 7. NEWSLETTER ═══ --}}
      <section style="background:#181818;padding:48px 24px;text-align:center;margin-top:8px;">
        <p class="eyebrow" style="color:rgba(235, 184, 41,0.8);margin-bottom:12px;">{{ $newsletter['eyebrow'] ?? 'Stay in the Know' }}</p>
        <h2 style="font-family:'Cormorant Garamond',serif;font-size:2rem;font-style:italic;font-weight:300;color:#fff;margin-bottom:12px;">{{ $newsletter['title'] ?? 'Join the Inner Circle' }}</h2>
        <p style="font-size:13px;color:rgba(255,255,255,0.55);margin-bottom:28px;line-height:1.6;">{{ $newsletter['description'] ?? '' }}</p>
        <form action="{{ route('home') }}" method="POST" class="flex flex-col gap-3">
          @csrf
          <input type="email" name="email" placeholder="{{ $newsletter['placeholder'] ?? 'Your email address' }}"
                 class="w-full px-4 py-3 bg-white/10 border border-white/20 text-white text-sm placeholder-white/40 focus:outline-none focus:border-secondary">
          <button type="submit" class="w-full py-3 bg-secondary text-primary text-xs font-bold tracking-widest uppercase">
            {{ $newsletter['button_text'] ?? 'Subscribe' }}
          </button>
        </form>
      </section>
    @endif

  @endif
@endforeach

@endsection

@section('scripts')
<script>
(function () {
  function initMobile() {
    if (document.querySelector('.hero-swiper')) {
      new Swiper('.hero-swiper', {
        loop: true, autoplay: { delay: 4500, disableOnInteraction: false },
        effect: 'fade', fadeEffect: { crossFade: true },
        pagination: { el: '.hero-dots', clickable: true }
      });
    }

    if (document.querySelector('.ig-reels-swiper-m')) {
      new Swiper('.ig-reels-swiper-m', {
        slidesPerView: 2,
        spaceBetween: 12,
        grabCursor: true,
        observer: true,
        observeParents: true,
      });
      document.querySelectorAll('.ig-reels-swiper-m video').forEach(function (v) {
        v.play().catch(function () {});
      });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMobile);
  } else {
    initMobile();
  }
})();
</script>
<style>
  .hide-scrollbar::-webkit-scrollbar { display: none; }
</style>
@endsection
