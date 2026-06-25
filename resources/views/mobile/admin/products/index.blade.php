@extends('admin.layout')
@section('admin_title', 'Products')

@section('admin_content')
<div class="mb-4">
  <h2 class="font-display text-2xl text-primary">Manage Products</h2>
  <p class="text-[11px] text-muted mt-0.5">Create, update, or remove catalog items.</p>
</div>

{{-- Action buttons --}}
<div class="grid grid-cols-2 gap-3 mb-5">
  <a href="{{ route('admin.products.create') }}"
     class="text-center text-[10px] font-bold tracking-widest uppercase text-white bg-primary py-3">✦ Add Product</a>
  <button type="button" onclick="document.getElementById('product-filters').classList.toggle('hidden')"
          class="text-center text-[10px] font-bold tracking-widest uppercase text-primary border border-gray-300 py-3">Filters</button>
</div>

{{-- Collapsible filters --}}
<div id="product-filters" class="hidden mb-6">
  <form action="{{ route('admin.products.index') }}" method="GET" class="bg-white border border-gray-200 p-4 space-y-3">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, slug, tags..."
           class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2.5 outline-none">
    <select name="category_id" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2.5 outline-none">
      <option value="">All Collections</option>
      @foreach($categories as $category)
        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
      @endforeach
    </select>
    <select name="stock_status" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2.5 outline-none">
      <option value="">All Stock Status</option>
      <option value="in_stock" {{ request('stock_status') === 'in_stock' ? 'selected' : '' }}>In Stock</option>
      <option value="out_of_stock" {{ request('stock_status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
    </select>
    <select name="sort_by" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2.5 outline-none">
      <option value="latest" {{ request('sort_by') === 'latest' || !request('sort_by') ? 'selected' : '' }}>Latest</option>
      <option value="price_low_high" {{ request('sort_by') === 'price_low_high' ? 'selected' : '' }}>Price: Low to High</option>
      <option value="price_high_low" {{ request('sort_by') === 'price_high_low' ? 'selected' : '' }}>Price: High to Low</option>
      <option value="name_a_z" {{ request('sort_by') === 'name_a_z' ? 'selected' : '' }}>Name: A to Z</option>
      <option value="name_z_a" {{ request('sort_by') === 'name_z_a' ? 'selected' : '' }}>Name: Z to A</option>
      <option value="bestseller" {{ request('sort_by') === 'bestseller' ? 'selected' : '' }}>Bestsellers First</option>
    </select>
    <div class="flex gap-2">
      <button type="submit" class="flex-1 btn-primary !py-2.5 text-[10px] uppercase tracking-wider">Apply</button>
      <a href="{{ route('admin.products.index') }}" class="flex-1 text-center text-[10px] uppercase tracking-wider border border-gray-200 text-muted py-2.5">Clear</a>
    </div>
  </form>
</div>

{{-- Product cards --}}
<div class="space-y-3">
  @forelse($products as $product)
    <div class="border border-gray-100 bg-white p-3">
      <div class="flex items-start gap-3">
        @if($product->image_url)
          <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-14 h-16 object-cover bg-silk shrink-0">
        @else
          <div class="w-14 h-16 bg-silk flex items-center justify-center text-[9px] text-muted shrink-0">No Image</div>
        @endif
        <div class="flex-1 min-w-0">
          <div class="flex items-start justify-between gap-2">
            <p class="text-sm font-medium text-primary leading-tight">{{ $product->name }}</p>
            <span class="text-[9px] font-mono text-muted shrink-0">#{{ str_pad($product->id, 4, '0', STR_PAD_LEFT) }}</span>
          </div>
          <p class="text-[10px] text-muted truncate mt-0.5">{{ $product->slug }}</p>
          <p class="text-[10px] text-muted mt-0.5">{{ $product->category->name ?? 'No collection' }}</p>
          <div class="flex items-center gap-2 mt-1">
            <span class="text-sm font-semibold text-primary">₹{{ number_format($product->price, 2) }}</span>
            @if($product->original_price)
              <span class="text-[10px] text-muted line-through">₹{{ number_format($product->original_price, 2) }}</span>
            @endif
          </div>
        </div>
      </div>
      <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
        @if($product->is_bestseller)
          <span class="text-[8px] font-bold uppercase text-emerald-700 bg-emerald-50 px-1.5 py-0.5 border border-emerald-200">Bestseller</span>
        @else
          <span class="text-[8px] font-bold uppercase text-gray-500 bg-gray-50 px-1.5 py-0.5 border border-gray-200">Standard</span>
        @endif
        <div class="flex items-center gap-2">
          <a href="{{ route('admin.products.edit', $product->id) }}"
             class="text-[10px] font-bold tracking-wider uppercase text-primary border border-gray-300 px-3 py-1.5">Edit</a>
          <form action="{{ route('admin.products.delete', $product->id) }}" method="POST"
                onsubmit="return confirm('Delete this product?');">
            @csrf
            <button type="submit" class="text-[10px] font-bold tracking-wider uppercase text-rose-600 border border-rose-200 px-3 py-1.5">Delete</button>
          </form>
        </div>
      </div>
    </div>
  @empty
    <div class="text-center py-10 text-muted text-sm border border-dashed border-gray-200">No products found.</div>
  @endforelse
</div>

<div class="mt-6">
  {{ $products->links() }}
</div>
@endsection
