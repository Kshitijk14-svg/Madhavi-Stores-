@extends('admin.layout')

@section('admin_content')
<div>
    <div class="border-b border-gray-100 pb-4 mb-8">
        <a href="{{ route('admin.products.index') }}" class="text-[10px] font-bold tracking-widest uppercase text-muted hover:text-primary transition-colors mb-2 block">
            &larr; Back to Products
        </a>
        <h2 class="font-display text-2xl text-primary">Add New Product</h2>
        <p class="text-xs text-muted">Create a new premium item to add to the storefront catalog.</p>
    </div>

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6 max-w-3xl">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Name --}}
            <div>
                <label for="name" class="block text-[10px] font-bold tracking-widest uppercase text-muted mb-2">Product Name *</label>
                <input type="text" id="name" name="name" required value="{{ old('name') }}"
                       class="w-full px-4 py-3 bg-slate-50 border border-gray-200 focus:border-secondary focus:bg-white focus:outline-none transition-colors text-sm">
            </div>

            {{-- Category --}}
            <div>
                <label for="category_id" class="block text-[10px] font-bold tracking-widest uppercase text-muted mb-2">Collection *</label>
                <select id="category_id" name="category_id" required
                        class="w-full px-4 py-3 bg-slate-50 border border-gray-200 focus:border-secondary focus:bg-white focus:outline-none transition-colors text-sm">
                    <option value="">Select a Collection</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Price --}}
            <div>
                <label for="price" class="block text-[10px] font-bold tracking-widest uppercase text-muted mb-2">Selling Price (₹) *</label>
                <input type="number" id="price" name="price" step="0.01" required value="{{ old('price') }}"
                       class="w-full px-4 py-3 bg-slate-50 border border-gray-200 focus:border-secondary focus:bg-white focus:outline-none transition-colors text-sm">
            </div>

            {{-- Original Price --}}
            <div>
                <label for="original_price" class="block text-[10px] font-bold tracking-widest uppercase text-muted mb-2">Original Price (₹ - Optional)</label>
                <input type="number" id="original_price" name="original_price" step="0.01" value="{{ old('original_price') }}"
                       class="w-full px-4 py-3 bg-slate-50 border border-gray-200 focus:border-secondary focus:bg-white focus:outline-none transition-colors text-sm">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Image File Upload --}}
            <div>
                <label for="image" class="block text-[10px] font-bold tracking-widest uppercase text-muted mb-2">Upload Cover Image</label>
                <input type="file" id="image" name="image" accept="image/*"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 focus:border-secondary focus:bg-white focus:outline-none transition-colors text-sm file:mr-4 file:py-1 file:px-3 file:border-0 file:text-xs file:font-semibold file:bg-primary file:text-white hover:file:bg-secondary hover:file:text-primary transition-all">
            </div>

            {{-- Image URL fallback --}}
            <div>
                <label for="image_url" class="block text-[10px] font-bold tracking-widest uppercase text-muted mb-2">Or Direct Image URL</label>
                <input type="url" id="image_url" name="image_url" placeholder="https://example.com/image.jpg" value="{{ old('image_url') }}"
                       class="w-full px-4 py-3 bg-slate-50 border border-gray-200 focus:border-secondary focus:bg-white focus:outline-none transition-colors text-sm">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Badge --}}
            <div>
                <label for="badge" class="block text-[10px] font-bold tracking-widest uppercase text-muted mb-2">Tag/Badge (Optional)</label>
                <input type="text" id="badge" name="badge" placeholder="e.g. NEW IN, LIMITED" value="{{ old('badge') }}"
                       class="w-full px-4 py-3 bg-slate-50 border border-gray-200 focus:border-secondary focus:bg-white focus:outline-none transition-colors text-sm">
            </div>

            {{-- Checkboxes --}}
            <div class="flex items-center pt-8">
                <input type="checkbox" id="is_bestseller" name="is_bestseller" value="1" {{ old('is_bestseller') ? 'checked' : '' }}
                       class="w-4 h-4 text-secondary border-gray-300 focus:ring-secondary focus:ring-2 focus:ring-offset-2">
                <label for="is_bestseller" class="ml-2 text-xs font-bold tracking-wider uppercase text-primary">Mark as Bestseller / Featured Piece</label>
            </div>
        </div>

        <div class="pt-6 border-t border-gray-100 flex justify-end gap-4">
            <a href="{{ route('admin.products.index') }}" class="btn-secondary !py-3.5 !px-8 text-[10px]">Cancel</a>
            <button type="submit" class="btn-primary !py-3.5 !px-8 text-[10px]">Add Product</button>
        </div>
    </form>
</div>
@endsection
