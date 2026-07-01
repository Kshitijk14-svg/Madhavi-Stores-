@extends('layouts.app')

@section('title', 'About Us — Madhavi Stores')
@section('meta_description', 'Welcome to Madhavi Stores – The Ethnic Brand. Discover premium women\'s ethnic and contemporary wear in Nanded.')

@section('content')

<!-- HEADER -->
<section class="py-20 lg:py-28 bg-silk text-center">
    <div class="max-w-3xl mx-auto px-6">
        <span class="text-secondary text-xs tracking-widest uppercase font-bold mb-4 block">Our Story</span>
        <h1 class="font-display text-4xl lg:text-6xl font-light text-primary leading-tight">Welcome to Madhavi Stores</h1>
        <p class="text-secondary text-sm tracking-widest uppercase mt-3 font-semibold">— The Ethnic Brand —</p>
    </div>
</section>

<!-- MAIN STORY -->
<section class="py-20 bg-background">
    <div class="max-w-3xl mx-auto px-6 leading-relaxed">
        <!-- Introduction -->
        <div class="text-center mb-16">
            <p class="text-primary text-lg lg:text-xl font-light leading-relaxed">
                At <strong class="font-semibold text-secondary">Madhavi Stores</strong>, we believe that clothing is a powerful celebration of culture, modern style, and timeless elegance. Located in the heart of Nanded at GG Road (Opposite Laxmicycle), we are dedicated to bringing you a curated collection of premium women's ethnic and contemporary wear.
            </p>
        </div>

        <hr class="border-t border-muted/20 my-12">

        <!-- Collection -->
        <div class="space-y-8 mb-16">
            <div class="text-center">
                <span class="text-secondary text-xs tracking-widest uppercase font-bold mb-2 block">Curated Design</span>
                <h2 class="font-display text-3xl lg:text-4xl font-light text-primary">Our Collection</h2>
            </div>
            <p class="text-muted text-[15px] font-light text-center max-w-2xl mx-auto mb-8">
                We focus exclusively on high-quality, beautifully crafted outfits designed to make you stand out on every occasion. Our specialized inventory includes:
            </p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 pt-4">
                <div class="bg-white p-8 rounded-none border border-border/60 hover:border-secondary/60 transition-colors shadow-sm text-center flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 bg-silk rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-secondary font-display text-xl font-medium">K</span>
                        </div>
                        <h3 class="font-display text-xl text-primary mb-3">Kurti Sets</h3>
                        <p class="text-muted text-xs font-light leading-relaxed">Elegant, perfectly coordinated traditional sets that blend heritage with everyday comfort.</p>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-none border border-border/60 hover:border-secondary/60 transition-colors shadow-sm text-center flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 bg-silk rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-secondary font-display text-xl font-medium">C</span>
                        </div>
                        <h3 class="font-display text-xl text-primary mb-3">Coordsets</h3>
                        <p class="text-muted text-xs font-light leading-relaxed">Trendy, contemporary matching sets designed for the modern woman who loves effortless style.</p>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-none border border-border/60 hover:border-secondary/60 transition-colors shadow-sm text-center flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 bg-silk rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-secondary font-display text-xl font-medium">B</span>
                        </div>
                        <h3 class="font-display text-xl text-primary mb-3">Bandhani Suits</h3>
                        <p class="text-muted text-xs font-light leading-relaxed">Timeless, vibrant traditional suits featuring classic Bandhani print artistry.</p>
                    </div>
                </div>
            </div>
        </div>

        <hr class="border-t border-muted/20 my-12">

        <!-- Philosophy -->
        <div class="space-y-6 text-center">
            <span class="text-secondary text-xs tracking-widest uppercase font-bold mb-2 block">Quality Assurance</span>
            <h2 class="font-display text-3xl lg:text-4xl font-light text-primary">Our Philosophy</h2>
            <p class="text-muted text-[15px] font-light leading-relaxed max-w-2xl mx-auto">
                As an authentic apparel brand, our goal is simple: to offer premium, meticulously selected outfits that deliver the rich heritage of Indian textiles with a modern touch. Every single piece at Madhavi Stores undergoes a strict quality check before it reaches you, ensuring your shopping experience is nothing short of exceptional.
            </p>
        </div>
    </div>
</section>

@endsection
