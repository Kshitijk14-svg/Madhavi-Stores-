@extends('mobile.layouts.app')
@section('title', 'Wishlist — Madhavi Stores')

@section('content')
<div class="pb-24">

  {{-- Header --}}
  <div class="px-4 py-6 border-b border-gray-100 flex items-center justify-between">
    <div>
      <p class="eyebrow" style="color:var(--secondary);margin-bottom:2px;">Saved</p>
      <h1 style="font-family:'Cormorant Garamond',serif;font-size:1.75rem;font-weight:300;">Wishlist</h1>
    </div>
    <span class="text-xs text-gray-400">{{ $wishlistItems->count() }} items</span>
  </div>

  @if($wishlistItems->isEmpty())
    <div class="px-6 py-16 text-center">
      <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24" class="mx-auto text-gray-200 mb-4"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
      <p class="text-sm text-gray-500 mb-6">Your wishlist is empty.</p>
      <a href="{{ route('shop') }}" class="btn-primary text-sm" style="padding:12px 28px;">Browse Products</a>
    </div>
  @else
  <div class="grid grid-cols-2 gap-3 p-4">
    @foreach($wishlistItems as $item)
      @if(!$item->product) @continue @endif
      <div class="border border-gray-100 overflow-hidden" style="border-radius:2px;">
        <a href="{{ route('product.show', $item->product->slug) }}" class="block">
          <div class="aspect-[3/4] overflow-hidden bg-gray-50">
            <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}"
                 class="w-full h-full object-cover">
          </div>
        </a>
        <div class="p-2">
          <a href="{{ route('product.show', $item->product->slug) }}"
             class="text-xs font-semibold text-primary leading-snug line-clamp-2 block mb-1">{{ $item->product->name }}</a>
          <p class="text-xs text-secondary font-bold mb-2">₹{{ number_format($item->product->price, 0) }}</p>
          <div class="flex gap-1">
            <a href="{{ route('product.show', $item->product->slug) }}"
               class="flex-1 text-center text-[10px] font-bold tracking-wider uppercase bg-primary text-white py-2.5"
               style="min-height:36px;display:flex;align-items:center;justify-content:center;">Add to Bag</a>
            <form method="POST" action="{{ route('wishlist.toggle', $item->product->id) }}">
              @csrf
              <button type="submit"
                      class="w-9 h-9 flex items-center justify-center border border-gray-200 text-red-400"
                      title="Remove from wishlist">
                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
              </button>
            </form>
          </div>
        </div>
      </div>
    @endforeach
  </div>
  @endif

</div>
@endsection
