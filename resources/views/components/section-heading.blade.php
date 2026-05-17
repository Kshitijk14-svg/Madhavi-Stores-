@props(['overline', 'title'])

<div class="text-center mb-12 lg:mb-16">
    <span class="block text-secondary text-xs tracking-widest uppercase font-medium mb-3">
        {{ $overline }}
    </span>
    <h2 class="font-display text-4xl lg:text-5xl font-light text-primary mb-4">
        {{ $title }}
    </h2>
    <div class="h-[1px] w-12 bg-secondary mx-auto"></div>
</div>
