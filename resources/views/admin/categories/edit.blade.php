@extends('admin.layout')

@section('admin_content')
<div>
    <div class="border-b border-gray-100 pb-4 mb-8">
        <a href="{{ route('admin.categories.index') }}" class="text-[10px] font-bold tracking-widest uppercase text-muted hover:text-primary transition-colors mb-2 block">
            &larr; Back to Collections
        </a>
        <h2 class="font-display text-2xl text-primary">Edit Collection: {{ $category->name }}</h2>
        <p class="text-xs text-muted">Update collection name and brand cover imagery.</p>
    </div>

    <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6 max-w-3xl">
        @csrf

        {{-- Current Image Preview if exists --}}
        @if($category->image_url)
        <div class="mb-6 p-4 bg-slate-50 border border-gray-100 inline-flex items-center gap-4">
            <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="w-20 h-20 object-cover rounded-full bg-silk border">
            <div>
                <p class="text-xs font-bold text-primary uppercase tracking-wider">Current Cover</p>
                <p class="text-[10px] text-muted max-w-xs break-all mt-1">{{ $category->image_url }}</p>
            </div>
        </div>
        @endif

        {{-- Name --}}
        <div>
            <label for="name" class="block text-[10px] font-bold tracking-widest uppercase text-muted mb-2">Collection Name *</label>
            <input type="text" id="name" name="name" required value="{{ old('name', $category->name) }}"
                   class="w-full px-4 py-3 bg-slate-50 border border-gray-200 focus:border-secondary focus:bg-white focus:outline-none transition-colors text-sm">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Image File Upload --}}
            <div>
                <label for="image" class="block text-[10px] font-bold tracking-widest uppercase text-muted mb-2">Upload New Cover Image (Replaces current)</label>
                <input type="file" id="image" name="image" accept="image/*"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 focus:border-secondary focus:bg-white focus:outline-none transition-colors text-sm file:mr-4 file:py-1 file:px-3 file:border-0 file:text-xs file:font-semibold file:bg-primary file:text-white hover:file:bg-secondary hover:file:text-primary transition-all">
            </div>

            {{-- Size Chart Image Upload --}}
            <div>
                <label for="size_chart_image" class="block text-[10px] font-bold tracking-widest uppercase text-muted mb-2">Update Default Size Chart (Optional)</label>
                <input type="file" id="size_chart_image" name="size_chart_image" accept="image/*"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 focus:border-secondary focus:bg-white focus:outline-none transition-colors text-sm file:mr-4 file:py-1 file:px-3 file:border-0 file:text-xs file:font-semibold file:bg-primary file:text-white hover:file:bg-secondary hover:file:text-primary transition-all">
                <p class="text-[9px] text-muted mt-1">This will be used for products in this collection if they don't have their own size chart.</p>
                @if($category->size_chart_image)
                    <div class="mt-2 text-[10px] text-primary">
                        Current: <a href="{{ asset($category->size_chart_image) }}" target="_blank" class="underline hover:text-secondary">View Size Chart</a>
                    </div>
                @endif
            </div>
        </div>

        <div class="pt-6 border-t border-gray-100 flex justify-end gap-4">
            <a href="{{ route('admin.categories.index') }}" class="btn-secondary !py-3.5 !px-8 text-[10px]">Cancel</a>
            <button type="submit" class="btn-primary !py-3.5 !px-8 text-[10px]">Save Changes</button>
        </div>
    </form>
</div>
@endsection
