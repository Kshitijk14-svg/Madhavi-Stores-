@extends('layouts.app')

@section('title', 'Collections | Madhavi Stores')

@section('content')
<section class="py-16">
    <div class="wrap">
        <div class="flex flex-col md:flex-row justify-between items-end mb-12 border-b pb-4">
            <div>
                <h1 class="font-display text-4xl lg:text-5xl text-primary italic mb-2">Our Collections</h1>
                <p class="text-muted">Explore our handpicked curation of luxury ethnic wear.</p>
            </div>
            
            <div class="mt-6 md:mt-0">
                <form action="{{ route('collections.index') }}" method="GET" class="flex items-center gap-4" id="sort-form">
                    <label for="sort" class="text-sm font-semibold tracking-wider text-primary uppercase">Sort By:</label>
                    <select name="sort" id="sort" onchange="document.getElementById('sort-form').submit()" 
                            class="border-b border-gray-300 py-1 bg-transparent text-sm focus:outline-none focus:border-secondary">
                        <option value="az" {{ request('sort') == 'az' ? 'selected' : '' }}>Name: A-Z</option>
                        <option value="za" {{ request('sort') == 'za' ? 'selected' : '' }}>Name: Z-A</option>
                    </select>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($collections as $collection)
                <a href="{{ route('shop', ['category' => $collection->slug]) }}" class="group block relative overflow-hidden bg-gray-50 aspect-[4/5]">
                    @if($collection->image_url)
                        <img src="{{ asset('storage/' . $collection->image_url) }}" alt="{{ $collection->name }}" 
                             loading="lazy"
                             class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-gray-100 text-gray-400 font-display text-2xl italic">
                            {{ $collection->name }}
                        </div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex items-end p-8">
                        <div>
                            <h2 class="text-white font-display text-3xl italic mb-1">{{ $collection->name }}</h2>
                            <p class="text-white/80 text-sm tracking-wider uppercase flex items-center gap-2">
                                Explore <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3"/></svg>
                            </p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-10">
            {{ $collections->links() }}
        </div>

        @if($collections->isEmpty())
            <div class="text-center py-20">
                <p class="text-muted text-lg">No collections found.</p>
            </div>
        @endif
    </div>
</section>
@endsection
