@extends('layouts.app')

@section('title', $product->seo_title ?? ($product->name . ' | Madhavi Stores'))

@section('meta_description', $product->seo_description ?: (Str::limit(strip_tags($product->description ?: 'Discover our exclusive handcrafted ethnic silhouettes, sarees, and fine textiles.'), 150)))
@section('meta_keywords', $product->seo_keywords ?: ($product->name . ', ethnic wear, quiet luxury, handcrafted, indian heritage'))
@section('og_image', $product->image_url ? asset($product->image_url) : asset('images/brand/logo.png'))
@section('og_type', 'product')

@section('content')

<section class="py-12 lg:py-20" style="background:var(--white);">
    <div class="wrap">
        <nav style="display:flex;align-items:center;gap:8px;margin-bottom:40px;">
            <a href="{{ route('home') }}" class="eyebrow" style="text-decoration:none;transition:color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--muted)'">Home</a>
            <span class="eyebrow">/</span>
            <a href="{{ route('shop') }}" class="eyebrow" style="text-decoration:none;transition:color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--muted)'">Collection</a>
            <span class="eyebrow">/</span>
            <span class="eyebrow" style="color:var(--primary);">{{ $product->name }}</span>
        </nav>

        <div class="grid-12 md:grid-cols-2 lg:grid-cols-12" style="gap:48px;">
            
            <!-- Product Gallery Carousel -->
            <div class="md:col-span-1 lg:col-span-7">
                <div class="swiper product-gallery-swiper" style="position:relative;overflow:hidden;aspect-ratio:3/4;background:var(--silk);">
                    <div class="swiper-wrapper">
                        @if($product->image_url)
                        <div class="swiper-slide cursor-zoom-in" onclick="openZoom(this.querySelector('img').src)">
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" style="width:100%;height:100%;object-fit:cover;">
                        </div>
                        @endif
                        
                        @if($product->gallery_images && is_array($product->gallery_images))
                            @foreach($product->gallery_images as $image)
                                <div class="swiper-slide cursor-zoom-in" onclick="openZoom(this.querySelector('img').src)">
                                    <img src="{{ $image }}" alt="{{ $product->name }}" style="width:100%;height:100%;object-fit:cover;">
                                </div>
                            @endforeach
                        @endif
                    </div>
                    
                    <!-- Navigation -->
                    <button class="product-prev" style="position:absolute;left:16px;top:50%;transform:translateY(-50%);z-index:10;width:44px;height:44px;background:var(--white);border:1px solid var(--border);cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--primary);transition:all 0.2s;" onmouseover="this.style.background='var(--primary)';this.style.color='var(--white)'" onmouseout="this.style.background='var(--white)';this.style.color='var(--primary)'">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                    </button>
                    <button class="product-next" style="position:absolute;right:16px;top:50%;transform:translateY(-50%);z-index:10;width:44px;height:44px;background:var(--white);border:1px solid var(--border);cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--primary);transition:all 0.2s;" onmouseover="this.style.background='var(--primary)';this.style.color='var(--white)'" onmouseout="this.style.background='var(--white)';this.style.color='var(--primary)'">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                    </button>
                    
                    <!-- Pagination -->
                    <div class="swiper-pagination product-dots" style="position:absolute;bottom:24px;left:50%;transform:translateX(-50%);z-index:10;display:flex;gap:8px;"></div>
                </div>
            </div>

            <!-- Product Info -->
            <div class="md:col-span-1 lg:col-span-5">
                <div style="padding-top:24px;">
                    <div style="margin-bottom:32px;">
                        @if($product->badge)
                            <span class="pcard-badge" style="position:static;display:inline-block;margin-bottom:16px;">
                                {{ $product->badge }}
                            </span>
                        @endif
                        @if($product->is_new_arrival)
                            <span class="pcard-badge" style="position:static;display:inline-block;margin-bottom:16px;margin-left:8px;background:var(--accent);">
                                NEW ARRIVAL
                            </span>
                        @endif
                        
                        <h1 style="font-family:'Cormorant Garamond',serif;font-size:clamp(2.5rem,4vw,3.5rem);font-weight:300;line-height:1.1;margin-bottom:16px;">{{ $product->name }}</h1>
                        
                        <div style="display:flex;align-items:center;gap:16px;margin-bottom:24px;">
                            @php
                                // Single source of truth for the charged price — matches cart & checkout.
                                $finalPrice = $product->final_price;
                            @endphp
                            <span class="price-display">₹{{ number_format($finalPrice, 2) }}</span>
                            @if($product->original_price || $finalPrice < $product->price)
                                <span class="price-display line-through" style="font-style:normal;">₹{{ number_format($product->original_price ?: $product->price, 2) }}</span>
                                <span class="disc-badge" style="background:rgba(22,163,74,0.1);padding:4px 8px;border-radius:4px;">SAVE {{ round((($product->original_price ?: $product->price)-$finalPrice)/($product->original_price ?: $product->price)*100) }}%</span>
                            @endif
                        </div>
                        
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:24px;">
                            <div style="color:var(--secondary);font-size:14px;letter-spacing:2px;">
                                @for($i = 1; $i <= 5; $i++) ★ @endfor
                            </div>
                            <span class="eyebrow" style="text-transform:none;letter-spacing:0;font-weight:400;">({{ $product->review_count ?? 0 }} reviews)</span>
                        </div>
                    </div>

                    <div style="margin-bottom:40px;color:var(--muted);font-weight:300;line-height:1.8;font-size:15px;">
                        @php
                            $prodDesc = $product->seo_description ?: 'Discover our exclusive handcrafted ethnic wear, designed to bring elegance and quiet luxury to your wardrobe.';
                            $prodDesc = str_replace(['\n', '\\n'], "\n", $prodDesc);
                        @endphp
                        <p>{!! nl2br(e($prodDesc)) !!}</p>
                    </div>

                    <!-- Size Selection -->
                    @if($product->has_sizes)
                    <div style="margin-bottom:40px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                            <span class="eyebrow" style="color:var(--primary);">Select Size</span>
                            @if($product->size_chart_image || ($product->category && $product->category->size_chart_image) || true)
                                <button onclick="openSizeGuide()" style="background:none;border:none;border-bottom:1px solid var(--secondary);color:var(--secondary);font-size:10px;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;cursor:pointer;">Size Guide</button>
                            @endif
                        </div>
                        <div style="display:flex;flex-wrap:wrap;gap:12px;">
                            @php
                                $sizes = $product->sizes;
                            @endphp
                            @foreach($sizes as $sizeModel)
                                @php
                                    $size = is_string($sizeModel) ? $sizeModel : $sizeModel->size;
                                    $stock = is_string($sizeModel) ? 10 : $sizeModel->stock;
                                @endphp
                                <button type="button" data-size="{{ $size }}" class="size-btn {{ $stock <= 0 ? 'out-of-stock' : '' }}" {{ $stock <= 0 ? 'disabled' : '' }} style="position:relative;width:48px;height:48px;display:flex;align-items:center;justify-content:center;border:1px solid var(--border);background:{{ $stock <= 0 ? 'var(--silk)' : 'transparent' }};color:{{ $stock <= 0 ? 'var(--muted)' : 'var(--primary)' }};font-size:13px;font-weight:600;cursor:{{ $stock <= 0 ? 'not-allowed' : 'pointer' }};opacity:{{ $stock <= 0 ? '0.5' : '1' }};transition:all 0.2s;" onmouseover="if(!this.disabled) { this.style.borderColor='var(--primary)'; }" onmouseout="if(!this.disabled && !this.classList.contains('selected')) { this.style.borderColor='var(--border)'; }">
                                    {{ $size }}
                                    @if($stock > 0 && $stock <= 5)
                                        <span class="low-stock-tooltip" style="position:absolute;top:-24px;left:50%;transform:translateX(-50%);background:var(--accent);color:var(--white);font-size:9px;padding:2px 6px;border-radius:2px;white-space:nowrap;display:none;z-index:20;">Only {{ $stock }} left!</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div style="margin-bottom:40px;">
                        <div style="display:flex;align-items:center;gap:12px;">
                            <span class="eyebrow" style="color:var(--primary);">Availability:</span>
                            @if($product->stock > 0)
                                <span class="eyebrow" style="color:#16a34a;background:rgba(22,163,74,0.1);padding:4px 8px;">In Stock ({{ $product->stock }} left)</span>
                            @else
                                <span class="eyebrow" style="color:var(--accent);background:rgba(212,77,68,0.1);padding:4px 8px;">Sold Out</span>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Actions -->
                    <div id="mob-product-bar" class="fixed bottom-0 left-0 right-0 p-4 bg-white/95 backdrop-blur-md shadow-[0_-8px_30px_rgb(0,0,0,0.08)] z-50 flex gap-3 pb-[calc(16px+env(safe-area-inset-bottom))] lg:static lg:p-0 lg:bg-transparent lg:shadow-none lg:flex-col lg:gap-4 lg:mb-12">
                        <form action="{{ route('cart.add') }}" method="POST" id="add-to-bag-form" class="flex-1">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="quantity" value="1">
                            @if($product->has_sizes)
                                <input type="hidden" name="size" id="selected-size" value="">
                            @endif
                            <button type="submit" class="btn-primary w-full h-full min-h-[50px] lg:min-h-[60px]" style="text-align:center; {{ !$product->has_sizes && $product->stock <= 0 ? 'opacity:0.5;cursor:not-allowed;' : '' }}" {{ !$product->has_sizes && $product->stock <= 0 ? 'disabled' : '' }}>
                                Add to Bag
                            </button>
                        </form>

                        <form action="{{ route('wishlist.toggle', $product->id) }}" method="POST" id="wishlist-form" class="flex-shrink-0 lg:w-full">
                            @csrf
                            <button type="submit" class="btn-secondary w-[50px] h-[50px] lg:w-full lg:h-[60px] flex items-center justify-center lg:gap-3 p-0">
                                <svg width="18" height="18" fill="{{ in_array($product->id, $wishlistIds) ? 'currentColor' : 'none' }}" class="{{ in_array($product->id, $wishlistIds) ? 'text-red-500' : 'text-primary' }}" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                                <span class="hidden lg:inline">{{ in_array($product->id, $wishlistIds) ? 'Remove from Wishlist' : 'Add to Wishlist' }}</span>
                            </button>
                        </form>
                    </div>

                    <!-- Product Details List -->
                    <div style="border-top:1px solid var(--border);padding-top:32px;">
                        <h3 class="eyebrow" style="color:var(--primary);margin-bottom:20px;">Product Details</h3>
                        <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:12px;color:var(--muted);font-size:14px;">
                            @php
                                $details = $product->details ?? [
                                    '100% premium handcrafted fabric.',
                                    'Dry clean only recommended.',
                                    'Ethically sourced and produced in India.',
                                    'Standard fit, fits true to size.'
                                ];
                            @endphp
                            @foreach($details as $detail)
                                <li style="display:flex;align-items:flex-start;gap:12px;">
                                    <span style="display:inline-block;width:4px;height:4px;background:var(--secondary);border-radius:50%;margin-top:8px;flex-shrink:0;"></span>
                                    {{ $detail }}
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Reviews Section -->
                    <div style="border-top:1px solid var(--border);padding-top:32px;margin-top:32px;">
                        <h3 class="eyebrow" style="color:var(--primary);margin-bottom:20px;">Customer Reviews</h3>
                        @if($product->reviews->isEmpty())
                            <p class="text-sm text-muted mb-6">No reviews yet.</p>
                        @else
                            <div class="space-y-6 mb-8">
                                @foreach($product->reviews as $review)
                                    <div class="border-b border-gray-100 pb-4">
                                        <div class="flex items-center gap-2 mb-2">
                                            <div style="color:var(--secondary);font-size:12px;letter-spacing:1px;">
                                                @for($i = 1; $i <= 5; $i++) 
                                                    {!! $i <= $review->rating ? '★' : '☆' !!} 
                                                @endfor
                                            </div>
                                            <span class="text-xs font-semibold text-primary">{{ $review->user->name ?? 'Guest' }}</span>
                                            <span class="text-[10px] text-muted">{{ $review->created_at->format('M d, Y') }}</span>
                                        </div>
                                        <p class="text-sm text-muted">{{ $review->comment }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if($hasPurchased)
                            <h4 class="text-sm font-semibold mb-4">Write a Review</h4>
                            <form action="{{ route('product.review', $product->id) }}" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-xs font-semibold text-muted mb-1">Rating</label>
                                    <select name="rating" class="border border-gray-200 px-3 py-2 text-sm outline-none focus:border-secondary w-full max-w-[150px]">
                                        <option value="5">5 - Excellent</option>
                                        <option value="4">4 - Very Good</option>
                                        <option value="3">3 - Good</option>
                                        <option value="2">2 - Fair</option>
                                        <option value="1">1 - Poor</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-muted mb-1">Comment</label>
                                    <textarea name="comment" rows="3" class="border border-gray-200 px-3 py-2 text-sm outline-none focus:border-secondary w-full" placeholder="Share your experience..."></textarea>
                                </div>
                                <button type="submit" class="btn-primary py-2 px-6 text-xs">Submit Review</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Size Guide Modal -->
@php
    $sizeChartImage = $product->size_chart_image ?: ($product->category ? $product->category->size_chart_image : null);
@endphp
@if($sizeChartImage)
<div id="size-guide-modal" class="fixed inset-0 z-[100] hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeSizeGuide()"></div>
    <div class="relative bg-white w-[90%] max-w-2xl p-6 rounded-none shadow-2xl">
        <button onclick="closeSizeGuide()" class="absolute top-4 right-4 text-muted hover:text-primary transition-colors">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <h2 class="font-display text-2xl italic text-primary mb-4 text-center">Size Guide</h2>
        <div class="w-full flex justify-center">
            <img src="{{ asset($sizeChartImage) }}" alt="Size Guide" class="max-w-full h-auto object-contain max-h-[70vh]">
        </div>
    </div>
</div>
@endif

<!-- RELATED PRODUCTS -->
<section style="padding:80px 0;background:var(--silk);">
    <div class="wrap">
        <h2 style="font-family:'Cormorant Garamond',serif;font-size:clamp(2rem,3vw,3rem);font-weight:300;text-align:center;margin-bottom:48px;">Complete The Look</h2>
        <div class="grid-4">
            @foreach($relatedProducts as $related)
                <div class="pcard">
                  <div class="pcard-img">
                    <a href="{{ route('product.show', $related->slug) }}" style="display:block;width:100%;height:100%;">
                      <img src="{{ $related->image_url }}" alt="{{ $related->name }}" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=400&q=70'">
                    </a>
                    @if($related->badge)
                      <span class="pcard-badge">{{ $related->badge }}</span>
                    @endif
                    <button class="pcard-wish" onclick="event.preventDefault(); document.getElementById('wishlist-form-{{ $related->id }}').submit();">
                      <svg width="16" height="16" fill="{{ in_array($related->id, $wishlistIds) ? 'currentColor' : 'none' }}" class="{{ in_array($related->id, $wishlistIds) ? 'text-red-500' : 'text-primary' }}" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
                      </svg>
                    </button>
                    <a href="{{ route('product.show', $related->slug) }}" class="pcard-quick">Add to Cart</a>
                    <form id="wishlist-form-{{ $related->id }}" action="{{ route('wishlist.toggle', $related->id) }}" method="POST" style="display:none;">@csrf</form>
                  </div>
                  <div style="margin-top:14px;text-align:center;">
                    <h3 style="font-size:13px;font-weight:600;line-height:1.4;margin-bottom:6px;color:var(--primary);">
                      <a href="{{ route('product.show', $related->slug) }}" style="color:inherit;text-decoration:none;">{{ $related->name }}</a>
                    </h3>
                    <div style="display:flex;align-items:center;justify-content:center;gap:8px;">
                      <span class="eyebrow" style="color:var(--primary);">₹{{ number_format($related->price) }}</span>
                      @if($related->original_price)
                        <span style="font-size:11px;color:var(--muted);text-decoration:line-through;">₹{{ number_format($related->original_price) }}</span>
                      @endif
                    </div>
                  </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Zoom Overlay -->
<div id="zoom-overlay" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(255,255,255,0.95);z-index:99999;align-items:center;justify-content:center;cursor:zoom-out;overflow:auto;" onclick="closeZoom()">
    <button onclick="closeZoom()" style="position:fixed;top:24px;right:24px;background:none;border:none;cursor:pointer;color:var(--primary);z-index:100000;">
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
    <img id="zoom-image" src="" alt="Zoomed" style="max-width:90%;max-height:90vh;object-fit:contain;transition:transform 0.3s;cursor:zoom-in;" onclick="event.stopPropagation(); toggleZoomScale(this)">
</div>

<script>
    let isZoomedIn = false;
    function openZoom(src) {
        const overlay = document.getElementById('zoom-overlay');
        const img = document.getElementById('zoom-image');
        img.src = src;
        img.style.transform = 'scale(1)';
        isZoomedIn = false;
        overlay.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    
    function closeZoom() {
        const overlay = document.getElementById('zoom-overlay');
        overlay.style.display = 'none';
        document.body.style.overflow = '';
    }

    function toggleZoomScale(img) {
        isZoomedIn = !isZoomedIn;
        img.style.transform = isZoomedIn ? 'scale(1.5)' : 'scale(1)';
        img.style.cursor = isZoomedIn ? 'zoom-out' : 'zoom-in';
    }
</script>

<!-- Size Guide Modal -->
<div id="size-guide-modal" style="display:none;position:fixed;inset:0;background:rgba(24,24,24,0.6);z-index:99999;align-items:center;justify-content:center;padding:20px;">
    <div style="background:var(--white);width:100%;max-width:600px;max-height:90vh;overflow-y:auto;position:relative;padding:32px;">
        <button onclick="closeSizeGuide()" style="position:absolute;top:16px;right:16px;background:none;border:none;cursor:pointer;color:var(--primary);">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <h3 style="font-family:'Cormorant Garamond',serif;font-size:2rem;margin-bottom:24px;">Size Guide</h3>
        @php
            $chartImg = $product->size_chart_image ?: ($product->category ? $product->category->size_chart_image : null);
        @endphp
        @if($chartImg)
            <img src="{{ $chartImg }}" alt="Size Chart" style="width:100%;height:auto;object-fit:contain;">
        @else
            <img src="https://images.unsplash.com/photo-1599304917647-79aaad6f31ef?w=800&q=80" alt="Standard Size Chart" style="width:100%;height:auto;object-fit:contain;margin-bottom:16px;">
            <p class="text-muted text-sm text-center">Standard measurement guide. Please note variations may occur by style.</p>
        @endif
    </div>
</div>

<script>
    function openSizeGuide() {
        document.getElementById('size-guide-modal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    function closeSizeGuide() {
        document.getElementById('size-guide-modal').style.display = 'none';
        document.body.style.overflow = '';
    }
</script>

{{-- JSON-LD Product Schema for SEO --}}
<script type="application/ld+json">
{
  "@@context": "https://schema.org/",
  "@type": "Product",
  "name": "{{ $product->name }}",
  "image": [
    "{{ asset($product->image_url) }}"
    @if($product->gallery_images && is_array($product->gallery_images))
      @foreach($product->gallery_images as $img)
        ,"{{ asset($img) }}"
      @endforeach
    @endif
  ],
  "description": "{{ addslashes(strip_tags($product->description ?: ($product->seo_description ?: 'Madhavi Stores — Premium handcrafted Indian ethnic wear.'))) }}",
  "sku": "MS-PROD-{{ $product->id }}",
  "mpn": "MS-{{ $product->id }}",
  "brand": {
    "@type": "Brand",
    "name": "Madhavi Stores"
  },
  "offers": {
    "@type": "Offer",
    "url": "{{ url()->current() }}",
    "priceCurrency": "INR",
    "price": "{{ $product->price }}",
    "priceValidUntil": "{{ date('Y-m-d', strtotime('+1 year')) }}",
    "itemCondition": "https://schema.org/NewCondition",
    "availability": "{{ $product->stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' }}",
    "seller": {
      "@type": "Organization",
      "name": "Madhavi Stores"
    }
  }
}
</script>

@endsection

@section('scripts')
<script>
(function() {
  function initProductShow() {
    // Initialize Swiper for product gallery
    if(typeof Swiper !== 'undefined' && document.querySelector('.product-gallery-swiper')) {
        new Swiper('.product-gallery-swiper', {
            loop: true,
            effect: 'fade',
            fadeEffect: { crossFade: true },
            navigation: {
                nextEl: '.product-next',
                prevEl: '.product-prev',
            },
            pagination: {
                el: '.product-dots',
                clickable: true,
            },
        });
    }

    // Size selection logic
    const sizeBtns = document.querySelectorAll('.size-btn');
    const selectedSizeInput = document.getElementById('selected-size');
    const addToBagForm = document.getElementById('add-to-bag-form');

    const firstAvailable = document.querySelector('.size-btn:not([disabled])');
    if (firstAvailable) {
        selectSize(firstAvailable);
    }

    sizeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            selectSize(this);
        });
        // Tooltip logic
        btn.addEventListener('mouseenter', function() {
            const tooltip = this.querySelector('.low-stock-tooltip');
            if(tooltip) tooltip.style.display = 'block';
        });
        btn.addEventListener('mouseleave', function() {
            const tooltip = this.querySelector('.low-stock-tooltip');
            if(tooltip) tooltip.style.display = 'none';
        });
    });

    function selectSize(btn) {
        sizeBtns.forEach(b => {
            b.style.borderColor = 'var(--border)';
            b.style.background = 'transparent';
            b.style.color = 'var(--primary)';
            b.classList.remove('selected');
        });
        btn.style.borderColor = 'var(--primary)';
        btn.style.background = 'var(--primary)';
        btn.style.color = 'var(--white)';
        btn.classList.add('selected');
        if (selectedSizeInput) {
            selectedSizeInput.value = btn.getAttribute('data-size');
        }
    }

    if (addToBagForm) {
        addToBagForm.addEventListener('submit', function(e) {
            if (selectedSizeInput && !selectedSizeInput.value) {
                e.preventDefault();
                alert('Please select a size first.');
            }
        });
    }
  }

  if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initProductShow);
  } else {
      initProductShow();
  }
})();

function openSizeGuide() {
    const modal = document.getElementById('size-guide-modal');
    if(modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeSizeGuide() {
    const modal = document.getElementById('size-guide-modal');
    if(modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
}
</script>
@endsection
