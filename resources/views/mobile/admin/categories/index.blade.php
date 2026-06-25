@extends('admin.layout')
@section('admin_title', 'Collections')

@section('admin_content')
<div class="mb-4">
  <h2 class="font-display text-2xl text-primary">Manage Collections</h2>
  <p class="text-[11px] text-muted mt-0.5">Organize the catalog into brand collections.</p>
</div>

{{-- Actions --}}
<div class="grid grid-cols-2 gap-3 mb-5">
  <a href="{{ route('admin.categories.create') }}"
     class="text-center text-[10px] font-bold tracking-widest uppercase text-white bg-primary py-3">✦ Add Collection</a>
  <button type="button" onclick="document.getElementById('cat-filters').classList.toggle('hidden')"
          class="text-center text-[10px] font-bold tracking-widest uppercase text-primary border border-gray-300 py-3">Filters</button>
</div>

<div id="cat-filters" class="hidden mb-6">
  <form action="{{ route('admin.categories.index') }}" method="GET" class="bg-white border border-gray-200 p-4 space-y-3">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or slug..."
           class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2.5 outline-none">
    <select name="product_count_range" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2.5 outline-none">
      <option value="">All Sizes</option>
      <option value="empty" {{ request('product_count_range') === 'empty' ? 'selected' : '' }}>Empty (0 Items)</option>
      <option value="1_10" {{ request('product_count_range') === '1_10' ? 'selected' : '' }}>Small (1–10)</option>
      <option value="11_50" {{ request('product_count_range') === '11_50' ? 'selected' : '' }}>Medium (11–50)</option>
      <option value="50_plus" {{ request('product_count_range') === '50_plus' ? 'selected' : '' }}>Large (50+)</option>
    </select>
    <select name="sort_by" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2.5 outline-none">
      <option value="latest" {{ request('sort_by') === 'latest' || !request('sort_by') ? 'selected' : '' }}>Latest</option>
      <option value="name_a_z" {{ request('sort_by') === 'name_a_z' ? 'selected' : '' }}>Name: A to Z</option>
      <option value="name_z_a" {{ request('sort_by') === 'name_z_a' ? 'selected' : '' }}>Name: Z to A</option>
      <option value="products_desc" {{ request('sort_by') === 'products_desc' ? 'selected' : '' }}>Items: High to Low</option>
      <option value="products_asc" {{ request('sort_by') === 'products_asc' ? 'selected' : '' }}>Items: Low to High</option>
    </select>
    <div class="flex gap-2">
      <button type="submit" class="flex-1 btn-primary !py-2.5 text-[10px] uppercase tracking-wider">Apply</button>
      <a href="{{ route('admin.categories.index') }}" class="flex-1 text-center text-[10px] uppercase tracking-wider border border-gray-200 text-muted py-2.5">Clear</a>
    </div>
  </form>
</div>

{{-- Collection cards --}}
<div class="space-y-3">
  @forelse($categories as $category)
    <div class="border border-gray-100 bg-white p-4">
      <div class="flex items-center gap-3">
        @if($category->image_url)
          <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="w-14 h-14 rounded-full object-cover bg-silk border border-gray-100 shrink-0">
        @else
          <div class="w-14 h-14 rounded-full bg-silk flex items-center justify-center text-[9px] text-muted border border-gray-100 shrink-0">No Image</div>
        @endif
        <div class="min-w-0">
          <h3 class="font-display text-lg text-primary truncate">{{ $category->name }}</h3>
          <p class="text-[10px] text-muted tracking-widest uppercase">{{ $category->products_count }} items</p>
          <p class="text-[10px] text-muted truncate">Slug: {{ $category->slug }}</p>
        </div>
      </div>
      <div class="flex items-center justify-end gap-2 mt-3 pt-3 border-t border-gray-100">
        <a href="{{ route('admin.categories.edit', $category->id) }}"
           class="text-[10px] font-bold tracking-wider uppercase text-primary border border-gray-300 px-3 py-1.5">Edit</a>
        <form action="{{ route('admin.categories.delete', $category->id) }}" method="POST"
              onsubmit="return confirm('Delete this collection? This removes associated products.');">
          @csrf
          <button type="submit" class="text-[10px] font-bold tracking-wider uppercase text-rose-600 border border-rose-200 px-3 py-1.5">Delete</button>
        </form>
      </div>
    </div>
  @empty
    <div class="text-center py-10 text-muted text-sm border border-dashed border-gray-200">No collections found.</div>
  @endforelse
</div>
@endsection
