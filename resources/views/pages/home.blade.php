@extends('layouts.app')
@section('title', 'Madhavi Stores | Quiet Luxury. Indian Heritage.')

@section('content')

@foreach($homepageSections as $section)
  @if(!isset($section['visible']) || $section['visible'])
    
    @if($section['id'] === 'hero')
      {{-- ═══ 1. HERO CAROUSEL ═══ --}}
      <section style="position:relative;overflow:hidden;">
        <div class="swiper hero-swiper">
          <div class="swiper-wrapper">
            @foreach($heroSlides as $i => $slide)
              <div class="swiper-slide">
                <div class="hero-slide">
                  <img src="{{ $slide['image_url'] }}" alt="{{ strip_tags($slide['title']) }}">
                  <div style="position:absolute;inset:0;background:linear-gradient(to {{ $i % 2 === 1 ? 'left' : 'right' }},rgba(24,24,24,0.75),rgba(24,24,24,0.35),transparent);"></div>
                  <div style="position:absolute;inset:0;display:flex;align-items:center;{{ $i % 2 === 1 ? 'justify-content:flex-end;' : '' }}">
                    <div class="wrap" style="width:100%;">
                      <div style="max-width:480px;color:#fff;{{ $i % 2 === 1 ? 'margin-left:auto;text-align:right;' : '' }}">
                        <p class="eyebrow" style="color:rgba(184,152,110,0.9);margin-bottom:20px;">{!! \App\Models\Setting::format($slide['eyebrow']) !!}</p>
                        <h1 style="font-family:'Cormorant Garamond',serif;font-size:clamp(3rem,6.5vw,6.5rem);font-weight:300;line-height:0.87;margin-bottom:24px;">
                          {!! \App\Models\Setting::format($slide['title']) !!}
                        </h1>
                        @if(!empty($slide['subtitle']))
                          <p style="color:rgba(255,255,255,0.7);font-size:16px;font-weight:300;line-height:1.6;max-width:340px;margin-bottom:36px;{{ $i % 2 === 1 ? 'margin-left:auto;' : '' }}">
                            {!! \App\Models\Setting::format($slide['subtitle']) !!}
                          </p>
                        @endif
                        <div style="display:flex;flex-wrap:wrap;gap:14px;{{ $i % 2 === 1 ? 'justify-content:flex-end;' : '' }}">
                          @if(!empty($slide['button_text']))
                            <a href="{{ $slide['button_url'] ?: route('shop') }}" class="btn-white">{{ $slide['button_text'] }}</a>
                          @endif
                          @if(!empty($slide['has_second_button']) && !empty($slide['second_button_text']))
                            <a href="{{ $slide['second_button_url'] ?: route('shop') }}" style="display:inline-flex;align-items:center;padding:14px 28px;border:2px solid rgba(255,255,255,0.4);color:#fff;font-size:11px;font-weight:700;letter-spacing:0.25em;text-transform:uppercase;transition:background 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.15)'" onmouseout="this.style.background='transparent'">{{ $slide['second_button_text'] }}</a>
                          @endif
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            @endforeach
          </div>

          <div class="swiper-pagination hero-dots" style="position:absolute;bottom:24px;left:50%;transform:translateX(-50%);z-index:10;display:flex;gap:8px;"></div>
          <button class="hero-prev" style="position:absolute;left:16px;top:50%;transform:translateY(-50%);z-index:10;width:44px;height:44px;background:rgba(255,255,255,0.2);border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#fff;transition:background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.4)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
          </button>
          <button class="hero-next" style="position:absolute;right:16px;top:50%;transform:translateY(-50%);z-index:10;width:44px;height:44px;background:rgba(255,255,255,0.2);border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#fff;transition:background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.4)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
          </button>
        </div>
      </section>
    @endif

    @if($section['id'] === 'categories')
      {{-- ═══ 2. CATEGORY CIRCLES ═══ --}}
      <section style="padding:56px 0;border-bottom:1px solid #f0f0f0;">
        <div class="wrap">
          <div class="grid-cats">
            @foreach($categories as $category)
              <a href="{{ route('shop', ['category'=>$category->slug]) }}" class="cat-pill">
                <div class="cat-pill-img">
                  <img src="{{ $category->image_url }}" alt="{{ $category->name }}" loading="lazy">
                </div>
                <span>{{ $category->name }}</span>
              </a>
            @endforeach
          </div>
        </div>
      </section>
    @endif

    @if($section['id'] === 'dual_banners')
      {{-- ═══ 3. DUAL BANNERS ═══ --}}
      <section style="padding:56px 0;">
        <div class="wrap">
          <div class="dual-banner">

            <a href="{{ $dualBanners['banner1']['link'] ?? route('shop') }}" class="banner-card" style="aspect-ratio:4/3;">
              <img src="{{ $dualBanners['banner1']['image_url'] }}" alt="{{ $dualBanners['banner1']['title'] }}" style="height:480px;">
              <div class="banner-card-overlay" style="background:linear-gradient(to top,rgba(24,24,24,0.7),rgba(24,24,24,0.1),transparent);"></div>
              <div class="banner-card-content">
                <p class="eyebrow" style="margin-bottom:8px;">{!! \App\Models\Setting::format($dualBanners['banner1']['eyebrow']) !!}</p>
                <h3 style="font-family:'Cormorant Garamond',serif;font-size:2.5rem;font-style:italic;font-weight:300;margin-bottom:16px;">{!! \App\Models\Setting::format($dualBanners['banner1']['title']) !!}</h3>
                <span style="font-size:11px;font-weight:700;letter-spacing:0.35em;text-transform:uppercase;border-bottom:1px solid rgba(255,255,255,0.5);padding-bottom:2px;">Explore →</span>
              </div>
            </a>

            <div class="dual-right">
              <a href="{{ $dualBanners['banner2']['link'] ?? route('shop') }}" class="banner-card">
                <img src="{{ $dualBanners['banner2']['image_url'] }}" alt="{{ $dualBanners['banner2']['title'] }}" style="height:224px;object-position:top;">
                <div class="banner-card-overlay" style="background:linear-gradient(to right,rgba(24,24,24,0.65),transparent);"></div>
                <div style="position:absolute;inset:0;display:flex;align-items:center;padding:32px;color:#fff;">
                  <div>
                    <p class="eyebrow" style="margin-bottom:6px;">{!! \App\Models\Setting::format($dualBanners['banner2']['eyebrow']) !!}</p>
                    <h3 style="font-family:'Cormorant Garamond',serif;font-size:2rem;font-style:italic;font-weight:300;">{!! \App\Models\Setting::format($dualBanners['banner2']['title']) !!}</h3>
                  </div>
                </div>
              </a>
              <a href="{{ $dualBanners['banner3']['link'] ?? route('shop') }}" class="banner-card">
                <img src="{{ $dualBanners['banner3']['image_url'] }}" alt="{{ $dualBanners['banner3']['title'] }}" style="height:224px;">
                <div class="banner-card-overlay" style="background:linear-gradient(to right,rgba(24,24,24,0.65),transparent);"></div>
                <div style="position:absolute;inset:0;display:flex;align-items:center;padding:32px;color:#fff;">
                  <div>
                    <p class="eyebrow" style="margin-bottom:6px;">{!! \App\Models\Setting::format($dualBanners['banner3']['eyebrow']) !!}</p>
                    <h3 style="font-family:'Cormorant Garamond',serif;font-size:2rem;font-style:italic;font-weight:300;">{!! \App\Models\Setting::format($dualBanners['banner3']['title']) !!}</h3>
                  </div>
                </div>
              </a>
            </div>

          </div>
        </div>
      </section>
    @endif

    @if($section['id'] === 'new_arrivals')
      {{-- ═══ 4. NEW ARRIVALS SLIDER ═══ --}}
      <section style="padding:56px 0;background:#fff;">
        <div class="wrap">
          <div style="display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:32px;">
            <div>
              <p class="eyebrow" style="margin-bottom:8px;">Fresh In</p>
              <h2 class="section-title">New Arrivals</h2>
            </div>
          </div>
          <div class="grid-4">
            @foreach($newArrivals->take(8) as $product)
            <div class="pcard">
              <div class="pcard-img">
                <a href="{{ route('product.show', $product['slug']) }}" style="display:block;width:100%;height:100%;">
                  <img src="{{ $product['image_url'] }}" alt="{{ $product['name'] }}" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=400&q=70'">
                </a>
                @if($product['badge'] ?? false)
                  <span class="pcard-badge">{{ $product['badge'] }}</span>
                @endif
                <form action="{{ route('wishlist.toggle', $product->id) }}" method="POST" class="absolute top-3 right-3 z-10">
                  @csrf
                  <button type="submit" class="w-8 h-8 rounded-full bg-white/90 shadow-md flex items-center justify-center hover:text-red-500 transition-colors {{ in_array($product->id, $wishlistIds) ? 'text-red-500' : 'text-primary' }}">
                    <svg class="w-4 h-4" fill="{{ in_array($product->id, $wishlistIds) ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                  </button>
                </form>
                <a href="{{ route('product.show', $product['slug']) }}" class="pcard-quick">View Details</a>
              </div>
              <div style="margin-top:14px;">
                    <h3 style="font-size:13px;font-weight:600;line-height:1.4;margin-bottom:6px;">
                      <a href="{{ route('product.show', $product['slug']) }}" style="color:inherit;text-decoration:none;">{{ $product['name'] }}</a>
                    </h3>
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                      <span class="eyebrow" style="color:var(--primary);">₹{{ number_format($product['price']) }}</span>
                      @if($product['original_price'] ?? false)
                        <span style="font-size:11px;color:var(--muted);text-decoration:line-through;">₹{{ number_format($product['original_price']) }}</span>
                        <span class="disc-badge">{{ round((($product['original_price']-$product['price'])/$product['original_price'])*100) }}% OFF</span>
                      @endif
                    </div>
                    <form action="{{ route('cart.add') }}" method="POST" class="mt-3">
                      @csrf
                      <input type="hidden" name="product_id" value="{{ $product['id'] }}">
                      @if($product['has_sizes'] ?? false)
                        <input type="hidden" name="size" value="M">
                      @endif
                      <input type="hidden" name="quantity" value="1">
                      <button type="submit" class="w-full text-center py-2 text-[10px] font-bold tracking-wider uppercase border border-primary text-primary hover:bg-primary hover:text-white transition-all {{ !($product['has_sizes'] ?? false) && ($product['stock'] ?? 0) <= 0 ? 'opacity-50 cursor-not-allowed' : '' }}" {{ !($product['has_sizes'] ?? false) && ($product['stock'] ?? 0) <= 0 ? 'disabled' : '' }}>
                        {{ !($product['has_sizes'] ?? false) && ($product['stock'] ?? 0) <= 0 ? 'Out Of Stock' : 'Add To Bag' }}
                      </button>
                    </form>
                  </div>
                </div>
            @endforeach
          </div>
          <div style="margin-top:40px;text-align:center;">
            <a href="{{ route('shop', ['filter' => 'new-arrivals']) }}" class="btn-secondary">View All New Arrivals</a>
          </div>
        </div>
      </section>
    @endif

    @if($section['id'] === 'promo_banner')
      {{-- ═══ 5. PROMO BANNER ═══ --}}
      <div class="promo-banner">
        <img src="{{ $promoBanner['image_url'] }}" alt="Sale">
        <div class="promo-overlay">
          <div class="promo-inner">
            <p class="eyebrow" style="margin-bottom:20px;">{!! \App\Models\Setting::format($promoBanner['eyebrow']) !!}</p>
            <h2 style="font-family:'Cormorant Garamond',serif;font-size:clamp(2.5rem,6vw,5.5rem);font-weight:300;font-style:italic;line-height:0.9;margin-bottom:32px;">{!! \App\Models\Setting::format($promoBanner['title']) !!}</h2>
            <a href="{{ $promoBanner['button_link'] ?? route('shop') }}" class="btn-white">{{ $promoBanner['button_text'] }}</a>
          </div>
        </div>
      </div>
    @endif

    @if($section['id'] === 'bestsellers')
      {{-- ═══ 6. BESTSELLERS ═══ --}}
      <section style="padding:56px 0;background:var(--silk);">
        <div class="wrap">
          <div style="display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:32px;">
            <div>
              <p class="eyebrow" style="margin-bottom:8px;">Most Loved</p>
              <h2 class="section-title">Bestsellers</h2>
            </div>
          </div>
          <div class="grid-4">
            @foreach($bestSellers as $product)
            <div class="pcard">
              <div class="pcard-img">
                <a href="{{ route('product.show', $product['slug']) }}" style="display:block;width:100%;height:100%;">
                  <img src="{{ $product['image_url'] }}" alt="{{ $product['name'] }}" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=400&q=70'">
                </a>
                @if($product['badge'] ?? false)
                  <span class="pcard-badge">{{ $product['badge'] }}</span>
                @endif
                <form action="{{ route('wishlist.toggle', $product->id) }}" method="POST" class="absolute top-3 right-3 z-10">
                  @csrf
                  <button type="submit" class="w-8 h-8 rounded-full bg-white/90 shadow-md flex items-center justify-center hover:text-red-500 transition-colors {{ in_array($product->id, $wishlistIds) ? 'text-red-500' : 'text-primary' }}">
                    <svg class="w-4 h-4" fill="{{ in_array($product->id, $wishlistIds) ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                  </button>
                </form>
                <a href="{{ route('product.show', $product['slug']) }}" class="pcard-quick">View Details</a>
              </div>
              <div style="margin-top:14px;">
                <h3 style="font-size:13px;font-weight:600;line-height:1.4;margin-bottom:6px;">
                  <a href="{{ route('product.show', $product['slug']) }}" style="color:inherit;text-decoration:none;">{{ $product['name'] }}</a>
                </h3>
                <div style="display:flex;align-items:center;gap:8px;">
                  <span class="eyebrow" style="color:var(--primary);">₹{{ number_format($product['price']) }}</span>
                  @if($product['original_price'] ?? false)
                    <span style="font-size:11px;color:var(--muted);text-decoration:line-through;">₹{{ number_format($product['original_price']) }}</span>
                  @endif
                </div>
                <form action="{{ route('cart.add') }}" method="POST" class="mt-3">
                  @csrf
                  <input type="hidden" name="product_id" value="{{ $product['id'] }}">
                  @if($product['has_sizes'] ?? false)
                    <input type="hidden" name="size" value="M">
                  @endif
                  <input type="hidden" name="quantity" value="1">
                  <button type="submit" class="w-full text-center py-2 text-[10px] font-bold tracking-wider uppercase border border-primary text-primary hover:bg-primary hover:text-white transition-all {{ !($product['has_sizes'] ?? false) && ($product['stock'] ?? 0) <= 0 ? 'opacity-50 cursor-not-allowed' : '' }}" {{ !($product['has_sizes'] ?? false) && ($product['stock'] ?? 0) <= 0 ? 'disabled' : '' }}>
                    {{ !($product['has_sizes'] ?? false) && ($product['stock'] ?? 0) <= 0 ? 'Out Of Stock' : 'Add To Bag' }}
                  </button>
                </form>
              </div>
            </div>
            @endforeach
          </div>
          <div style="margin-top:40px;text-align:center;">
            <a href="{{ route('shop', ['filter' => 'bestsellers']) }}" class="btn-secondary">View All Bestsellers</a>
          </div>
        </div>
      </section>
    @endif



    @if($section['id'] === 'newsletter')
      {{-- ═══ 8. NEWSLETTER ═══ --}}
      <section style="padding:64px 0;background:var(--primary);">
        <div class="wrap">
          <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:32px;">
            <div style="color:#fff;">
              <p class="eyebrow" style="color:var(--secondary);margin-bottom:12px;">{!! \App\Models\Setting::format($newsletter['eyebrow']) !!}</p>
              <h2 style="font-family:'Cormorant Garamond',serif;font-size:clamp(2rem,4vw,3rem);font-weight:300;font-style:italic;line-height:1.1;">{!! \App\Models\Setting::format($newsletter['title']) !!}</h2>
              <p style="color:rgba(255,255,255,0.5);margin-top:10px;font-size:13px;font-weight:300;">{!! \App\Models\Setting::format($newsletter['description']) !!}</p>
            </div>
            <form action="#" method="POST" class="newsletter-form">
              <input type="email" placeholder="Your email address" required
                     style="flex:1;padding:14px 20px;font-size:13px;border:none;outline:none;font-family:inherit;color:var(--primary);">
              <button type="submit" class="btn-primary" style="border-radius:0;border:none;">Subscribe</button>
            </form>
          </div>
        </div>
      </section>
    @endif

  @endif
@endforeach

@endsection

@section('scripts')
<script>
(function() {
  function initHome() {


    // Hero Swiper
    if (document.querySelector('.hero-swiper')) {
      new Swiper('.hero-swiper', {
        loop: true, speed: 800,
        autoplay: { delay: 5000, disableOnInteraction: false },
        navigation: { prevEl: '.hero-prev', nextEl: '.hero-next' },
        pagination: { el: '.hero-dots', clickable: true },
        effect: 'fade', fadeEffect: { crossFade: true },
      });
    }

    // Arrivals Swiper
    if (document.querySelector('.arrivals-swiper')) {
      new Swiper('.arrivals-swiper', {
        slidesPerView: 'auto', spaceBetween: 16, grabCursor: true,
        navigation: { prevEl: '.arr-prev', nextEl: '.arr-next' },
      });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHome);
  } else {
    initHome();
  }
})();
</script>
@endsection
