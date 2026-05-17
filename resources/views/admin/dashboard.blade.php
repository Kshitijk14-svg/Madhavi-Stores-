@extends('admin.layout')

@section('admin_content')
<div>
    <div class="mb-8">
        <h2 class="font-display text-2xl mb-1 text-primary">Overview & Statistics</h2>
        <p class="text-xs text-muted">A summary of the Atelier's collections, catalog items, and active users.</p>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        @foreach($stats as $stat)
        <div class="border border-gray-100 p-6 bg-slate-50/50 flex flex-col justify-between hover:border-secondary/40 transition-colors">
            <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-muted mb-2">{{ $stat['label'] }}</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-3xl font-display font-medium text-primary">{{ $stat['value'] }}</span>
                <span class="text-[9px] font-bold tracking-[0.1em] uppercase text-secondary bg-secondary/10 px-2 py-0.5">{{ $stat['trend'] }}</span>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Recent Products --}}
    <div>
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="font-display text-xl text-primary">Recently Added Pieces</h3>
                <p class="text-[11px] text-muted">The latest designs introduced to the Madhavi catalog.</p>
            </div>
            <a href="{{ route('admin.products.index') }}" class="text-[10px] font-bold tracking-widest uppercase text-secondary hover:text-primary transition-colors">
                View All Products &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="border-b border-gray-100 text-[10px] font-bold tracking-widest uppercase text-muted">
                        <th class="py-4">Product</th>
                        <th class="py-4">Collection</th>
                        <th class="py-4">Price</th>
                        <th class="py-4">Featured Status</th>
                        <th class="py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-sm">
                    @forelse($recentProducts as $product)
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
                        <td class="py-4">
                            @if($product->is_bestseller)
                                <span class="text-[9px] font-bold tracking-widest uppercase text-emerald-700 bg-emerald-50 px-2.5 py-0.5 border border-emerald-200">Bestseller</span>
                            @else
                                <span class="text-[9px] font-bold tracking-widest uppercase text-gray-500 bg-gray-50 px-2.5 py-0.5 border border-gray-200">Standard</span>
                            @endif
                        </td>
                        <td class="py-4 text-right">
                            <a href="{{ route('admin.products.edit', $product->id) }}" class="text-xs text-secondary hover:text-primary font-bold tracking-wider uppercase transition-colors">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-muted text-sm">No products found. Start by adding a new product!</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
