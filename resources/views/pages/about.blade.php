@extends('layouts.app')

@section('content')

<!-- HERO -->
<section class="relative h-[60vh] min-h-[500px] overflow-hidden">
    <img src="{{ $about['cover_image'] ?? 'https://images.unsplash.com/photo-1469334031218-e382a71b716b?w=1600&q=80' }}" alt="Artisan Craftsmanship" class="w-full h-full object-cover">
    <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
        <h1 class="font-display text-6xl lg:text-8xl text-white font-light italic">Our Story</h1>
    </div>
</section>

<!-- STORY -->
<section class="py-24 bg-white">
    <div class="max-w-4xl mx-auto px-6 text-center">
        <span class="text-secondary text-xs tracking-widest uppercase font-medium mb-8 block">{!! \App\Models\Setting::format($about['story_eyebrow'] ?? 'Rooted in Tradition') !!}</span>
        <h2 class="font-display text-4xl lg:text-5xl font-light text-primary mb-10 leading-tight">{!! \App\Models\Setting::format($about['story_title'] ?? 'Crafting Luxury with a Soul') !!}</h2>
        <div class="space-y-8 text-muted text-lg font-light leading-relaxed">
            @foreach($about['story_paragraphs'] ?? [
                'Founded in the heart of traditional textile hubs, Madhavi Stores was born out of a desire to preserve ancient Indian craftsmanship while catering to the sensibilities of the modern world.',
                'Our philosophy is simple: Quiet Luxury. We don\'t believe in loud logos or fast fashion. Instead, we focus on the subtle details—the hand-spun threads, the natural dyes, and the intricate embroidery that only a human hand can achieve.',
                'Every garment at Madhavi Stores is a labor of love, created by artisans who have inherited their skills through generations. By choosing us, you are not just buying clothing; you are supporting a legacy of sustainable, ethical fashion.'
            ] as $p)
                <p>{!! \App\Models\Setting::format($p) !!}</p>
            @endforeach
        </div>
    </div>
</section>

<!-- VALUES -->
<section class="py-24 bg-background">
    <div class="max-w-7xl mx-auto px-6 lg:px-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
            <div class="text-center space-y-4">
                <h3 class="font-display text-2xl font-light text-primary">{!! \App\Models\Setting::format($about['value1_title'] ?? 'Artisanal Craft') !!}</h3>
                <p class="text-muted text-sm font-light leading-relaxed">{!! \App\Models\Setting::format($about['value1_desc'] ?? 'Every piece is meticulously handcrafted by skilled artisans, ensuring unique character and superior quality.') !!}</p>
            </div>
            <div class="text-center space-y-4">
                <h3 class="font-display text-2xl font-light text-primary">{!! \App\Models\Setting::format($about['value2_title'] ?? 'Ethical Sourcing') !!}</h3>
                <p class="text-muted text-sm font-light leading-relaxed">{!! \App\Models\Setting::format($about['value2_desc'] ?? 'We work directly with weaving clusters and craftsmen, ensuring fair wages and sustainable production methods.') !!}</p>
            </div>
            <div class="text-center space-y-4">
                <h3 class="font-display text-2xl font-light text-primary">{!! \App\Models\Setting::format($about['value3_title'] ?? 'Timeless Design') !!}</h3>
                <p class="text-muted text-sm font-light leading-relaxed">{!! \App\Models\Setting::format($about['value3_desc'] ?? 'Our designs transcend seasonal trends, focusing on silhouettes and patterns that remain elegant for years.') !!}</p>
            </div>
        </div>
    </div>
</section>

@endsection
