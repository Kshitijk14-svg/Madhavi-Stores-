@extends('layouts.app')
@section('title', 'My Saved Pieces | Madhavi Stores')

@section('content')

{{-- PAGE BANNER --}}
<div class="bg-silk border-b border-gray-100 py-5">
  <div class="wrap">
    <nav class="flex items-center gap-2 text-[10px] font-semibold tracking-widest uppercase text-muted mb-2">
      <a href="{{ route('home') }}" class="hover:text-secondary transition-colors">Home</a>
      <span>/</span>
      <span class="text-primary">Wishlist</span>
    </nav>
    <div class="flex items-end justify-between">
      <h1 class="font-display text-4xl lg:text-5xl italic font-light">My Saved Pieces</h1>
      <p class="text-sm text-muted hidden lg:block">{{ $wishlistItems->count() }} items saved</p>
    </div>
  </div>
</div>

<div class="wrap py-12">
  @if($wishlistItems->isEmpty())
    <div class="text-center py-20 max-w-md mx-auto space-y-6">
      <div class="w-16 h-16 bg-silk rounded-full flex items-center justify-center mx-auto text-muted/60">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
      </div>
      <h2 class="font-display text-2xl font-light">Your wishlist is empty</h2>
      <p class="text-muted text-sm font-light leading-relaxed">Discover our collection of handcrafted ethnic silhouettes, sarees, and fine textiles, and save your favorites here.</p>
      <a href="{{ route('shop') }}" class="btn-primary inline-flex mt-4">Discover Silhouettes</a>
    </div>
  @else
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      @foreach($wishlistItems as $item)
        @php $product = $item->product; @endphp
        <div class="pcard relative group">
          <div class="pcard-img">
            <img src="{{ $product->thumb_url }}" alt="{{ $product->name }}" loading="lazy" decoding="async"
                 onerror="this.src='https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=400&q=70'">
            
            {{-- Remove from Wishlist button --}}
            <form action="{{ route('wishlist.toggle', $product->id) }}" method="POST" class="absolute top-2 right-2 z-10">
              @csrf
              <button type="submit" class="flex items-center gap-1 px-2 py-1 rounded bg-white/90 shadow text-[10px] font-bold text-rose-500 hover:bg-rose-500 hover:text-white transition-all" title="Remove from wishlist">
                <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                <span class="uppercase tracking-wide">Remove</span>
              </button>
            </form>

            <a href="{{ route('product.show', $product->slug) }}" class="pcard-quick">View Details</a>
          </div>

          <div class="mt-4 space-y-1">
            <h3 class="text-sm font-semibold leading-snug">
              <a href="{{ route('product.show', $product->slug) }}" class="hover:text-secondary transition-colors">{{ $product->name }}</a>
            </h3>
            <div class="flex items-center gap-2 flex-wrap">
              <span class="eyebrow text-primary">₹{{ number_format($product->price) }}</span>
              @if($product->original_price)
                <span class="text-xs text-muted line-through">₹{{ number_format($product->original_price) }}</span>
              @endif
            </div>

            {{-- Dynamic Quick-Add to bag --}}
            <form action="{{ route('cart.add') }}" method="POST" class="pt-2">
              @csrf
              <input type="hidden" name="product_id" value="{{ $product->id }}">
              <input type="hidden" name="size" value="M">
              <input type="hidden" name="quantity" value="1">
              <button type="submit" class="w-full text-center py-2 text-[10px] font-bold tracking-wider uppercase bg-secondary text-ink border border-secondary hover:bg-[#d9a91f] transition-all">
                Move to Shopping Bag
              </button>
            </form>
          </div>
        </div>
      @endforeach
    </div>
  @endif
</div>

@endsection
