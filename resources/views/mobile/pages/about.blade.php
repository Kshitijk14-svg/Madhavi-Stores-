@extends('mobile.layouts.app')
@section('title', 'Our Story — Madhavi Stores')

@section('content')
<div class="pb-24">

  {{-- Hero --}}
  <section class="relative h-64 overflow-hidden">
    <img src="{{ $about['cover_image'] ?? '' }}" alt="Our Story" class="w-full h-full object-cover">
    <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
      <h1 style="font-family:'Cormorant Garamond',serif;font-size:2.5rem;font-weight:300;font-style:italic;color:#fff;">Our Story</h1>
    </div>
  </section>

  {{-- Story --}}
  <section class="px-6 py-10">
    @if(!empty($about['story_eyebrow']))
      <p class="eyebrow" style="color:var(--secondary);margin-bottom:12px;">{!! \App\Models\Setting::format($about['story_eyebrow']) !!}</p>
    @endif
    @if(!empty($about['story_title']))
      <h2 style="font-family:'Cormorant Garamond',serif;font-size:1.875rem;font-weight:300;line-height:1.2;margin-bottom:20px;">{!! \App\Models\Setting::format($about['story_title']) !!}</h2>
    @endif
    <div class="space-y-4 text-sm font-light leading-relaxed text-gray-600">
      @foreach($about['story_paragraphs'] ?? [] as $p)
        @if($p)<p>{!! \App\Models\Setting::format($p) !!}</p>@endif
      @endforeach
    </div>
  </section>

  {{-- Values --}}
  @if(!empty($about['value1_title']) || !empty($about['value2_title']) || !empty($about['value3_title']))
  <section class="bg-gray-50 px-6 py-10">
    <p class="eyebrow" style="color:var(--secondary);margin-bottom:20px;">Our Values</p>
    <div class="space-y-8">
      @foreach([
        ['title' => $about['value1_title'] ?? '', 'desc' => $about['value1_desc'] ?? ''],
        ['title' => $about['value2_title'] ?? '', 'desc' => $about['value2_desc'] ?? ''],
        ['title' => $about['value3_title'] ?? '', 'desc' => $about['value3_desc'] ?? ''],
      ] as $value)
        @if($value['title'])
        <div>
          <h3 style="font-family:'Cormorant Garamond',serif;font-size:1.25rem;font-weight:300;margin-bottom:8px;">{{ $value['title'] }}</h3>
          <p class="text-sm font-light leading-relaxed text-gray-600">{{ $value['desc'] }}</p>
        </div>
        @endif
      @endforeach
    </div>
  </section>
  @endif

  {{-- CTA --}}
  <section class="px-6 py-10 text-center">
    <p class="eyebrow" style="color:var(--secondary);margin-bottom:12px;">Explore the Collection</p>
    <a href="{{ route('shop') }}" class="btn-primary inline-block" style="padding:14px 32px;">Shop Now</a>
  </section>

</div>
@endsection
