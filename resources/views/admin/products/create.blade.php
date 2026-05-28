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
                <label for="image" class="block text-[10px] font-bold tracking-widest uppercase text-muted mb-2">Upload Cover Image *</label>
                <input type="file" id="image" name="image" accept="image/*" required
                       class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 focus:border-secondary focus:bg-white focus:outline-none transition-colors text-sm file:mr-4 file:py-1 file:px-3 file:border-0 file:text-xs file:font-semibold file:bg-primary file:text-white hover:file:bg-secondary hover:file:text-primary transition-all">
            </div>

            {{-- Image URL fallback --}}
            <div>
                <label for="image_url" class="block text-[10px] font-bold tracking-widest uppercase text-muted mb-2">Or Direct Image URL</label>
                <input type="url" id="image_url" name="image_url" placeholder="https://example.com/image.jpg" value="{{ old('image_url') }}"
                       class="w-full px-4 py-3 bg-slate-50 border border-gray-200 focus:border-secondary focus:bg-white focus:outline-none transition-colors text-sm">
            </div>
            
            {{-- Size Chart Image Upload --}}
            <div>
                <label for="size_chart_image" class="block text-[10px] font-bold tracking-widest uppercase text-muted mb-2">Upload Size Chart (Optional)</label>
                <input type="file" id="size_chart_image" name="size_chart_image" accept="image/*"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 focus:border-secondary focus:bg-white focus:outline-none transition-colors text-sm file:mr-4 file:py-1 file:px-3 file:border-0 file:text-xs file:font-semibold file:bg-primary file:text-white hover:file:bg-secondary hover:file:text-primary transition-all">
                <p class="text-[9px] text-muted mt-1">If not uploaded, collection's size chart will be used.</p>
            </div>
        </div>

        {{-- Gallery Images 7 Drag & Drop Slots --}}
        <div>
            <label class="block text-[10px] font-bold tracking-widest uppercase text-muted mb-3">Product Gallery (7 Slots — Drag & Drop into individual slots)</label>
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-4">
                @for($i = 0; $i < 7; $i++)
                    <div class="relative aspect-[3/4] border-2 border-dashed border-gray-200 bg-slate-50 flex flex-col items-center justify-center hover:bg-slate-100 transition-colors drop-zone" data-index="{{ $i }}" draggable="true">
                        <input type="file" name="gallery_images[{{ $i }}]" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10 file-input">
                        <div class="text-center pointer-events-none p-2 text-muted-text">
                            <span class="text-xl block mb-1">🖼️</span>
                            <span class="text-[9px] uppercase tracking-wider font-bold block">Slot {{ $i + 1 }}</span>
                        </div>
                        <div class="absolute inset-0 hidden preview-container z-20">
                            <img class="w-full h-full object-cover preview-image">
                            <button type="button" class="absolute top-1 right-1 bg-primary text-white text-[10px] font-bold px-1.5 py-0.5 rounded hover:bg-secondary hover:text-primary transition-colors remove-btn z-30">&times;</button>
                        </div>
                    </div>
                @endfor
            </div>
            <p class="text-[9px] text-muted mt-2">✦ Drag and drop an image directly into any slot above, or click the slot to select a file. You can also drag populated slots onto each other to swap their positions.</p>
        </div>

        <script>
            document.querySelectorAll('.drop-zone').forEach(zone => {
                const input = zone.querySelector('.file-input');
                const previewContainer = zone.querySelector('.preview-container');
                const previewImage = zone.querySelector('.preview-image');
                const removeBtn = zone.querySelector('.remove-btn');
                const index = zone.getAttribute('data-index');

                // Drag and drop from computer
                ['dragenter', 'dragover'].forEach(eventName => {
                    zone.addEventListener(eventName, (e) => {
                        e.preventDefault();
                        zone.classList.add('border-secondary', 'bg-slate-100');
                    }, false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    zone.addEventListener(eventName, (e) => {
                        e.preventDefault();
                        zone.classList.remove('border-secondary', 'bg-slate-100');
                    }, false);
                });

                // Sorting slots (draggable)
                zone.addEventListener('dragstart', (e) => {
                    if (previewContainer.classList.contains('hidden')) {
                        e.preventDefault();
                        return;
                    }
                    e.dataTransfer.setData('text/plain', index);
                });

                zone.addEventListener('drop', (e) => {
                    const sourceIndex = e.dataTransfer.getData('text/plain');
                    if (sourceIndex !== '' && sourceIndex !== null && !isNaN(sourceIndex)) {
                        e.stopPropagation();
                        swapSlots(parseInt(sourceIndex), parseInt(index));
                    } else {
                        const dt = e.dataTransfer;
                        const files = dt.files;
                        if (files.length) {
                            input.files = files;
                            handleFiles(files);
                        }
                    }
                }, false);

                input.addEventListener('change', function() {
                    if (this.files.length) {
                        handleFiles(this.files);
                    }
                });

                function handleFiles(files) {
                    const file = files[0];
                    if (file && file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.readAsDataURL(file);
                        reader.onloadend = function() {
                            previewImage.src = reader.result;
                            previewContainer.classList.remove('hidden');
                        }
                    }
                }

                removeBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    e.preventDefault();
                    input.value = '';
                    previewImage.src = '';
                    previewContainer.classList.add('hidden');
                });
            });

            function swapSlots(indexA, indexB) {
                if (indexA === indexB) return;

                const zoneA = document.querySelector(`.drop-zone[data-index="${indexA}"]`);
                const zoneB = document.querySelector(`.drop-zone[data-index="${indexB}"]`);
                
                const inputA = zoneA.querySelector('.file-input');
                const inputB = zoneB.querySelector('.file-input');
                
                const previewContainerA = zoneA.querySelector('.preview-container');
                const previewContainerB = zoneB.querySelector('.preview-container');
                
                const previewImageA = zoneA.querySelector('.preview-image');
                const previewImageB = zoneB.querySelector('.preview-image');

                // Swap files
                const fileA = inputA.files[0];
                const fileB = inputB.files[0];

                const dtA = new DataTransfer();
                if (fileA) dtA.items.add(fileA);
                inputB.files = dtA.files;

                const dtB = new DataTransfer();
                if (fileB) dtB.items.add(fileB);
                inputA.files = dtB.files;

                // Swap preview visibility and src
                const srcA = previewImageA.src;
                const isHiddenA = previewContainerA.classList.contains('hidden');

                const srcB = previewImageB.src;
                const isHiddenB = previewContainerB.classList.contains('hidden');

                previewImageA.src = srcB;
                if (isHiddenB) {
                    previewContainerA.classList.add('hidden');
                } else {
                    previewContainerA.classList.remove('hidden');
                }

                previewImageB.src = srcA;
                if (isHiddenA) {
                    previewContainerB.classList.add('hidden');
                } else {
                    previewContainerB.classList.remove('hidden');
                }
            }

            // Prevent empty file inputs from being submitted
            document.querySelector('form').addEventListener('submit', function() {
                document.querySelectorAll('.file-input').forEach(input => {
                    if (!input.files || input.files.length === 0) {
                        input.removeAttribute('name');
                    }
                });
            });
        </script>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 border-t border-gray-100 pt-6">
            {{-- Badge --}}
            <div>
                <label for="badge" class="block text-[10px] font-bold tracking-widest uppercase text-muted mb-2">Tag/Badge (Optional)</label>
                <input type="text" id="badge" name="badge" placeholder="e.g. LIMITED" value="{{ old('badge') }}"
                       class="w-full px-4 py-3 bg-slate-50 border border-gray-200 focus:border-secondary focus:bg-white focus:outline-none transition-colors text-sm">
            </div>

            {{-- Discount Type --}}
            <div>
                <label for="discount_type" class="block text-[10px] font-bold tracking-widest uppercase text-muted mb-2">Discount Type</label>
                <select id="discount_type" name="discount_type" class="w-full px-4 py-3 bg-slate-50 border border-gray-200 focus:border-secondary focus:bg-white focus:outline-none transition-colors text-sm">
                    <option value="">No Discount</option>
                    <option value="percent" {{ old('discount_type') == 'percent' ? 'selected' : '' }}>Percentage (%)</option>
                    <option value="fixed" {{ old('discount_type') == 'fixed' ? 'selected' : '' }}>Fixed Amount (₹)</option>
                </select>
            </div>

            {{-- Discount Value --}}
            <div>
                <label for="discount_value" class="block text-[10px] font-bold tracking-widest uppercase text-muted mb-2">Discount Value</label>
                <input type="number" id="discount_value" name="discount_value" step="0.01" value="{{ old('discount_value') }}" placeholder="e.g. 10"
                       class="w-full px-4 py-3 bg-slate-50 border border-gray-200 focus:border-secondary focus:bg-white focus:outline-none transition-colors text-sm">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="flex items-center pt-8">
                <input type="checkbox" id="is_bestseller" name="is_bestseller" value="1" {{ old('is_bestseller') ? 'checked' : '' }}
                       class="w-4 h-4 text-secondary border-gray-300 focus:ring-secondary focus:ring-2 focus:ring-offset-2">
                <label for="is_bestseller" class="ml-2 text-xs font-bold tracking-wider uppercase text-primary">Mark as Bestseller</label>
            </div>

            <div class="flex items-center pt-8">
                <input type="checkbox" id="is_new_arrival" name="is_new_arrival" value="1" {{ old('is_new_arrival') ? 'checked' : '' }}
                       class="w-4 h-4 text-secondary border-gray-300 focus:ring-secondary focus:ring-2 focus:ring-offset-2">
                <label for="is_new_arrival" class="ml-2 text-xs font-bold tracking-wider uppercase text-primary">Mark as New Arrival</label>
            </div>

            <div>
                <label for="new_arrival_expires_at" class="block text-[10px] font-bold tracking-widest uppercase text-muted mb-2">New Arrival Timer (Optional)</label>
                <input type="datetime-local" id="new_arrival_expires_at" name="new_arrival_expires_at" value="{{ old('new_arrival_expires_at') }}"
                       class="w-full px-4 py-3 bg-slate-50 border border-gray-200 focus:border-secondary focus:bg-white focus:outline-none transition-colors text-sm">
            </div>
        </div>

        {{-- Sizing Option Toggle --}}
        <div class="border-t border-gray-100 pt-6">
            <label for="has_sizes" class="block text-[10px] font-bold tracking-widest uppercase text-muted mb-2">Product Stock & Sizes Option</label>
            <select id="has_sizes" name="has_sizes" class="w-full md:w-1/3 px-4 py-3 bg-slate-50 border border-gray-200 focus:border-secondary focus:bg-white focus:outline-none transition-colors text-sm">
                <option value="1" {{ old('has_sizes', '1') == '1' ? 'selected' : '' }}>This product has multiple sizes (XS to XXXL)</option>
                <option value="0" {{ old('has_sizes') == '0' ? 'selected' : '' }}>This product is one-size / has no sizes</option>
            </select>
        </div>

        {{-- Sizes & Stock (Show if has_sizes is 1) --}}
        <div id="sizes-stock-section" class="border-t border-gray-100 pt-6">
            <h3 class="text-sm font-bold uppercase tracking-widest text-primary mb-4">Sizes & Inventory (Stock)</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
                @foreach(['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL'] as $size)
                <div>
                    <label class="block text-[10px] font-bold tracking-widest uppercase text-muted mb-1">{{ $size }}</label>
                    <input type="number" name="sizes[{{ $size }}]" min="0" placeholder="0" class="w-full px-3 py-2 bg-slate-50 border border-gray-200 text-sm">
                </div>
                @endforeach
            </div>
        </div>

        {{-- General Stock (Show if has_sizes is 0) --}}
        <div id="general-stock-section" class="border-t border-gray-100 pt-6 hidden">
            <h3 class="text-sm font-bold uppercase tracking-widest text-primary mb-4">General Inventory (Stock)</h3>
            <div class="w-full md:w-1/3">
                <label class="block text-[10px] font-bold tracking-widest uppercase text-muted mb-1">Total Stock Available</label>
                <input type="number" id="stock" name="stock" min="0" placeholder="e.g. 50" value="{{ old('stock', 0) }}" class="w-full px-4 py-3 bg-slate-50 border border-gray-200 text-sm">
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const hasSizesSelect = document.getElementById('has_sizes');
                const sizesStockSection = document.getElementById('sizes-stock-section');
                const generalStockSection = document.getElementById('general-stock-section');

                function toggleStockSections() {
                    if (hasSizesSelect.value === '1') {
                        sizesStockSection.classList.remove('hidden');
                        generalStockSection.classList.add('hidden');
                    } else {
                        sizesStockSection.classList.add('hidden');
                        generalStockSection.classList.remove('hidden');
                    }
                }

                hasSizesSelect.addEventListener('change', toggleStockSections);
                // Run on load
                toggleStockSections();
            });
        </script>

        {{-- Product Details --}}
        <div class="border-t border-gray-100 pt-6 space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-widest text-primary mb-2">Product Details</h3>
            <div>
                <label for="details" class="block text-[10px] font-bold tracking-widest uppercase text-muted mb-2">Bullet Points (One per line)</label>
                <textarea id="details" name="details" rows="5" placeholder="100% premium handcrafted fabric.&#10;Dry clean only recommended."
                          class="w-full px-4 py-3 bg-slate-50 border border-gray-200 focus:border-secondary focus:bg-white focus:outline-none transition-colors text-sm">{{ old('details') }}</textarea>
            </div>
        </div>

        {{-- SEO Settings --}}
        <div class="border-t border-gray-100 pt-6 space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-widest text-primary mb-2">SEO Optimization</h3>
            <div>
                <label for="seo_title" class="block text-[10px] font-bold tracking-widest uppercase text-muted mb-2">SEO Title</label>
                <input type="text" id="seo_title" name="seo_title" value="{{ old('seo_title') }}" placeholder="Optimized title for Google"
                       class="w-full px-4 py-3 bg-slate-50 border border-gray-200 focus:border-secondary focus:bg-white focus:outline-none transition-colors text-sm">
            </div>
            <div>
                <label for="seo_keywords" class="block text-[10px] font-bold tracking-widest uppercase text-muted mb-2">SEO Keywords (Comma separated)</label>
                <input type="text" id="seo_keywords" name="seo_keywords" value="{{ old('seo_keywords') }}" placeholder="luxury, dress, summer"
                       class="w-full px-4 py-3 bg-slate-50 border border-gray-200 focus:border-secondary focus:bg-white focus:outline-none transition-colors text-sm">
            </div>
            <div>
                <label for="seo_description" class="block text-[10px] font-bold tracking-widest uppercase text-muted mb-2">SEO Description</label>
                <textarea id="seo_description" name="seo_description" rows="2" placeholder="Meta description for search results"
                          class="w-full px-4 py-3 bg-slate-50 border border-gray-200 focus:border-secondary focus:bg-white focus:outline-none transition-colors text-sm">{{ old('seo_description') }}</textarea>
            </div>
        </div>

        <div class="pt-6 border-t border-gray-100 flex justify-end gap-4">
            <a href="{{ route('admin.products.index') }}" class="btn-secondary !py-3.5 !px-8 text-[10px]">Cancel</a>
            <button type="submit" class="btn-primary !py-3.5 !px-8 text-[10px]">Add Product</button>
        </div>
    </form>
</div>
@endsection
