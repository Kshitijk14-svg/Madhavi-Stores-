@extends('layouts.app')
@section('title', 'Shop Women | Madhavi Stores')

@section('content')

{{-- PAGE BANNER --}}
<div class="bg-silk border-b border-gray-100 py-5">
  <div class="wrap">
    <nav class="flex items-center gap-2 text-[10px] font-semibold tracking-widest uppercase text-muted mb-2">
      <a href="{{ route('home') }}" class="hover:text-secondary transition-colors">Home</a>
      <span>/</span>
      <span class="text-primary">All Products</span>
    </nav>
    <div class="flex items-end justify-between">
      <h1 class="font-display text-4xl lg:text-5xl italic font-light">
        @if(request('category'))
          {{ $categories->where('slug', request('category'))->first()->name ?? 'Women\'s Collection' }}
        @elseif(request('filter') === 'new-arrivals' || request('filter') === 'new_arrivals')
          New Arrivals
        @elseif(request('filter') === 'bestsellers' || request('filter') === 'bestseller')
          Bestsellers
        @else
          Women's Collection
        @endif
      </h1>
      <p class="text-sm text-muted hidden lg:block">{{ $products->count() }} products</p>
    </div>
  </div>
</div>

{{-- LAYOUT --}}
<div class="wrap py-10">
  <div class="flex flex-col lg:flex-row gap-10">

    {{-- SIDEBAR --}}
    <aside class="hidden lg:block w-full lg:w-56 xl:w-64 flex-shrink-0">
      <form action="{{ route('shop') }}" method="GET" id="filter-form" class="lg:sticky lg:top-32 space-y-8">
        {{-- Preserve Search and Sort --}}
        @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
        @if(request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif

        {{-- Category filter --}}
        <div>
          <h3 class="text-[10px] font-bold tracking-[0.35em] uppercase text-primary mb-4 pb-3 border-b border-gray-100">Collections</h3>
          <ul class="space-y-3">
            {{-- Permanent special filters --}}
            <li>
              <label class="flex items-center gap-3 cursor-pointer group">
                <input type="checkbox" name="new_arrivals" value="1" class="accent-secondary w-4 h-4 cursor-pointer" 
                       {{ request('new_arrivals') == '1' || request('filter') === 'new-arrivals' || request('filter') === 'new_arrivals' ? 'checked' : '' }}>
                <span class="text-sm text-muted group-hover:text-primary transition-colors flex-1">New Arrivals</span>
                <span class="text-[10px] text-muted/50">{{ \App\Models\Product::where('is_new_arrival', true)->exists() ? \App\Models\Product::where('is_new_arrival', true)->count() : \App\Models\Product::count() }}</span>
              </label>
            </li>
            <li>
              <label class="flex items-center gap-3 cursor-pointer group">
                <input type="checkbox" name="bestsellers" value="1" class="accent-secondary w-4 h-4 cursor-pointer" 
                       {{ request('bestsellers') == '1' || request('filter') === 'bestsellers' || request('filter') === 'bestseller' ? 'checked' : '' }}>
                <span class="text-sm text-muted group-hover:text-primary transition-colors flex-1">Bestsellers</span>
                <span class="text-[10px] text-muted/50">{{ \App\Models\Product::where('is_bestseller', true)->exists() ? \App\Models\Product::where('is_bestseller', true)->count() : \App\Models\Product::count() }}</span>
              </label>
            </li>
            <li class="border-b border-gray-100 my-2"></li>

            @foreach($categories as $cat)
              <li>
                <label class="flex items-center gap-3 cursor-pointer group">
                  <input type="checkbox" name="category[]" value="{{ $cat->slug }}" class="accent-secondary w-4 h-4 cursor-pointer" 
                         {{ in_array($cat->slug, (array)request('category', [])) ? 'checked' : '' }}>
                  <span class="text-sm text-muted group-hover:text-primary transition-colors flex-1">{{ $cat->name }}</span>
                  <span class="text-[10px] text-muted/50">{{ $cat->products_count }}</span>
                </label>
              </li>
            @endforeach
          </ul>
        </div>

        {{-- Tags filter --}}
        @if(!empty($allTags))
        <div>
          <h3 class="text-[10px] font-bold tracking-[0.35em] uppercase text-primary mb-4 pb-3 border-b border-gray-100">Tags</h3>
          <ul class="space-y-3 max-h-48 overflow-y-auto pr-2">
            @foreach($allTags as $tag)
              <li>
                <label class="flex items-center gap-3 cursor-pointer group">
                  <input type="checkbox" name="tags[]" value="{{ $tag }}" class="accent-secondary w-4 h-4 cursor-pointer"
                         {{ in_array($tag, (array)request('tags', [])) ? 'checked' : '' }}>
                  <span class="text-sm text-muted group-hover:text-primary transition-colors">{{ $tag }}</span>
                </label>
              </li>
            @endforeach
          </ul>
        </div>
        @endif

        {{-- Price Range --}}
        <div>
          <h3 class="text-[10px] font-bold tracking-[0.35em] uppercase text-primary mb-4 pb-3 border-b border-gray-100">Price</h3>
          <div class="flex items-center gap-2">
            <input type="number" name="price_min" placeholder="Min" value="{{ request('price_min') }}" class="w-full text-sm border border-gray-200 px-3 py-2 outline-none focus:border-secondary transition-colors" min="0">
            <span class="text-muted">-</span>
            <input type="number" name="price_max" placeholder="Max" value="{{ request('price_max') }}" class="w-full text-sm border border-gray-200 px-3 py-2 outline-none focus:border-secondary transition-colors" min="0">
          </div>
        </div>

        <button type="submit" class="w-full btn-primary py-3 text-[10px]">Apply Filters</button>
        @if(request()->anyFilled(['category', 'tags', 'price_min', 'price_max', 'new_arrivals', 'bestsellers', 'filter']))
            <a href="{{ route('shop') }}" class="block text-center text-xs text-secondary mt-3 hover:underline">Clear Filters</a>
        @endif
      </form>
    </aside>

    {{-- PRODUCT GRID --}}
    <div class="flex-1">

      @if(request('search'))
        <div class="mb-6 p-4 bg-silk/40 rounded flex items-center justify-between">
          <p class="text-sm text-muted">
            Search results for "<span class="font-semibold text-primary">{{ request('search') }}</span>"
          </p>
          <a href="{{ route('shop') }}" class="text-xs text-secondary hover:underline">Clear Search</a>
        </div>
      @endif

      {{-- Mobile sort bar is moved to drawer --}}
      <div class="lg:hidden mb-4">
        <p class="text-sm text-muted">{{ $products->count() }} products</p>
      </div>
      
      {{-- Desktop sort --}}
      <div class="hidden lg:flex justify-end mb-6">
        <select form="filter-form" name="sort" onchange="this.form.submit()" class="text-sm text-primary bg-transparent border border-gray-200 px-3 py-2 outline-none w-48">
          <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest First</option>
          <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Price: Low → High</option>
          <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Price: High → Low</option>
        </select>
      </div>

      @if($products->isEmpty())
        <div class="text-center py-20 bg-background rounded-lg border border-dashed border-gray-200">
          <p class="text-muted mb-4">No products found matching your criteria.</p>
          <a href="{{ route('shop') }}" class="btn-primary inline-flex">View All Products</a>
        </div>
      @else
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-3 md:gap-4 lg:gap-6">
          @foreach($products as $product)
            <div class="pcard">
              <div class="pcard-img">
                <a href="{{ route('product.show', $product->slug) }}" style="display:block;width:100%;height:100%;">
                  <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy"
                       onerror="this.src='https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=400&q=70'">
                </a>
                @if($product->badge)
                  <span class="pcard-badge">{{ $product->badge }}</span>
                @endif
                
                {{-- Heart form --}}
                <form action="{{ route('wishlist.toggle', $product->id) }}" method="POST" class="absolute top-3 right-3 z-10">
                  @csrf
                  <button type="submit" class="w-8 h-8 rounded-full bg-white/90 shadow-md flex items-center justify-center hover:text-red-500 transition-colors {{ in_array($product->id, $wishlistIds) ? 'text-red-500' : 'text-primary' }}" title="Save Piece">
                    <svg class="w-4 h-4" fill="{{ in_array($product->id, $wishlistIds) ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                      <path d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
                    </svg>
                  </button>
                </form>

                <a href="{{ route('product.show', $product->slug) }}" class="pcard-quick">View Details</a>
              </div>
              <div class="mt-4">
                <h3 class="text-sm font-semibold leading-snug mb-2">
                  <a href="{{ route('product.show', $product->slug) }}" class="hover:text-secondary transition-colors">{{ $product->name }}</a>
                </h3>
                <div class="flex items-center gap-2 flex-wrap">
                  <span class="eyebrow text-primary">₹{{ number_format($product->price) }}</span>
                  @if($product->original_price)
                    <span class="text-xs text-muted line-through">₹{{ number_format($product->original_price) }}</span>
                    <span class="text-[10px] font-bold text-green-600 bg-green-50 px-1.5 py-0.5">
                      {{ round((($product->original_price - $product->price) / $product->original_price) * 100) }}% OFF
                    </span>
                  @endif
                </div>
                
                {{-- Quick Add bag --}}
                <form action="{{ route('cart.add') }}" method="POST" class="mt-3">
                  @csrf
                  <input type="hidden" name="product_id" value="{{ $product->id }}">
                  @if($product->has_sizes)
                    <input type="hidden" name="size" value="M">
                  @endif
                  <input type="hidden" name="quantity" value="1">
                  <button type="submit" class="w-full text-center py-2 text-[10px] font-bold tracking-wider uppercase bg-secondary text-ink border border-secondary hover:bg-[#d9a91f] transition-all {{ !$product->has_sizes && $product->stock <= 0 ? 'opacity-50 cursor-not-allowed' : '' }}" {{ !$product->has_sizes && $product->stock <= 0 ? 'disabled' : '' }}>
                    {{ !$product->has_sizes && $product->stock <= 0 ? 'Out Of Stock' : 'Add To Bag' }}
                  </button>
                </form>

              </div>
            </div>
          @endforeach
        </div>
        <div class="mt-10">
          {{ $products->links() }}
        </div>
      @endif

    </div>
  </div>
{{-- ══ MOBILE FILTER BUTTON ══ --}}
<div class="lg:hidden fixed bottom-[calc(70px+env(safe-area-inset-bottom))] left-1/2 -translate-x-1/2 z-40">
  <button id="mob-filter-open" class="bg-primary text-white px-6 py-3 rounded-full shadow-[0_8px_30px_rgb(0,0,0,0.12)] text-[10px] font-bold tracking-[0.2em] uppercase flex items-center gap-2">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/></svg>
    Filter & Sort
  </button>
</div>

{{-- ══ MOBILE FILTER DRAWER ══ --}}
<div class="drawer-backdrop" id="filter-back"></div>
<div class="mob-drawer-bottom" id="filter-panel">
  <div class="flex items-center justify-between p-5 border-b border-gray-100">
    <span class="text-[10px] font-bold tracking-widest uppercase text-primary">Filter & Sort</span>
    <button id="filter-close" class="text-muted hover:text-primary">
      <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
  </div>
  <div class="flex-1 overflow-y-auto p-5">
    <form action="{{ route('shop') }}" method="GET" id="filter-form-mob" class="space-y-8">
      @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
      
      <div>
        <h3 class="text-[10px] font-bold tracking-[0.35em] uppercase text-primary mb-4 pb-3 border-b border-gray-100">Sort By</h3>
        <select name="sort" class="w-full text-sm border border-gray-200 px-3 py-3 outline-none focus:border-secondary">
          <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest</option>
          <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Price: Low-High</option>
          <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Price: High-Low</option>
        </select>
      </div>

      <div>
        <h3 class="text-[10px] font-bold tracking-[0.35em] uppercase text-primary mb-4 pb-3 border-b border-gray-100">Collections</h3>
        <ul class="space-y-4">
          <li>
            <label class="flex items-center gap-3 cursor-pointer">
              <input type="checkbox" name="new_arrivals" value="1" class="accent-secondary w-5 h-5 cursor-pointer" {{ request('new_arrivals') == '1' || request('filter') === 'new-arrivals' || request('filter') === 'new_arrivals' ? 'checked' : '' }}>
              <span class="text-sm">New Arrivals</span>
            </label>
          </li>
          <li>
            <label class="flex items-center gap-3 cursor-pointer">
              <input type="checkbox" name="bestsellers" value="1" class="accent-secondary w-5 h-5 cursor-pointer" {{ request('bestsellers') == '1' || request('filter') === 'bestsellers' || request('filter') === 'bestseller' ? 'checked' : '' }}>
              <span class="text-sm">Bestsellers</span>
            </label>
          </li>
          <li class="border-b border-gray-100 my-2"></li>
          @foreach($categories as $cat)
            <li>
              <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="category[]" value="{{ $cat->slug }}" class="accent-secondary w-5 h-5 cursor-pointer" {{ in_array($cat->slug, (array)request('category', [])) ? 'checked' : '' }}>
                <span class="text-sm">{{ $cat->name }}</span>
              </label>
            </li>
          @endforeach
        </ul>
      </div>

      <div>
        <h3 class="text-[10px] font-bold tracking-[0.35em] uppercase text-primary mb-4 pb-3 border-b border-gray-100">Price</h3>
        <div class="flex items-center gap-2">
          <input type="number" name="price_min" placeholder="Min" value="{{ request('price_min') }}" class="w-full text-sm border border-gray-200 px-4 py-3 outline-none" min="0">
          <span class="text-muted">-</span>
          <input type="number" name="price_max" placeholder="Max" value="{{ request('price_max') }}" class="w-full text-sm border border-gray-200 px-4 py-3 outline-none" min="0">
        </div>
      </div>
      
    </form>
  </div>
  <div class="p-5 border-t border-gray-100">
    <button type="submit" form="filter-form-mob" class="w-full btn-primary py-4 text-[10px]">Apply Filters</button>
  </div>
</div>

<script>
  document.getElementById('mob-filter-open')?.addEventListener('click', function(){
    document.getElementById('filter-back').classList.add('open');
    document.getElementById('filter-panel').classList.add('open');
  });
  document.getElementById('filter-close')?.addEventListener('click', function(){
    document.getElementById('filter-back').classList.remove('open');
    document.getElementById('filter-panel').classList.remove('open');
  });
  document.getElementById('filter-back')?.addEventListener('click', function(){
    document.getElementById('filter-back').classList.remove('open');
    document.getElementById('filter-panel').classList.remove('open');
  });
</script>

@endsection
