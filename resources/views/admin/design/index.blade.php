@extends('admin.layout')

@section('admin_content')
{{-- Flash Messages --}}
@if(session('success'))
    <div class="mb-6 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
        ✓ {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="mb-6 px-4 py-3 bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold">
        ✕ {{ session('error') }}
    </div>
@endif
<div>
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-primary">Homepage & Brand Design Manager</h2>
        <p class="text-xs text-muted mt-1">Aesthetic controls to dynamically update hero carousels, lookbook chapters, and about pages.</p>
    </div>

    {{-- Tabs sub-nav --}}
    <div class="flex gap-4 border-b border-gray-150 pb-px mb-8 overflow-x-auto whitespace-nowrap">
        <button type="button" 
                onclick="switchTab(event, 'hero-tab')" 
                class="tab-button border-b-2 border-primary text-primary px-4 py-3 font-semibold text-xs tracking-wider uppercase transition-colors outline-none">
            ✦ Hero Swiper Slides
        </button>
        <button type="button" 
                onclick="switchTab(event, 'lookbook-tab')" 
                class="tab-button border-b-2 border-transparent text-muted px-4 py-3 font-semibold text-xs tracking-wider uppercase transition-colors outline-none">
            ✦ Lookbook Chapters
        </button>
        <button type="button" 
                onclick="switchTab(event, 'about-tab')" 
                class="tab-button border-b-2 border-transparent text-muted px-4 py-3 font-semibold text-xs tracking-wider uppercase transition-colors outline-none">
            ✦ About Story
        </button>
        <button type="button" 
                onclick="switchTab(event, 'signin-tab')" 
                class="tab-button border-b-2 border-transparent text-muted px-4 py-3 font-semibold text-xs tracking-wider uppercase transition-colors outline-none">
            ✦ Sign-In Image
        </button>
    </div>

    {{-- ── TAB 1: HERO CAROUSEL ──────────────────────────────────── --}}
    <div id="hero-tab" class="tab-content space-y-8">
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <div>
                <h3 class="text-sm font-bold text-primary uppercase">Hero swiper slides</h3>
                <p class="text-[10px] text-muted">Slides will appear sequentially on the homepage showcase swiper.</p>
            </div>
            <button type="button" onclick="addNewSlide()" class="btn-secondary !py-2 !px-4 text-[10px]">Add Slide Card</button>
        </div>

        <form action="{{ route('admin.design.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="type" value="hero">

            <div id="slides-container" class="space-y-8">
                @forelse($heroSlides as $index => $slide)
                    <div class="slide-card border border-gray-200/80 p-6 bg-silk/10 space-y-6 relative" data-index="{{ $index }}">
                        {{-- Delete button --}}
                        <button type="button" 
                                onclick="this.closest('.slide-card').remove(); reIndexSlides();" 
                                class="absolute top-6 right-6 text-muted hover:text-red-600 transition-colors text-xs font-semibold">
                            Delete Slide
                        </button>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            {{-- Image column --}}
                            <div class="space-y-3">
                                <label class="text-[9px] font-bold text-muted uppercase tracking-wider block">Slide Showcase Image</label>
                                @if($slide['image_url'])
                                    <img src="{{ $slide['image_url'] }}" 
                                         alt="Slide {{ $index }}" 
                                         class="w-full h-32 object-cover border border-gray-200/50 mb-2">
                                @endif
                                <input type="text" 
                                       name="slides[{{ $index }}][image_url]" 
                                       value="{{ $slide['image_url'] ?? '' }}" 
                                       placeholder="Fallback Image URL..."
                                       class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                                <span class="text-[9px] text-muted block text-center">or upload local file:</span>
                                <input type="file" 
                                       name="slides[{{ $index }}][image_file]" 
                                       class="w-full text-[10px] text-primary cursor-pointer">
                            </div>

                            {{-- Text Column --}}
                            <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Slide Title</label>
                                    <input type="text" 
                                           name="slides[{{ $index }}][title]" 
                                           value="{{ $slide['title'] ?? '' }}" 
                                           class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                                </div>

                                <div>
                                    <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Eyebrow (Overline)</label>
                                    <input type="text" 
                                           name="slides[{{ $index }}][eyebrow]" 
                                           value="{{ $slide['eyebrow'] ?? '' }}" 
                                           class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                                </div>

                                <div>
                                    <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Subtitle</label>
                                    <input type="text" 
                                           name="slides[{{ $index }}][subtitle]" 
                                           value="{{ $slide['subtitle'] ?? '' }}" 
                                           class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                                </div>

                                <div>
                                    <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Primary Button Text</label>
                                    <input type="text" 
                                           name="slides[{{ $index }}][button_text]" 
                                           value="{{ $slide['button_text'] ?? '' }}" 
                                           class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                                </div>

                                <div>
                                    <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Primary Button URL</label>
                                    <input type="text" 
                                           name="slides[{{ $index }}][button_url]" 
                                           value="{{ $slide['button_url'] ?? '' }}" 
                                           class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                                </div>

                                <div>
                                    <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Second Button Text (Optional)</label>
                                    <input type="text" 
                                           name="slides[{{ $index }}][second_button_text]" 
                                           value="{{ $slide['second_button_text'] ?? '' }}" 
                                           class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                                </div>

                                <div>
                                    <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Second Button URL (Optional)</label>
                                    <input type="text" 
                                           name="slides[{{ $index }}][second_button_url]" 
                                           value="{{ $slide['second_button_url'] ?? '' }}" 
                                           class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10 bg-silk/10 text-xs text-muted border border-dashed border-gray-200">
                        No hero slides configured. Click Add Slide Card to get started.
                    </div>
                @endforelse
            </div>

            <div class="pt-6 border-t border-gray-100 mt-8 flex justify-end">
                <button type="submit" class="btn-primary !py-3 !px-8 uppercase tracking-widest text-[10px] font-semibold">
                    Save Swiper Carousel Settings
                </button>
            </div>
        </form>
    </div>

    {{-- ── TAB 2: LOOKBOOK CUSTOMIZER ────────────────────────────── --}}
    <div id="lookbook-tab" class="tab-content space-y-8 hidden">
        <form action="{{ route('admin.design.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="type" value="lookbook">

            <div class="space-y-8">
                {{-- Cover Design --}}
                <div class="border border-gray-150 p-6 bg-white space-y-6">
                    <div>
                        <h4 class="text-xs font-bold text-primary uppercase">1. Lookbook Landing Cover</h4>
                        <p class="text-[10px] text-muted">The main banner at the top of the Lookbook editorial screen.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-3">
                            <label class="text-[9px] font-bold text-muted uppercase tracking-wider block">Cover Photo</label>
                            @if(!empty($lookbook['cover_image']))
                                <img src="{{ $lookbook['cover_image'] }}" class="w-full h-32 object-cover border border-gray-200">
                            @endif
                            <input type="text" name="cover_image" value="{{ $lookbook['cover_image'] ?? '' }}" placeholder="Image URL..." class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                            <input type="file" name="cover_file" class="w-full text-[10px] text-primary cursor-pointer">
                        </div>

                        <div class="md:col-span-2 space-y-4">
                            <div>
                                <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Cover Eyebrow</label>
                                <input type="text" name="cover_eyebrow" value="{{ $lookbook['cover_eyebrow'] ?? '' }}" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                            </div>
                            <div>
                                <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Cover Main Title</label>
                                <input type="text" name="cover_title" value="{{ $lookbook['cover_title'] ?? '' }}" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Chapter 1 Design --}}
                <div class="border border-gray-150 p-6 bg-white space-y-6">
                    <div>
                        <h4 class="text-xs font-bold text-primary uppercase">2. Chapter I — Whispers of Silk</h4>
                        <p class="text-[10px] text-muted">First chapter layout with side main photo and inset overlap piece.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-3">
                            <label class="text-[9px] font-bold text-muted uppercase tracking-wider block">Main Chapter Photo</label>
                            @if(!empty($lookbook['chapter1_image']))
                                <img src="{{ $lookbook['chapter1_image'] }}" class="w-full h-32 object-cover border border-gray-200">
                            @endif
                            <input type="text" name="chapter1_image" value="{{ $lookbook['chapter1_image'] ?? '' }}" placeholder="Image URL..." class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                            <input type="file" name="chapter1_file" class="w-full text-[10px] text-primary cursor-pointer">
                        </div>

                        <div class="space-y-3">
                            <label class="text-[9px] font-bold text-muted uppercase tracking-wider block">Overlapping Inset Photo</label>
                            @if(!empty($lookbook['chapter1_inset_image']))
                                <img src="{{ $lookbook['chapter1_inset_image'] }}" class="w-full h-32 object-cover border border-gray-200">
                            @endif
                            <input type="text" name="chapter1_inset_image" value="{{ $lookbook['chapter1_inset_image'] ?? '' }}" placeholder="Image URL..." class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                            <input type="file" name="chapter1_inset_file" class="w-full text-[10px] text-primary cursor-pointer">
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Chapter Eyebrow</label>
                                <input type="text" name="chapter1_eyebrow" value="{{ $lookbook['chapter1_eyebrow'] ?? '' }}" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                            </div>
                            <div>
                                <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Chapter Title</label>
                                <input type="text" name="chapter1_title" value="{{ $lookbook['chapter1_title'] ?? '' }}" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                            </div>
                            <div>
                                <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Chapter Text Description</label>
                                <textarea name="chapter1_description" rows="3" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">{{ $lookbook['chapter1_description'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Separator middle --}}
                <div class="border border-gray-150 p-6 bg-white space-y-6">
                    <div>
                        <h4 class="text-xs font-bold text-primary uppercase">3. Full Width Interlude Image</h4>
                        <p class="text-[10px] text-muted">A beautiful sweeping full-width aesthetic banner breaks Chapter I and Chapter II.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-3">
                            <label class="text-[9px] font-bold text-muted uppercase tracking-wider block">Middle Banner</label>
                            @if(!empty($lookbook['middle_image']))
                                <img src="{{ $lookbook['middle_image'] }}" class="w-full h-32 object-cover border border-gray-200">
                            @endif
                            <input type="text" name="middle_image" value="{{ $lookbook['middle_image'] ?? '' }}" placeholder="Image URL..." class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                            <input type="file" name="middle_file" class="w-full text-[10px] text-primary cursor-pointer">
                        </div>
                    </div>
                </div>

                {{-- Chapter 2 Design --}}
                <div class="border border-gray-150 p-6 bg-white space-y-6">
                    <div>
                        <h4 class="text-xs font-bold text-primary uppercase">4. Chapter II — Modern Heritage</h4>
                        <p class="text-[10px] text-muted">Second cinematic text chapter layout with large block illustration.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-3">
                            <label class="text-[9px] font-bold text-muted uppercase tracking-wider block">Chapter Illustration Photo</label>
                            @if(!empty($lookbook['chapter2_image']))
                                <img src="{{ $lookbook['chapter2_image'] }}" class="w-full h-32 object-cover border border-gray-200">
                            @endif
                            <input type="text" name="chapter2_image" value="{{ $lookbook['chapter2_image'] ?? '' }}" placeholder="Image URL..." class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                            <input type="file" name="chapter2_file" class="w-full text-[10px] text-primary cursor-pointer">
                        </div>

                        <div class="md:col-span-2 space-y-4">
                            <div>
                                <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Chapter Eyebrow</label>
                                <input type="text" name="chapter2_eyebrow" value="{{ $lookbook['chapter2_eyebrow'] ?? '' }}" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                            </div>
                            <div>
                                <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Chapter Title</label>
                                <input type="text" name="chapter2_title" value="{{ $lookbook['chapter2_title'] ?? '' }}" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                            </div>
                            <div>
                                <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Chapter Text Description</label>
                                <textarea name="chapter2_description" rows="3" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">{{ $lookbook['chapter2_description'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Behind the Scenes Grid (6 Images) --}}
                <div class="border border-gray-150 p-6 bg-white space-y-6">
                    <div>
                        <h4 class="text-xs font-bold text-primary uppercase">5. Behind The Scenes Grid (6 Frames)</h4>
                        <p class="text-[10px] text-muted">A dynamic showcase grid exhibiting active work, embroidery, and atelier snaps.</p>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                        @for($i = 0; $i < 6; $i++)
                            <div class="border border-gray-100 p-4 bg-silk/10 space-y-3">
                                <label class="text-[9px] font-bold text-muted block uppercase">Frame {{ $i + 1 }}</label>
                                @if(!empty($lookbook['bts_images'][$i]))
                                    <img src="{{ $lookbook['bts_images'][$i] }}" class="w-full h-24 object-cover border border-gray-200/50 mb-1">
                                @endif
                                <input type="text" name="bts_images[{{ $i }}]" value="{{ $lookbook['bts_images'][$i] ?? '' }}" placeholder="Image URL..." class="w-full text-[10px] text-primary bg-white border border-gray-200 px-2 py-1.5 outline-none">
                                <input type="file" name="bts_files[{{ $i }}]" class="w-full text-[9px] text-primary cursor-pointer">
                            </div>
                        @endfor
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-100 mt-8 flex justify-end">
                <button type="submit" class="btn-primary !py-3 !px-8 uppercase tracking-widest text-[10px] font-semibold">
                    Save Lookbook Editorial
                </button>
            </div>
        </form>
    </div>

    {{-- ── TAB 3: ABOUT STORY ───────────────────────────────────── --}}
    <div id="about-tab" class="tab-content space-y-8 hidden">
        <form action="{{ route('admin.design.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="type" value="about">

            <div class="space-y-8">
                <div class="border border-gray-150 p-6 bg-white space-y-6">
                    <div>
                        <h4 class="text-xs font-bold text-primary uppercase">About Story Branding Panel</h4>
                        <p class="text-[10px] text-muted">Configure the dynamic story chapters, brand principles, and sidebar cover artwork.</p>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        {{-- Cover Image --}}
                        <div class="space-y-3">
                            <label class="text-[9px] font-bold text-muted uppercase tracking-wider block">Sidebar Story Photo</label>
                            @if(!empty($about['cover_image']))
                                <img src="{{ $about['cover_image'] }}" class="w-full h-56 object-cover border border-gray-200">
                            @endif
                            <input type="text" name="cover_image" value="{{ $about['cover_image'] ?? '' }}" placeholder="Image URL..." class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                            <input type="file" name="about_cover_file" class="w-full text-[10px] text-primary cursor-pointer">
                        </div>

                        {{-- Text forms --}}
                        <div class="lg:col-span-2 space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Story Eyebrow</label>
                                    <input type="text" name="story_eyebrow" value="{{ $about['story_eyebrow'] ?? '' }}" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                                </div>
                                <div>
                                    <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Story Title</label>
                                    <input type="text" name="story_title" value="{{ $about['story_title'] ?? '' }}" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                                </div>
                            </div>

                            <div>
                                <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Story Paragraph 1</label>
                                <textarea name="story_paragraph_1" rows="3" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">{{ $about['story_paragraphs'][0] ?? '' }}</textarea>
                            </div>

                            <div>
                                <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Story Paragraph 2</label>
                                <textarea name="story_paragraph_2" rows="3" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">{{ $about['story_paragraphs'][1] ?? '' }}</textarea>
                            </div>

                            <div>
                                <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Story Paragraph 3</label>
                                <textarea name="story_paragraph_3" rows="3" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">{{ $about['story_paragraphs'][2] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Values Panel --}}
                <div class="border border-gray-150 p-6 bg-white space-y-6">
                    <div>
                        <h4 class="text-xs font-bold text-primary uppercase">Atelier Foundational Values (3 Columns)</h4>
                        <p class="text-[10px] text-muted">Values are shown in three distinct grids representing your artistic philosophies.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {{-- Value 1 --}}
                        <div class="border border-gray-100 p-4 space-y-4">
                            <span class="text-[10px] font-bold block uppercase tracking-widest text-secondary">Philosophy I</span>
                            <div>
                                <label class="text-[9px] font-bold text-muted uppercase block mb-1">Value Title</label>
                                <input type="text" name="value1_title" value="{{ $about['value1_title'] ?? '' }}" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                            </div>
                            <div>
                                <label class="text-[9px] font-bold text-muted uppercase block mb-1">Value Description</label>
                                <textarea name="value1_desc" rows="3" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">{{ $about['value1_desc'] ?? '' }}</textarea>
                            </div>
                        </div>

                        {{-- Value 2 --}}
                        <div class="border border-gray-100 p-4 space-y-4">
                            <span class="text-[10px] font-bold block uppercase tracking-widest text-secondary">Philosophy II</span>
                            <div>
                                <label class="text-[9px] font-bold text-muted uppercase block mb-1">Value Title</label>
                                <input type="text" name="value2_title" value="{{ $about['value2_title'] ?? '' }}" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                            </div>
                            <div>
                                <label class="text-[9px] font-bold text-muted uppercase block mb-1">Value Description</label>
                                <textarea name="value2_desc" rows="3" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">{{ $about['value2_desc'] ?? '' }}</textarea>
                            </div>
                        </div>

                        {{-- Value 3 --}}
                        <div class="border border-gray-100 p-4 space-y-4">
                            <span class="text-[10px] font-bold block uppercase tracking-widest text-secondary">Philosophy III</span>
                            <div>
                                <label class="text-[9px] font-bold text-muted uppercase block mb-1">Value Title</label>
                                <input type="text" name="value3_title" value="{{ $about['value3_title'] ?? '' }}" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                            </div>
                            <div>
                                <label class="text-[9px] font-bold text-muted uppercase block mb-1">Value Description</label>
                                <textarea name="value3_desc" rows="3" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">{{ $about['value3_desc'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-100 mt-8 flex justify-end">
                <button type="submit" class="btn-primary !py-3 !px-8 uppercase tracking-widest text-[10px] font-semibold">
                    Save About Story Settings
                </button>
            </div>
        </form>
    </div>

    {{-- ── TAB 4: SIGN-IN PAGE IMAGE ────────────────────────── --}}
    <div id="signin-tab" class="tab-content space-y-8 hidden">
        <form action="{{ route('admin.design.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="type" value="signin">

            <div class="border border-gray-150 p-6 bg-white space-y-6">
                <div>
                    <h4 class="text-xs font-bold text-primary uppercase">Sign-In Page Hero Image</h4>
                    <p class="text-[10px] text-muted">The large background/side image displayed on the Login &amp; Register pages.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                    {{-- Current Preview --}}
                    <div class="space-y-3">
                        <label class="text-[9px] font-bold text-muted uppercase tracking-wider block">Current Sign-In Image</label>
                        @if(!empty($signinImage))
                            <img src="{{ $signinImage }}" 
                                 alt="Sign-In Image" 
                                 class="w-full h-64 object-cover border border-gray-200">
                        @else
                            <div class="w-full h-64 bg-silk/30 border border-dashed border-gray-200 flex items-center justify-center">
                                <span class="text-xs text-muted">No image set</span>
                            </div>
                        @endif
                    </div>

                    {{-- Upload / URL --}}
                    <div class="space-y-4">
                        <div>
                            <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1.5">Image URL</label>
                            <input type="text"
                                   name="signin_image"
                                   value="{{ $signinImage ?? '' }}"
                                   placeholder="https://... or leave blank"
                                   class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2.5 outline-none focus:border-primary transition-colors">
                        </div>

                        <div>
                            <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1.5">Or Upload Local Image</label>
                            <input type="file"
                                   name="signin_file"
                                   accept="image/*"
                                   class="w-full text-[10px] text-primary cursor-pointer">
                            <span class="text-[8px] text-muted block mt-1">Supported: JPG, PNG, WEBP. Max 2MB. Upload overrides URL above.</span>
                        </div>

                        <div class="pt-2">
                            <p class="text-[9px] text-muted">This image is displayed on the <strong class="text-primary">/login</strong> and <strong class="text-primary">/register</strong> pages as the side/background visual.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-100 mt-8 flex justify-end">
                <button type="submit" class="btn-primary !py-3 !px-8 uppercase tracking-widest text-[10px] font-semibold">
                    Save Sign-In Image
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function switchTab(event, tabId) {
        // Hide all tab content boxes
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        
        // Deactivate all tab selectors
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('border-primary', 'text-primary');
            btn.classList.add('border-transparent', 'text-muted');
        });

        // Show active tab
        document.getElementById(tabId).classList.remove('hidden');

        // Activate clicked button
        event.currentTarget.classList.add('border-primary', 'text-primary');
        event.currentTarget.classList.remove('border-transparent', 'text-muted');
    }

    let slideIndexCounter = {{ count($heroSlides) }};

    function addNewSlide() {
        const container = document.getElementById('slides-container');
        const card = document.createElement('div');
        card.className = 'slide-card border border-gray-250 p-6 bg-silk/10 space-y-6 relative';
        card.setAttribute('data-index', slideIndexCounter);

        card.innerHTML = `
            <button type="button" 
                    onclick="this.closest('.slide-card').remove(); reIndexSlides();" 
                    class="absolute top-6 right-6 text-muted hover:text-red-600 transition-colors text-xs font-semibold">
                Delete Slide
            </button>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="space-y-3">
                    <label class="text-[9px] font-bold text-muted uppercase tracking-wider block">Slide Showcase Image</label>
                    <input type="text" 
                           name="slides[${slideIndexCounter}][image_url]" 
                           placeholder="Fallback Image URL..."
                           class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                    <span class="text-[9px] text-muted block text-center">or upload local file:</span>
                    <input type="file" 
                           name="slides[${slideIndexCounter}][image_file]" 
                           class="w-full text-[10px] text-primary cursor-pointer">
                </div>

                <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Slide Title</label>
                        <input type="text" 
                               name="slides[${slideIndexCounter}][title]" 
                               class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                    </div>

                    <div>
                        <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Eyebrow (Overline)</label>
                        <input type="text" 
                               name="slides[${slideIndexCounter}][eyebrow]" 
                               class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                    </div>

                    <div>
                        <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Subtitle</label>
                        <input type="text" 
                               name="slides[${slideIndexCounter}][subtitle]" 
                               class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                    </div>

                    <div>
                        <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Primary Button Text</label>
                        <input type="text" 
                               name="slides[${slideIndexCounter}][button_text]" 
                               class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                    </div>

                    <div>
                        <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Primary Button URL</label>
                        <input type="text" 
                               name="slides[${slideIndexCounter}][button_url]" 
                               class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                    </div>
                </div>
            </div>
        `;

        container.appendChild(card);
        slideIndexCounter++;
    }

    function reIndexSlides() {
        const cards = document.querySelectorAll('#slides-container .slide-card');
        cards.forEach((card, newIndex) => {
            card.setAttribute('data-index', newIndex);
            
            // Re-name fields
            card.querySelectorAll('[name^="slides["]').forEach(input => {
                const name = input.getAttribute('name');
                const rest = name.substring(name.indexOf(']') + 1); // e.g. [title]
                input.setAttribute('name', `slides[${newIndex}]${rest}`);
            });
        });
        slideIndexCounter = cards.length;
    }
</script>
@endsection
