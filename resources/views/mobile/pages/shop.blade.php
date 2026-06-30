@extends('mobile.layouts.app')
@section('title', 'Shop — Madhavi Stores')

@section('content')
<div class="pb-24">

  {{-- Search bar --}}
  <form method="GET" action="{{ route('shop') }}" class="px-4 py-3 border-b border-gray-100 flex gap-2" style="position:relative;">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products..."
           class="flex-1 border border-gray-200 px-3 py-2.5 text-sm outline-none focus:border-primary rounded-none"
           id="mob-search-input" autocomplete="off">
    <button type="submit" class="px-4 bg-primary text-white flex items-center justify-center" style="min-height:44px;">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
    </button>
    <div id="mob-search-suggest" class="ss-box" style="left:16px;right:16px;"></div>
  </form>

  {{-- Active Filters Summary --}}
  @if(request()->hasAny(['category', 'sort', 'price_min', 'price_max', 'tags', 'filter']))
  <div class="px-4 py-2 border-b border-gray-100 flex items-center gap-2 overflow-x-auto hide-scrollbar">
    <span class="text-[10px] text-gray-400 shrink-0">Filters:</span>
    @if(request('search'))
      <a href="{{ route('shop', array_diff_key(request()->query(), ['search' => ''])) }}"
         class="shrink-0 flex items-center gap-1 bg-primary/5 border border-primary/20 px-2 py-1 text-[10px] font-bold text-primary">
         "{{ request('search') }}" <span class="text-gray-400">✕</span>
      </a>
    @endif
    @foreach((array)request('category', []) as $cat)
      <a href="{{ route('shop', array_diff_key(request()->query(), ['category' => ''])) }}"
         class="shrink-0 flex items-center gap-1 bg-primary/5 border border-primary/20 px-2 py-1 text-[10px] font-bold text-primary capitalize">
         {{ $cat }} <span class="text-gray-400">✕</span>
      </a>
    @endforeach
    <a href="{{ route('shop') }}" class="shrink-0 text-[10px] text-red-400 font-semibold ml-auto">Clear all</a>
  </div>
  @endif

  {{-- Sort + Filter Buttons --}}
  <div class="flex border-b border-gray-100">
    <button type="button" onclick="document.getElementById('sort-drawer').classList.remove('hidden')"
            class="flex-1 flex items-center justify-center gap-2 py-3 text-[11px] font-bold tracking-wider uppercase border-r border-gray-100 {{ request('sort') ? 'text-secondary' : 'text-primary' }}"
            style="min-height:44px;">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5h18M6 12h12M10 16.5h4"/></svg>
      Sort{{ request('sort') ? ' ✓' : '' }}
    </button>
    <button type="button" onclick="document.getElementById('filter-drawer').classList.remove('hidden')"
            class="flex-1 flex items-center justify-center gap-2 py-3 text-[11px] font-bold tracking-wider uppercase {{ request()->hasAny(['category','price_min','price_max','tags','filter']) ? 'text-secondary' : 'text-primary' }}"
            style="min-height:44px;">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM3.75 6H7.5M3.75 12h16.5M3.75 18h16.5"/></svg>
      Filter{{ request()->hasAny(['category','price_min','price_max','tags','filter']) ? ' ✓' : '' }}
    </button>
  </div>

  {{-- Results count + product count --}}
  <div class="px-4 py-2 text-[10px] text-gray-400 flex items-center justify-between border-b border-gray-50">
    <span>{{ $products->total() }} products</span>
    @if($products->hasPages())
      <span>Page {{ $products->currentPage() }} of {{ $products->lastPage() }}</span>
    @endif
  </div>

  {{-- Product Grid --}}
  @if($products->isEmpty())
    <div class="px-6 py-16 text-center">
      <p class="text-sm text-gray-500 mb-4">No products found.</p>
      <a href="{{ route('shop') }}" class="text-xs font-bold text-secondary underline">Clear filters</a>
    </div>
  @else
  <div class="grid grid-cols-2 gap-3 p-4">
    @foreach($products as $product)
    <div class="border border-gray-100 overflow-hidden" style="border-radius:2px;">
      <a href="{{ route('product.show', $product->slug) }}" class="block">
        <div class="aspect-[3/4] overflow-hidden bg-gray-50 relative">
          <img src="{{ $product->thumb_url }}" @if($product->card_srcset) srcset="{{ $product->card_srcset }}" sizes="50vw" @endif alt="{{ $product->name }}"
               class="w-full h-full object-cover" loading="lazy" decoding="async">
          @if($product->badge)
            <span class="absolute top-2 left-2 bg-secondary text-primary text-[9px] font-bold px-1.5 py-0.5 tracking-wider uppercase">{{ $product->badge }}</span>
          @endif
        </div>
      </a>
      <div class="p-2.5">
        <a href="{{ route('product.show', $product->slug) }}"
           class="text-xs font-semibold text-primary leading-snug line-clamp-2 block mb-1">{{ $product->name }}</a>
        <div class="flex items-center justify-between">
          <div>
            <span class="text-xs font-bold text-secondary">₹{{ number_format($product->price, 0) }}</span>
            @if($product->original_price && $product->original_price > $product->price)
              <span class="text-[10px] text-gray-400 line-through ml-1">₹{{ number_format($product->original_price, 0) }}</span>
            @endif
          </div>
        </div>
        <form method="POST" action="{{ route('cart.add') }}" class="mt-2">
          @csrf
          <input type="hidden" name="product_id" value="{{ $product->id }}">
          <input type="hidden" name="quantity" value="1">
          <button type="submit"
                  class="w-full text-[10px] font-bold tracking-wider uppercase bg-primary text-white py-2.5 transition-opacity hover:opacity-80"
                  style="min-height:36px;">
            {{ $product->has_sizes ? 'Select Size' : 'Add to Bag' }}
          </button>
        </form>
      </div>
    </div>
    @endforeach
  </div>

  {{-- Pagination --}}
  @if($products->hasPages())
  <div class="px-4 pb-4">
    {{ $products->links() }}
  </div>
  @endif
  @endif

</div>

{{-- Sort Drawer (bottom-sheet) --}}
<div id="sort-drawer" class="hidden fixed inset-0 z-50 flex flex-col justify-end">
  <div class="absolute inset-0 bg-black/40" onclick="document.getElementById('sort-drawer').classList.add('hidden')"></div>
  <div class="relative bg-white w-full" style="border-radius:16px 16px 0 0;">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
      <h3 class="text-xs font-bold tracking-widest uppercase">Sort By</h3>
      <button onclick="document.getElementById('sort-drawer').classList.add('hidden')" class="text-gray-400 text-lg leading-none">✕</button>
    </div>
    <div class="py-2" style="padding-bottom:env(safe-area-inset-bottom);">
      @foreach([
        ['sort' => 'newest',     'label' => 'Newest First'],
        ['sort' => 'price_low',  'label' => 'Price: Low to High'],
        ['sort' => 'price_high', 'label' => 'Price: High to Low'],
      ] as $opt)
        <a href="{{ route('shop', array_merge(request()->query(), ['sort' => $opt['sort']])) }}"
           class="flex items-center justify-between px-5 text-sm font-medium {{ request('sort') === $opt['sort'] ? 'text-secondary font-bold' : 'text-primary' }}"
           style="min-height:52px;border-bottom:1px solid #f5f5f5;">
          {{ $opt['label'] }}
          @if(request('sort') === $opt['sort'])
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" class="text-secondary"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
          @endif
        </a>
      @endforeach
      @if(request('sort'))
        <a href="{{ route('shop', array_diff_key(request()->query(), ['sort' => ''])) }}"
           class="flex items-center px-5 text-[11px] text-gray-400 font-semibold"
           style="min-height:44px;">Clear sort</a>
      @endif
    </div>
  </div>
</div>

{{-- Filter Drawer --}}
<div id="filter-drawer" class="hidden fixed inset-0 z-50 flex">
  <div class="absolute inset-0 bg-black/40" onclick="document.getElementById('filter-drawer').classList.add('hidden')"></div>
  <div class="relative ml-auto w-72 h-full bg-white overflow-y-auto flex flex-col">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
      <h3 class="text-xs font-bold tracking-widest uppercase">Filters</h3>
      <button onclick="document.getElementById('filter-drawer').classList.add('hidden')" class="text-gray-400 text-lg leading-none">✕</button>
    </div>
    <form method="GET" action="{{ route('shop') }}" class="flex-1 overflow-y-auto px-5 py-4 space-y-6">
      {{-- Keep search --}}
      @if(request('search'))
        <input type="hidden" name="search" value="{{ request('search') }}">
      @endif

      {{-- Price Range --}}
      <div>
        <p class="text-[10px] font-bold tracking-wider uppercase text-gray-500 mb-3">Price Range</p>
        <div class="flex gap-2">
          <input type="number" name="price_min" value="{{ request('price_min') }}" placeholder="Min"
                 class="flex-1 border border-gray-200 px-2 py-2 text-xs outline-none">
          <input type="number" name="price_max" value="{{ request('price_max') }}" placeholder="Max"
                 class="flex-1 border border-gray-200 px-2 py-2 text-xs outline-none">
        </div>
      </div>

      {{-- Categories --}}
      @if($categories->isNotEmpty())
      <div>
        <p class="text-[10px] font-bold tracking-wider uppercase text-gray-500 mb-3">Category</p>
        <div class="space-y-2">
          @foreach($categories as $cat)
          <label class="flex items-center gap-2 cursor-pointer" style="min-height:36px;">
            <input type="checkbox" name="category[]" value="{{ $cat->slug }}"
                   {{ in_array($cat->slug, (array)request('category', [])) ? 'checked' : '' }}
                   class="accent-primary">
            <span class="text-xs text-primary">{{ $cat->name }}</span>
            <span class="text-[10px] text-gray-400 ml-auto">{{ $cat->products_count }}</span>
          </label>
          @endforeach
        </div>
      </div>
      @endif

      {{-- Tags --}}
      @if(!empty($allTags))
      <div>
        <p class="text-[10px] font-bold tracking-wider uppercase text-gray-500 mb-3">Tags</p>
        <div class="flex flex-wrap gap-2">
          @foreach($allTags as $tag)
          <label class="cursor-pointer">
            <input type="checkbox" name="tags[]" value="{{ $tag }}"
                   {{ in_array($tag, (array)request('tags', [])) ? 'checked' : '' }}
                   class="sr-only peer">
            <span class="inline-block px-2 py-1 text-[10px] font-bold border border-gray-200 peer-checked:bg-primary peer-checked:text-white peer-checked:border-primary transition-colors">{{ $tag }}</span>
          </label>
          @endforeach
        </div>
      </div>
      @endif

      <button type="submit" class="w-full btn-primary py-3.5 text-xs font-bold tracking-widest uppercase" style="min-height:48px;">Apply Filters</button>
      <a href="{{ route('shop') }}" class="block w-full text-center text-xs text-gray-400 py-2">Clear all</a>
    </form>
  </div>
</div>

<script>
  // Live suggestions + recent-search history on the mobile search bar.
  // Runs on first load and on every PJAX swap (this <script> lives inside #main).
  (function(){
    if (window.MadhaviSearch) {
      window.MadhaviSearch.attach(
        document.getElementById('mob-search-input'),
        document.getElementById('mob-search-suggest'),
        { url: '{{ route('search.suggestions') }}', shopBase: '{{ route('shop') }}' }
      );
    }
    // The navbar search icon links to /shop#search — focus the box without a stale term.
    function focusSearchFromHash(){
      if (location.hash === '#search') {
        var mi = document.getElementById('mob-search-input');
        if (mi) mi.focus();
      }
    }
    focusSearchFromHash();
    // Handle tapping the search icon while already on /shop (hash change, no reload).
    if (!window._msHashBound) {
      window._msHashBound = true;
      window.addEventListener('hashchange', focusSearchFromHash);
    }
    @if(request('search'))
      if (window.MadhaviSearch) window.MadhaviSearch.pushHistory(@js(request('search')));
    @endif
  })();
</script>

@endsection
