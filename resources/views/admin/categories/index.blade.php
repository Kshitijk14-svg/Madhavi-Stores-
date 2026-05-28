@extends('admin.layout')

@section('admin_content')
<div>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <h2 class="font-display text-2xl mb-1 text-primary">Manage Collections</h2>
            <p class="text-xs text-muted">Organize your clothing catalog into distinct brand collections.</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="btn-primary !py-3 !px-6 text-[10px] tracking-[0.25em]">
            ✦ Add New Collection
        </a>
    </div>

    {{-- Search and Filter Form --}}
    <form action="{{ route('admin.categories.index') }}" method="GET" class="bg-white border border-gray-150 p-6 mb-8">
        <div class="flex flex-wrap gap-4 items-center justify-between">
            <div class="flex flex-wrap gap-3 items-center flex-1 max-w-4xl">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Search collection name, slug..." 
                       class="text-xs text-primary bg-white border border-gray-200 px-4 py-2.5 outline-none w-full sm:w-64">

                <select name="product_count_range" class="text-xs text-primary bg-white border border-gray-200 px-4 py-2.5 outline-none">
                    <option value="">All Sizes</option>
                    <option value="empty" {{ request('product_count_range') === 'empty' ? 'selected' : '' }}>Empty Collections (0 Items)</option>
                    <option value="1_10" {{ request('product_count_range') === '1_10' ? 'selected' : '' }}>Small (1 - 10 Items)</option>
                    <option value="11_50" {{ request('product_count_range') === '11_50' ? 'selected' : '' }}>Medium (11 - 50 Items)</option>
                    <option value="50_plus" {{ request('product_count_range') === '50_plus' ? 'selected' : '' }}>Large (50+ Items)</option>
                </select>

                <select name="sort_by" class="text-xs text-primary bg-white border border-gray-200 px-4 py-2.5 outline-none">
                    <option value="latest" {{ request('sort_by') === 'latest' || !request('sort_by') ? 'selected' : '' }}>Latest</option>
                    <option value="name_a_z" {{ request('sort_by') === 'name_a_z' ? 'selected' : '' }}>Name: A to Z</option>
                    <option value="name_z_a" {{ request('sort_by') === 'name_z_a' ? 'selected' : '' }}>Name: Z to A</option>
                    <option value="products_desc" {{ request('sort_by') === 'products_desc' ? 'selected' : '' }}>Items: High to Low</option>
                    <option value="products_asc" {{ request('sort_by') === 'products_asc' ? 'selected' : '' }}>Items: Low to High</option>
                </select>
            </div>
            
            <div class="flex items-center gap-2">
                <button type="submit" class="btn-primary !py-2.5 !px-5 text-[9px] tracking-[0.15em] font-semibold uppercase">
                    Apply Filters
                </button>
                <a href="{{ route('admin.categories.index') }}" class="btn-secondary !py-2.5 !px-5 text-[9px] tracking-[0.15em] font-semibold uppercase text-center">
                    Clear
                </a>
            </div>
        </div>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($categories as $category)
        <div class="border border-gray-100 bg-slate-50/20 p-6 flex flex-col justify-between hover:border-secondary/40 transition-colors">
            <div class="flex items-start gap-4 mb-6">
                @if($category->image_url)
                    <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="w-16 h-16 rounded-full object-cover bg-silk border border-gray-100 flex-shrink-0">
                @else
                    <div class="w-16 h-16 rounded-full bg-silk flex items-center justify-center text-[10px] text-muted border border-gray-100 flex-shrink-0">No Image</div>
                @endif
                <div>
                    <h3 class="font-display text-xl text-primary mt-1">{{ $category->name }}</h3>
                    <p class="text-[10px] text-muted tracking-widest uppercase mt-0.5">{{ $category->products_count }} items in collection</p>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-4 flex justify-between items-center text-xs">
                <span class="text-[10px] text-muted font-bold tracking-wider uppercase">Slug: {{ $category->slug }}</span>
                <div class="flex gap-4">
                    <a href="{{ route('admin.categories.edit', $category->id) }}" class="text-secondary hover:text-primary font-bold tracking-wider uppercase transition-colors">Edit</a>
                    
                    <form action="{{ route('admin.categories.delete', $category->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this collection? This will remove all associated products.');">
                        @csrf
                        <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold tracking-wider uppercase transition-colors bg-transparent border-none p-0 cursor-pointer">Delete</button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-3 text-center py-8 text-muted text-sm border border-dashed border-gray-200">
            No collections found. Create a new collection to get started.
        </div>
        @endforelse
    </div>
</div>
@endsection
