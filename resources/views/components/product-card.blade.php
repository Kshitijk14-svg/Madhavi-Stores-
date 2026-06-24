@props(['product'])

<div class="product-card group relative flex flex-col">
    <!-- Image Area -->
    <a href="{{ route('product.show', $product['slug']) }}" class="block aspect-[3/4] overflow-hidden relative bg-gray-100 mb-4 focus-visible:ring-2 focus-visible:ring-secondary focus-visible:outline-none">
        <img 
            src="{{ $product['image_url'] }}" 
            alt="{{ $product['name'] }}" 
            loading="lazy"
            class="w-full h-full object-cover object-center transition-transform duration-600 ease-in-out group-hover:scale-[1.04]"
        >
        
        <!-- Badge -->
        @if($product['badge'])
            <span class="absolute top-3 left-3 bg-white text-primary text-[10px] tracking-widest uppercase font-bold px-3 py-1 shadow-sm">
                {{ $product['badge'] }}
            </span>
        @endif

        <!-- Quick Add Button -->
        <button class="absolute bottom-0 left-0 right-0 bg-white/90 backdrop-blur text-primary text-xs tracking-widest uppercase py-4 translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-out hover:bg-secondary hover:text-ink focus-visible:ring-2 focus-visible:ring-secondary focus-visible:outline-none" aria-label="Quick add {{ $product['name'] }} to cart">
            Quick Add
        </button>
    </a>

    <!-- Details Area -->
    <div class="flex flex-col flex-grow text-center">
        <a href="{{ route('product.show', $product['slug']) }}" class="text-sm font-medium tracking-wide text-primary hover:text-secondary transition-colors mb-1 focus-visible:ring-2 focus-visible:ring-secondary focus-visible:outline-none">
            {{ $product['name'] }}
        </a>
        <div class="text-sm mt-1">
            <span class="text-primary font-medium">₹{{ number_format($product['price']) }}</span>
            @if($product['original_price'])
                <span class="text-muted line-through ml-2">₹{{ number_format($product['original_price']) }}</span>
            @endif
        </div>
        
        <!-- Rating -->
        <div class="mt-2 flex items-center justify-center text-[#c5b358] text-xs">
            @for($i = 1; $i <= 5; $i++)
                @if($i <= floor($product['rating']))
                    <span>★</span>
                @else
                    <span class="text-gray-300">★</span>
                @endif
            @endfor
            <span class="text-muted ml-1">({{ $product['review_count'] }})</span>
        </div>
    </div>
</div>
