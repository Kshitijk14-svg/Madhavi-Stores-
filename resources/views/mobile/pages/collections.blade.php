@extends('mobile.layouts.app')
@section('title', 'Collections — Madhavi Stores')

@section('content')
<div class="pb-24">

  {{-- Header --}}
  <div class="px-4 py-6 border-b border-gray-100">
    <p class="eyebrow" style="color:var(--secondary);margin-bottom:4px;">Explore</p>
    <h1 style="font-family:'Cormorant Garamond',serif;font-size:2rem;font-weight:300;">Collections</h1>
  </div>

  {{-- Sort Bar --}}
  <div class="flex items-center gap-2 px-4 py-3 border-b border-gray-100 overflow-x-auto hide-scrollbar">
    @foreach([
      ['sort' => 'az', 'label' => 'A–Z'],
      ['sort' => 'za', 'label' => 'Z–A'],
    ] as $opt)
      <a href="{{ route('collections.index', array_merge(request()->query(), ['sort' => $opt['sort']])) }}"
         class="shrink-0 px-4 py-2 text-[10px] font-bold tracking-wider uppercase border transition-colors {{ request('sort') === $opt['sort'] ? 'bg-primary text-white border-primary' : 'border-gray-200 text-primary' }}"
         style="min-height:44px;display:flex;align-items:center;">{{ $opt['label'] }}</a>
    @endforeach
  </div>

  {{-- Grid --}}
  @if($collections->isEmpty())
    <div class="px-6 py-16 text-center">
      <p class="text-sm text-gray-400">No collections found.</p>
    </div>
  @else
  <div class="grid grid-cols-2 gap-3 p-4">
    @foreach($collections as $collection)
    <a href="{{ route('shop', ['category' => $collection->slug]) }}"
       class="relative overflow-hidden rounded-sm aspect-square block group">
      @if($collection->image_url)
        <img src="{{ $collection->image_url }}" alt="{{ $collection->name }}"
             class="w-full h-full object-cover transition-transform duration-500 group-active:scale-105">
        <div class="absolute inset-0 bg-black/30"></div>
      @else
        <div class="w-full h-full bg-gray-100 flex items-center justify-center">
          <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24" class="text-gray-300"><rect x="3" y="3" width="18" height="18" rx="1"/></svg>
        </div>
      @endif
      <div class="absolute inset-0 flex flex-col justify-end p-3">
        <p class="text-white font-bold text-xs tracking-wider uppercase leading-tight">{{ $collection->name }}</p>
        <p class="text-white/60 text-[10px] mt-0.5">{{ $collection->products_count }} items</p>
      </div>
    </a>
    @endforeach
  </div>

  {{-- Pagination --}}
  @if($collections->hasPages())
  <div class="px-4 pb-4">
    {{ $collections->links() }}
  </div>
  @endif
  @endif

</div>
@endsection
