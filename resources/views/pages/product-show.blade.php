@extends('layouts.app')

@section('content')

<section class="py-12 lg:py-20">
    <div class="max-w-7xl mx-auto px-6 lg:px-12">
        <nav class="flex items-center space-x-2 text-xs tracking-widest uppercase font-medium text-muted mb-10">
            <a href="{{ route('home') }}" class="hover:text-secondary transition-colors">Home</a>
            <span>/</span>
            <a href="{{ route('collection') }}" class="hover:text-secondary transition-colors">Collection</a>
            <span>/</span>
            <span class="text-primary">{{ $product['name'] }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 xl:gap-20">
            
            <!-- Product Gallery -->
            <div class="lg:col-span-7">
                <div class="grid grid-cols-1 gap-4">
                    @foreach($product['gallery'] as $image)
                        <div class="aspect-[3/4] overflow-hidden bg-background">
                            <img src="{{ $image }}" alt="{{ $product['name'] }}" class="w-full h-full object-cover">
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Product Info -->
            <div class="lg:col-span-5">
                <div class="sticky top-32">
                    <div class="mb-8">
                        @if($product['badge'])
                            <span class="inline-block bg-primary text-white text-[10px] tracking-widest uppercase font-bold px-3 py-1 mb-4">
                                {{ $product['badge'] }}
                            </span>
                        @endif
                        <h1 class="font-display text-4xl lg:text-5xl font-light text-primary mb-4">{{ $product['name'] }}</h1>
                        <div class="flex items-center space-x-4 mb-6">
                            <span class="text-2xl font-medium text-primary">₹{{ number_format($product['price']) }}</span>
                            @if($product['original_price'])
                                <span class="text-lg text-muted line-through">₹{{ number_format($product['original_price']) }}</span>
                            @endif
                        </div>
                        <div class="flex items-center text-[#c5b358] text-sm mb-6">
                            @for($i = 1; $i <= 5; $i++)
                                <span>★</span>
                            @endfor
                            <span class="text-muted ml-2">({{ $product['review_count'] }} reviews)</span>
                        </div>
                    </div>

                    <div class="mb-10 text-muted font-light leading-relaxed">
                        <p>{{ $product['description'] }}</p>
                    </div>

                    <!-- Size Selection (Mock) -->
                    <div class="mb-10">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xs tracking-widest uppercase font-bold text-primary">Select Size</h3>
                            <button class="text-[10px] tracking-widest uppercase font-medium text-secondary underline">Size Guide</button>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            @foreach(['XS', 'S', 'M', 'L', 'XL'] as $size)
                                <button class="w-12 h-12 flex items-center justify-center border border-primary/10 text-xs font-medium hover:border-primary transition-all {{ $size == 'M' ? 'bg-primary text-white border-primary' : '' }}">
                                    {{ $size }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col space-y-4 mb-12">
                        <button class="w-full py-5 bg-primary text-white text-xs tracking-widest uppercase font-bold hover:bg-secondary transition-all duration-300">
                            Add to Bag
                        </button>
                        <button class="w-full py-5 border border-primary text-primary text-xs tracking-widest uppercase font-bold hover:bg-primary hover:text-white transition-all duration-300">
                            Add to Wishlist
                        </button>
                    </div>

                    <!-- Product Details Accordion (Simplified) -->
                    <div class="border-t border-primary/10 pt-8">
                        <h3 class="text-xs tracking-widest uppercase font-bold text-primary mb-4">Product Details</h3>
                        <ul class="space-y-3 text-sm text-muted font-light">
                            @foreach($product['details'] as $detail)
                                <li class="flex items-start">
                                    <span class="mr-3 mt-1.5 w-1 h-1 bg-secondary rounded-full flex-shrink-0"></span>
                                    {{ $detail }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- RELATED PRODUCTS -->
<section class="py-20 bg-background">
    <div class="max-w-7xl mx-auto px-6 lg:px-12">
        <h2 class="font-display text-3xl font-light text-primary mb-12">Complete The Look</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            @foreach($relatedProducts as $related)
                <x-product-card :product="$related" />
            @endforeach
        </div>
    </div>
</section>

@endsection
