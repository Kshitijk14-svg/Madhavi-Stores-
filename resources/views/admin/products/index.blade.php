@extends('admin.layout')

@section('admin_content')
<div>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <h2 class="font-display text-2xl mb-1 text-primary">Manage Products</h2>
            <p class="text-xs text-muted">Create, update, or remove products from the storefront catalog.</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="btn-primary !py-3 !px-6 text-[10px] tracking-[0.25em]">
            ✦ Add New Product
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left">
            <thead>
                <tr class="border-b border-gray-100 text-[10px] font-bold tracking-widest uppercase text-muted">
                    <th class="py-4">Product</th>
                    <th class="py-4">Collection</th>
                    <th class="py-4">Price</th>
                    <th class="py-4">Original Price</th>
                    <th class="py-4">Featured</th>
                    <th class="py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 text-sm">
                @forelse($products as $product)
                <tr class="hover:bg-slate-50/30 transition-colors">
                    <td class="py-4 flex items-center gap-3">
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-10 h-12 object-cover bg-silk">
                        @else
                            <div class="w-10 h-12 bg-silk flex items-center justify-center text-xs text-muted">No Image</div>
                        @endif
                        <div>
                            <p class="font-medium text-primary">{{ $product->name }}</p>
                            <span class="text-[10px] text-muted tracking-wide">{{ $product->slug }}</span>
                        </div>
                    </td>
                    <td class="py-4 text-muted">
                        {{ $product->category->name ?? 'None' }}
                    </td>
                    <td class="py-4 font-medium text-primary">
                        ₹{{ number_format($product->price, 2) }}
                    </td>
                    <td class="py-4 text-muted">
                        {{ $product->original_price ? '₹' . number_format($product->original_price, 2) : '—' }}
                    </td>
                    <td class="py-4">
                        @if($product->is_bestseller)
                            <span class="text-[9px] font-bold tracking-widest uppercase text-emerald-700 bg-emerald-50 px-2.5 py-0.5 border border-emerald-200">Bestseller</span>
                        @else
                            <span class="text-[9px] font-bold tracking-widest uppercase text-gray-500 bg-gray-50 px-2.5 py-0.5 border border-gray-200">Standard</span>
                        @endif
                    </td>
                    <td class="py-4 text-right">
                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('admin.products.edit', $product->id) }}" class="text-xs text-secondary hover:text-primary font-bold tracking-wider uppercase transition-colors">Edit</a>
                            
                            <form action="{{ route('admin.products.delete', $product->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                @csrf
                                <button type="submit" class="text-xs text-rose-600 hover:text-rose-800 font-bold tracking-wider uppercase transition-colors bg-transparent border-none p-0 cursor-pointer">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-muted text-sm">No products found in the catalog.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-8">
        {{ $products->links() }}
    </div>
</div>
@endsection
