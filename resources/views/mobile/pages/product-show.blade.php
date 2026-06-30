@extends('mobile.layouts.app')
@section('title', $product->seo_title ?: $product->name . ' — Madhavi Stores')

@section('content')
<div class="pb-32">

  <style>
    .gallery-counter{display:flex;align-items:center;justify-content:center;gap:8px;margin-top:14px;font-size:13px;letter-spacing:0.18em;font-variant-numeric:tabular-nums;}
    .gallery-counter-window{display:inline-flex;height:1.5em;overflow:hidden;align-items:center;}
    .gallery-counter-current{display:inline-block;}
    .gallery-counter-current.enter-up{animation:galleryCounterUp .42s cubic-bezier(.4,0,.2,1);}
    .gallery-counter-current.enter-down{animation:galleryCounterDown .42s cubic-bezier(.4,0,.2,1);}
    @keyframes galleryCounterUp{0%{transform:translateY(110%);opacity:0;}100%{transform:translateY(0);opacity:1;}}
    @keyframes galleryCounterDown{0%{transform:translateY(-110%);opacity:0;}100%{transform:translateY(0);opacity:1;}}
  </style>

  {{-- Image Gallery (Swiper) --}}
  <div class="swiper product-gallery-swiper bg-gray-50" style="aspect-ratio:3/4;max-height:75vw;overflow:hidden;">
    <div class="swiper-wrapper">
      @php
        $galleryImages = array_filter(array_merge([$product->image_url], (array)($product->gallery_images ?? [])));
      @endphp
      @foreach($galleryImages as $img)
        <div class="swiper-slide">
          <img src="{{ $img }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
        </div>
      @endforeach
    </div>
    @if(count($galleryImages) > 1)
    <div class="swiper-pagination" style="bottom:10px;"></div>
    @endif
  </div>

  {{-- Image counter caption (below the gallery) --}}
  @if(count($galleryImages) > 1)
  <div class="gallery-counter text-gray-400" aria-hidden="true">
    <span class="gallery-counter-window"><span id="gallery-counter-current" class="gallery-counter-current text-primary font-semibold">1</span></span>
    <span class="opacity-50">/</span>
    <span class="text-primary font-semibold">{{ count($galleryImages) }}</span>
  </div>
  @endif

  {{-- Product Info --}}
  <div class="px-4 pt-5 pb-3">
    @if($product->badge)
      <span class="inline-block text-[9px] font-bold tracking-widest uppercase bg-secondary text-primary px-2 py-0.5 mb-2">{{ $product->badge }}</span>
    @endif
    @if($product->category)
      <p class="text-[10px] font-bold tracking-widest uppercase text-gray-400 mb-1">{{ $product->category->name }}</p>
    @endif
    <h1 style="font-family:'Cormorant Garamond',serif;font-size:1.625rem;font-weight:300;line-height:1.2;margin-bottom:8px;">{{ $product->name }}</h1>
    @php
      // final_price is the charged price (discount-aware) — matches cart & checkout.
      $finalPrice = $product->final_price;
      $wasPrice = $product->original_price ?: $product->price;
    @endphp
    <div class="flex items-center gap-3 mb-3">
      <span class="text-lg font-bold text-secondary">₹{{ number_format($finalPrice, 0) }}</span>
      @if($wasPrice > $finalPrice)
        <span class="text-sm text-gray-400 line-through">₹{{ number_format($wasPrice, 0) }}</span>
        @php $savePct = round((($wasPrice - $finalPrice) / $wasPrice) * 100); @endphp
        <span class="text-[10px] font-bold text-green-600 bg-green-50 px-1.5 py-0.5">{{ $savePct }}% off</span>
      @endif
    </div>
    {{-- Wishlist + Share (labeled action row) --}}
    @php $isWished = in_array($product->id, $wishlistIds ?? []); @endphp
    <div class="flex items-center gap-3 mt-1">
      <form method="POST" action="{{ route('wishlist.toggle', $product->id) }}" class="flex-1">
        @csrf
        <button type="submit" aria-label="{{ $isWished ? 'Remove from wishlist' : 'Add to wishlist' }}"
                class="w-full flex items-center justify-center gap-2 border border-gray-200 py-2.5 text-[11px] font-bold tracking-wider uppercase {{ $isWished ? 'text-red-500 border-red-200' : 'text-primary' }}">
          <svg width="15" height="15" fill="{{ $isWished ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
          {{ $isWished ? 'Wishlisted' : 'Wishlist' }}
        </button>
      </form>
      <button type="button" aria-label="Share product"
              onclick="shareProduct('{{ route('product.show', $product->slug) }}', @js($product->name))"
              class="flex-1 flex items-center justify-center gap-2 border border-gray-200 py-2.5 text-[11px] font-bold tracking-wider uppercase text-primary">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z"/></svg>
        Share
      </button>
    </div>
  </div>

  {{-- Description --}}
  @if($product->description)
  <div class="px-4 pb-4 border-b border-gray-100">
    <p class="text-sm font-light leading-relaxed text-gray-600">{{ $product->description }}</p>
  </div>
  @endif

  {{-- Product Details --}}
  @if(!empty($product->details) && count($product->details) > 0)
  <details class="border-b border-gray-100" open>
    <summary class="flex items-center justify-between px-4 py-4 cursor-pointer list-none" style="min-height:52px;">
      <span class="text-xs font-bold tracking-wider uppercase">Product Details</span>
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="text-gray-400"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
    </summary>
    <div class="px-4 pb-4 space-y-1.5">
      @foreach($product->details as $detail)
        @php $line = is_array($detail) ? trim(implode(' ', array_filter($detail))) : trim((string) $detail); @endphp
        @if($line !== '')
        <div class="flex gap-2 text-sm text-gray-600">
          <span class="text-secondary shrink-0">•</span>
          <span>{{ $line }}</span>
        </div>
        @endif
      @endforeach
    </div>
  </details>
  @endif

  {{-- Size Chart --}}
  @if($product->size_chart_image)
  <details class="border-b border-gray-100">
    <summary class="flex items-center justify-between px-4 py-4 cursor-pointer list-none" style="min-height:52px;">
      <span class="text-xs font-bold tracking-wider uppercase">Size Chart</span>
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="text-gray-400"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
    </summary>
    <div class="px-4 pb-4">
      <img src="/{{ ltrim($product->size_chart_image, '/') }}" alt="Size Chart" class="w-full">
    </div>
  </details>
  @endif

  {{-- Related Products --}}
  @if($relatedProducts->isNotEmpty())
  <div class="py-6">
    <p class="eyebrow px-4" style="color:var(--secondary);margin-bottom:16px;">You May Also Like</p>
    <div class="grid grid-cols-2 gap-3 px-4">
      @foreach($relatedProducts->take(4) as $rp)
      <a href="{{ route('product.show', $rp->slug) }}" class="group block">
        <div class="aspect-[3/4] bg-gray-50 overflow-hidden relative mb-2">
          <img src="{{ $rp->image_url }}" alt="{{ $rp->name }}"
               class="w-full h-full object-cover transition-transform duration-300 group-active:scale-105" loading="lazy">
          @if($rp->badge)
            <span class="absolute top-2 left-2 bg-secondary text-primary text-[9px] font-bold px-1.5 py-0.5 tracking-wider uppercase">{{ $rp->badge }}</span>
          @endif
        </div>
        <p class="text-xs font-semibold text-primary line-clamp-2 leading-snug">{{ $rp->name }}</p>
        <div class="flex items-center gap-2 mt-0.5">
          <span class="text-xs font-bold text-secondary">₹{{ number_format($rp->price, 0) }}</span>
          @if($rp->original_price && $rp->original_price > $rp->price)
            <span class="text-[10px] text-gray-400 line-through">₹{{ number_format($rp->original_price, 0) }}</span>
          @endif
        </div>
      </a>
      @endforeach
    </div>
  </div>
  @endif

</div>

{{-- Sticky Bottom CTA (product bar — hidden by bottom-bar for non-product pages) --}}
<div id="mob-product-bar" class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 z-50 px-4 py-3"
     style="padding-bottom:env(safe-area-inset-bottom);">
  <form method="POST" action="{{ route('cart.add') }}" id="mob-atc-form">
    @csrf
    <input type="hidden" name="product_id" value="{{ $product->id }}">
    <input type="hidden" name="quantity" value="1">
    @if($product->has_sizes)
    <div class="mb-2">
      <div class="flex gap-1.5 overflow-x-auto hide-scrollbar pb-1">
        @foreach($product->sizes->where('stock', '>', 0) as $size)
          <label class="shrink-0 cursor-pointer">
            <input type="radio" name="size" value="{{ $size->size }}" class="sr-only peer" required>
            <span class="inline-flex items-center justify-center w-10 h-10 border border-gray-200 text-xs font-bold peer-checked:bg-primary peer-checked:text-white peer-checked:border-primary transition-colors">{{ $size->size }}</span>
          </label>
        @endforeach
        @foreach($product->sizes->where('stock', 0) as $size)
          <span class="shrink-0 inline-flex items-center justify-center w-10 h-10 border border-gray-100 text-xs font-bold text-gray-300 line-through cursor-not-allowed">{{ $size->size }}</span>
        @endforeach
      </div>
    </div>
    @endif
    <div class="flex gap-2">
      <a href="{{ route('cart') }}"
         class="flex-shrink-0 w-12 h-12 flex items-center justify-center border border-gray-200 text-primary">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
      </a>
      @if($product->stock > 0 || ($product->has_sizes && $product->sizes->sum('stock') > 0))
        <button type="submit" class="flex-1 bg-primary text-white text-xs font-bold tracking-widest uppercase h-12">Add to Bag</button>
      @else
        <button type="button" disabled class="flex-1 bg-gray-200 text-gray-400 text-xs font-bold tracking-widest uppercase h-12 cursor-not-allowed">Out of Stock</button>
      @endif
    </div>
  </form>
</div>

@section('scripts')
<script>
  // Slide the gallery image-number caption when the active slide changes.
  let _galleryCounterPrev = 1;
  function updateGalleryCounter(n) {
    const el = document.getElementById('gallery-counter-current');
    if (!el) return;
    const cls = n >= _galleryCounterPrev ? 'enter-up' : 'enter-down';
    _galleryCounterPrev = n;
    el.textContent = n;
    el.classList.remove('enter-up', 'enter-down');
    void el.offsetWidth; // restart the animation
    el.classList.add(cls);
  }

  function initPgSwiper() {
    const root = document.querySelector('.product-gallery-swiper');
    if (!root) return;
    _galleryCounterPrev = 1;
    const multi = root.querySelectorAll('.swiper-slide').length > 1;
    new Swiper('.product-gallery-swiper', {
      loop: multi,
      pagination: { el: '.product-gallery-swiper .swiper-pagination', clickable: true },
      touchRatio: 1,
      on: {
        slideChange: function () { updateGalleryCounter(this.realIndex + 1); },
      },
    });
  }

  initPgSwiper();
  document.addEventListener('pjax:success', initPgSwiper);
</script>
@endsection
@endsection
