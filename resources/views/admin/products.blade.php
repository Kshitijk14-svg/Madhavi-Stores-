@extends('layouts.admin')

@section('title', 'Product Management')

@section('content')

<div class="bg-white border border-primary/5 shadow-sm">
    <div class="p-8 border-b border-primary/5 flex flex-col md:flex-row justify-between items-center gap-6">
        <div class="relative w-full md:w-96">
            <input type="text" placeholder="Search products..." class="w-full bg-background border-0 px-10 py-3 text-sm focus:ring-1 focus:ring-secondary outline-none transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-4 top-1/2 -translate-y-1/2 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
        </div>
        <button class="w-full md:w-auto px-8 py-3 bg-primary text-white text-[10px] tracking-widest uppercase font-bold hover:bg-secondary transition-all">
            Add New Product
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-background/50 text-[10px] tracking-widest uppercase font-bold text-muted">
                    <th class="px-8 py-4">Product</th>
                    <th class="px-8 py-4">Category</th>
                    <th class="px-8 py-4">Price</th>
                    <th class="px-8 py-4">Stock</th>
                    <th class="px-8 py-4">Status</th>
                    <th class="px-8 py-4">Actions</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                @foreach($products as $product)
                <tr class="border-b border-primary/5 hover:bg-background/30 transition-colors">
                    <td class="px-8 py-6">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-16 bg-background flex-shrink-0">
                                <img src="https://images.unsplash.com/photo-1583391733958-6c78278104ba?w=100&q=80" alt="Item" class="w-full h-full object-cover">
                            </div>
                            <span class="font-medium text-primary">{{ $product['name'] }}</span>
                        </div>
                    </td>
                    <td class="px-8 py-6 text-muted">{{ $product['category'] }}</td>
                    <td class="px-8 py-6 font-medium">₹{{ number_format($product['price']) }}</td>
                    <td class="px-8 py-6">
                        <span class="{{ $product['stock'] < 5 ? 'text-accent font-bold' : 'text-primary' }}">
                            {{ $product['stock'] }}
                        </span>
                    </td>
                    <td class="px-8 py-6">
                        <span class="px-2 py-1 text-[10px] font-bold uppercase rounded-sm {{ $product['status'] == 'Active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $product['status'] }}
                        </span>
                    </td>
                    <td class="px-8 py-6">
                        <div class="flex items-center space-x-4">
                            <button class="text-muted hover:text-secondary transition-colors" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </button>
                            <button class="text-muted hover:text-accent transition-colors" title="Delete">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="p-8 border-t border-primary/5 flex justify-between items-center">
        <span class="text-xs text-muted font-medium">Showing 3 of 128 products</span>
        <div class="flex space-x-2">
            <button class="w-8 h-8 flex items-center justify-center border border-primary/10 rounded-sm hover:border-primary transition-colors disabled:opacity-30" disabled>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            </button>
            <button class="w-8 h-8 flex items-center justify-center border border-primary/10 rounded-sm hover:border-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>
        </div>
    </div>
</div>

@endsection
