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
    <aside class="w-full lg:w-56 xl:w-64 flex-shrink-0">
      <div class="lg:sticky lg:top-32 space-y-8">

        {{-- Category filter --}}
        <div>
          <h3 class="text-[10px] font-bold tracking-[0.35em] uppercase text-primary mb-4 pb-3 border-b border-gray-100">Categories</h3>
          <ul class="space-y-3">
            <li>
              <a href="{{ route('collection') }}" class="flex items-center justify-between text-sm text-muted hover:text-primary transition-colors {{ !request('category') ? '!text-primary font-semibold' : '' }}">
                <span>All Products</span>
                <span class="text-[10px] text-muted/50">{{ \App\Models\Product::count() }}</span>
              </a>
            </li>
            @foreach($categories as $cat)
              <li>
                <a href="{{ route('collection') }}?category={{ $cat->slug }}{{ request('search') ? '&search='.urlencode(request('search')) : '' }}{{ request('sort') ? '&sort='.request('sort') : '' }}" class="flex items-center justify-between text-sm text-muted hover:text-primary transition-colors {{ request('category') === $cat->slug ? '!text-primary font-semibold' : '' }}">
                  <span>{{ $cat->name }}</span>
                  <span class="text-[10px] text-muted/50">{{ $cat->products()->count() }}</span>
                </a>
              </li>
            @endforeach
          </ul>
        </div>

        {{-- Sort --}}
        <div>
          <h3 class="text-[10px] font-bold tracking-[0.35em] uppercase text-primary mb-4 pb-3 border-b border-gray-100">Sort By</h3>
          <select onchange="window.location.href = this.value" class="w-full text-sm text-primary bg-transparent border border-gray-200 px-3 py-2.5 outline-none focus:border-secondary transition-colors cursor-pointer">
            <option value="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest First</option>
            <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_low']) }}" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Price: Low → High</option>
            <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_high']) }}" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Price: High → Low</option>
          </select>
        </div>

      </div>
    </aside>

    {{-- PRODUCT GRID --}}
    <div class="flex-1">

      @if(request('search'))
        <div class="mb-6 p-4 bg-silk/40 rounded flex items-center justify-between">
          <p class="text-sm text-muted">
            Search results for "<span class="font-semibold text-primary">{{ request('search') }}</span>"
          </p>
          <a href="{{ route('collection') }}" class="text-xs text-secondary hover:underline">Clear Search</a>
        </div>
      @endif

      {{-- Mobile sort bar --}}
      <div class="flex items-center justify-between mb-6 lg:hidden">
        <p class="text-sm text-muted">{{ $products->count() }} products</p>
        <select onchange="window.location.href = this.value" class="text-sm text-primary bg-transparent border border-gray-200 px-3 py-2 outline-none">
          <option value="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest</option>
          <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_low']) }}" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Price: Low-High</option>
          <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_high']) }}" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Price: High-Low</option>
        </select>
      </div>

      @if($products->isEmpty())
        <div class="text-center py-20 bg-background rounded-lg border border-dashed border-gray-200">
          <p class="text-muted mb-4">No products found matching your criteria.</p>
          <a href="{{ route('collection') }}" class="btn-primary inline-flex">View All Products</a>
        </div>
      @else
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
          @foreach($products as $product)
            <div class="pcard">
              <div class="pcard-img">
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy"
                     onerror="this.src='https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=400&q=70'">
                @if($product->badge)
                  <span class="pcard-badge">{{ $product->badge }}</span>
                @endif
                
                {{-- Heart form --}}
                <form action="{{ route('wishlist.toggle', $product->id) }}" method="POST" class="absolute top-3 right-3 z-10">
                  @csrf
                  <button type="submit" class="w-8 h-8 rounded-full bg-white/90 shadow-md flex items-center justify-center hover:text-red-500 transition-colors {{ Auth::check() && Auth::user()->wishlistItems()->where('product_id', $product->id)->exists() ? 'text-red-500' : 'text-primary' }}" title="Save Piece">
                    <svg class="w-4 h-4" fill="{{ Auth::check() && Auth::user()->wishlistItems()->where('product_id', $product->id)->exists() ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
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
                  <input type="hidden" name="size" value="M">
                  <input type="hidden" name="quantity" value="1">
                  <button type="submit" class="w-full text-center py-2 text-[10px] font-bold tracking-wider uppercase border border-primary text-primary hover:bg-primary hover:text-white transition-all">
                    Add To Bag
                  </button>
                </form>

              </div>
            </div>
          @endforeach
        </div>
      @endif

    </div>
  </div>
</div>

@endsection
