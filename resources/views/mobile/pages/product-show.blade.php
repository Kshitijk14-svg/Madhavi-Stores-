@extends('mobile.layouts.app')
@section('title', $product->seo_title ?: $product->name . ' — Madhavi Stores')

@section('content')
<div class="pb-32">

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
    {{-- Wishlist toggle --}}
    <div class="absolute top-3 right-3 z-10">
      <form method="POST" action="{{ route('wishlist.toggle', $product->id) }}">
        @csrf
        <button type="submit" class="w-10 h-10 flex items-center justify-center bg-white/90 backdrop-blur-sm shadow-sm rounded-full">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" class="text-primary"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
        </button>
      </form>
    </div>
  </div>

  {{-- Product Info --}}
  <div class="px-4 pt-5 pb-3">
    @if($product->badge)
      <span class="inline-block text-[9px] font-bold tracking-widest uppercase bg-secondary text-primary px-2 py-0.5 mb-2">{{ $product->badge }}</span>
    @endif
    @if($product->category)
      <p class="text-[10px] font-bold tracking-widest uppercase text-gray-400 mb-1">{{ $product->category->name }}</p>
    @endif
    <h1 style="font-family:'Cormorant Garamond',serif;font-size:1.625rem;font-weight:300;line-height:1.2;margin-bottom:8px;">{{ $product->name }}</h1>
    <div class="flex items-center gap-3 mb-3">
      <span class="text-lg font-bold text-secondary">₹{{ number_format($product->price, 0) }}</span>
      @if($product->original_price && $product->original_price > $product->price)
        <span class="text-sm text-gray-400 line-through">₹{{ number_format($product->original_price, 0) }}</span>
        @php $savePct = round((($product->original_price - $product->price) / $product->original_price) * 100); @endphp
        <span class="text-[10px] font-bold text-green-600 bg-green-50 px-1.5 py-0.5">{{ $savePct }}% off</span>
      @endif
    </div>
    @if($product->rating)
      <div class="flex items-center gap-1 mb-3">
        @for($i = 1; $i <= 5; $i++)
          <svg width="12" height="12" fill="{{ $i <= round($product->rating) ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" class="text-amber-400"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        @endfor
        <span class="text-[11px] text-gray-500 ml-1">{{ number_format($product->rating, 1) }} ({{ $product->review_count }})</span>
      </div>
    @endif
  </div>

  {{-- Description --}}
  @if($product->description)
  <div class="px-4 pb-4 border-b border-gray-100">
    <p class="text-sm font-light leading-relaxed text-gray-600">{{ $product->description }}</p>
  </div>
  @endif

  {{-- Product Details --}}
  @if(!empty($product->details) && count($product->details) > 0)
  <details class="border-b border-gray-100">
    <summary class="flex items-center justify-between px-4 py-4 cursor-pointer list-none" style="min-height:52px;">
      <span class="text-xs font-bold tracking-wider uppercase">Product Details</span>
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="text-gray-400"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
    </summary>
    <div class="px-4 pb-4 space-y-1.5">
      @foreach($product->details as $detail)
        @if(!empty($detail['label']) && !empty($detail['value']))
        <div class="flex gap-3 text-sm">
          <span class="text-gray-400 font-light w-24 shrink-0">{{ $detail['label'] }}</span>
          <span class="text-primary font-medium">{{ $detail['value'] }}</span>
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

  {{-- Reviews --}}
  <details class="border-b border-gray-100" @if($product->reviews->isEmpty()) open @endif>
    <summary class="flex items-center justify-between px-4 py-4 cursor-pointer list-none" style="min-height:52px;">
      <span class="text-xs font-bold tracking-wider uppercase">Reviews ({{ $product->review_count ?? 0 }})</span>
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="text-gray-400"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
    </summary>
    <div class="px-4 pb-4">
      @if($product->reviews->isEmpty())
        <p class="text-sm text-gray-400 py-2">No reviews yet. Be the first!</p>
      @else
        <div class="space-y-4 mb-6">
          @foreach($product->reviews as $review)
          <div class="border-b border-gray-50 pb-3 last:border-0">
            <div class="flex items-center gap-2 mb-1">
              <div class="flex gap-0.5">
                @for($i = 1; $i <= 5; $i++)
                  <svg width="10" height="10" fill="{{ $i <= $review->rating ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" class="text-amber-400"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                @endfor
              </div>
              <span class="text-[11px] font-semibold text-primary">{{ $review->user->name }}</span>
              <span class="text-[10px] text-gray-400 ml-auto">{{ $review->created_at->format('d M') }}</span>
            </div>
            @if($review->comment)
              <p class="text-sm font-light text-gray-600 leading-relaxed">{{ $review->comment }}</p>
            @endif
          </div>
          @endforeach
        </div>
      @endif

      @auth
        @if($hasPurchased)
        <form method="POST" action="{{ route('product.review', $product->id) }}" class="mt-4 pt-4 border-t border-gray-100">
          @csrf
          <p class="text-[10px] font-bold tracking-wider uppercase text-gray-500 mb-3">Write a Review</p>
          <div class="flex gap-2 mb-3">
            @for($i = 1; $i <= 5; $i++)
            <label class="cursor-pointer">
              <input type="radio" name="rating" value="{{ $i }}" class="sr-only peer" {{ $i === 5 ? 'required' : '' }}>
              <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" class="text-gray-300 peer-checked:fill-amber-400 peer-checked:text-amber-400 transition-colors"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            </label>
            @endfor
          </div>
          <textarea name="comment" rows="3" placeholder="Share your experience..."
                    class="w-full border border-gray-200 px-3 py-3 text-sm outline-none focus:border-primary resize-none mb-3"></textarea>
          <button type="submit" class="w-full bg-primary text-white text-xs font-bold tracking-widest uppercase py-3.5" style="min-height:48px;">Submit Review</button>
        </form>
        @endif
      @endauth
    </div>
  </details>

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
  const pgSwiper = new Swiper('.product-gallery-swiper', {
    loop: {{ count($galleryImages) > 1 ? 'true' : 'false' }},
    pagination: { el: '.product-gallery-swiper .swiper-pagination', clickable: true },
    touchRatio: 1,
  });

  document.addEventListener('pjax:success', function() {
    if (document.querySelector('.product-gallery-swiper')) {
      new Swiper('.product-gallery-swiper', {
        loop: true,
        pagination: { el: '.product-gallery-swiper .swiper-pagination', clickable: true },
      });
    }
  });
</script>
@endsection
@endsection
