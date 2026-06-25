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
        <h2 class="text-xl font-bold uppercase tracking-widest text-primary">Branding & Design Manager</h2>
        <p class="text-xs text-muted mt-1">Manage home page section layouts, hero slides, lookbook campaigns, about story, and static system images.</p>
    </div>

    {{-- Tabs sub-nav --}}
    <div class="flex gap-4 border-b border-gray-250 pb-px mb-8 overflow-x-auto whitespace-nowrap">
        <button type="button" 
                onclick="switchTab(event, 'layout-tab')" 
                class="tab-button border-b-2 border-primary text-primary px-4 py-3 font-semibold text-xs tracking-wider uppercase transition-colors outline-none">
            ✦ Homepage Layout
        </button>
        <button type="button" 
                onclick="switchTab(event, 'hero-tab')" 
                class="tab-button border-b-2 border-transparent text-muted px-4 py-3 font-semibold text-xs tracking-wider uppercase transition-colors outline-none">
            ✦ Hero Swiper Slides
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

    {{-- ── TAB 0: HOMEPAGE LAYOUT & BANNERS ──────────────────────── --}}
    <div id="layout-tab" class="tab-content space-y-8">
        <div class="border-b border-gray-100 pb-3">
            <h3 class="text-sm font-bold text-primary uppercase">Homepage Section Arranger</h3>
            <p class="text-[10px] text-muted">Drag and drop the cards below to change the order of sections on the homepage. Use the toggle checkbox to show/hide sections.</p>
        </div>

        <form action="{{ route('admin.design.update') }}" method="POST" enctype="multipart/form-data" id="layout-form" onsubmit="updateLayoutJson()">
            @csrf
            <input type="hidden" name="type" value="layout">
            <input type="hidden" name="sections_json" id="sections-json-input">

            {{-- Section Sorter Stack --}}
            <div id="sections-list" class="space-y-2 mb-10 max-w-xl">
                @foreach($homepageSections as $section)
                    <div class="section-row flex items-center justify-between p-4 bg-slate-50 border border-gray-200 cursor-grab hover:bg-slate-100/70 transition-colors" 
                         draggable="true" 
                         data-id="{{ $section['id'] }}">
                         <div class="flex items-center gap-3">
                            <span class="text-gray-400 font-mono text-sm pointer-events-none">☰</span>
                            <span class="text-xs font-bold text-primary uppercase tracking-wider pointer-events-none">{{ $section['name'] }}</span>
                         </div>
                         <div class="flex items-center gap-2">
                            <input type="checkbox" 
                                   class="section-toggle cursor-pointer" 
                                   {{ !isset($section['visible']) || $section['visible'] ? 'checked' : '' }} 
                                   onchange="updateLayoutJson()">
                            <span class="text-[9px] text-muted font-bold uppercase">Show</span>
                         </div>
                    </div>
                @endforeach
            </div>

            {{-- Dual Banners Editor --}}
            <div class="border-b border-gray-100 pb-3 mb-6">
                <h3 class="text-sm font-bold text-primary uppercase">Editorial Dual Banners</h3>
                <p class="text-[10px] text-muted">Customize the images, headings, and links for the homepage dual banners.</p>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
                @foreach(['banner1' => 'Banner 1 (Left Showcase)', 'banner2' => 'Banner 2 (Right Top)', 'banner3' => 'Banner 3 (Right Bottom)'] as $key => $label)
                    <div class="border border-gray-200 p-5 bg-white space-y-4">
                        <span class="text-[10px] font-bold text-secondary uppercase tracking-widest block">{{ $label }}</span>
                        
                        <div class="space-y-3">
                            <label class="text-[9px] font-bold text-muted uppercase tracking-wider block">Banner Image</label>
                            @if(!empty($dualBanners[$key]['image_url']))
                                <img src="{{ $dualBanners[$key]['image_url'] }}" class="w-full h-32 object-cover border border-gray-150">
                            @endif
                            <input type="text" name="{{ $key }}_image_url" value="{{ $dualBanners[$key]['image_url'] ?? '' }}" placeholder="Image URL..." class="w-full text-xs text-primary bg-slate-50 border border-gray-200 px-3 py-2 outline-none">
                            <input type="file" name="{{ $key }}_file" class="w-full text-[9px] text-primary cursor-pointer">
                        </div>

                        <div class="space-y-3">
                            <div>
                                <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Eyebrow</label>
                                <input type="text" name="{{ $key }}_eyebrow" value="{{ $dualBanners[$key]['eyebrow'] ?? '' }}" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                            </div>
                            <div>
                                <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Title</label>
                                <textarea name="{{ $key }}_title" rows="2" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">{{ $dualBanners[$key]['title'] ?? '' }}</textarea>
                            </div>
                            <div>
                                <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Target Link URL</label>
                                <input type="text" name="{{ $key }}_link" value="{{ $dualBanners[$key]['link'] ?? '' }}" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Promo Banner Editor --}}
            <div class="border-b border-gray-100 pb-3 mb-6">
                <h3 class="text-sm font-bold text-primary uppercase">Promo Season Banner</h3>
                <p class="text-[10px] text-muted">Customize the full-width promotional banner shown on the homepage.</p>
            </div>

            <div class="border border-gray-200 p-6 bg-white space-y-6 mb-10">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-3">
                        <label class="text-[9px] font-bold text-muted uppercase tracking-wider block">Promo Image</label>
                        @if(!empty($promoBanner['image_url']))
                            <img src="{{ $promoBanner['image_url'] }}" class="w-full h-32 object-cover border border-gray-150">
                        @endif
                        <input type="text" name="promo_image_url" value="{{ $promoBanner['image_url'] ?? '' }}" placeholder="Image URL..." class="w-full text-xs text-primary bg-slate-50 border border-gray-200 px-3 py-2 outline-none">
                        <input type="file" name="promo_file" class="w-full text-[9px] text-primary cursor-pointer">
                    </div>

                    <div class="md:col-span-2 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Eyebrow</label>
                                <input type="text" name="promo_eyebrow" value="{{ $promoBanner['eyebrow'] ?? '' }}" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                            </div>
                            <div>
                                <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Title</label>
                                <textarea name="promo_title" rows="2" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">{{ $promoBanner['title'] ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Button Text</label>
                                <input type="text" name="promo_button_text" value="{{ $promoBanner['button_text'] ?? '' }}" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                            </div>
                            <div>
                                <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Button Link URL</label>
                                <input type="text" name="promo_button_link" value="{{ $promoBanner['button_link'] ?? '' }}" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Newsletter Atelier Editor --}}
            <div class="border-b border-gray-100 pb-3 mb-6">
                <h3 class="text-sm font-bold text-primary uppercase">Newsletter Atelier Branding</h3>
                <p class="text-[10px] text-muted">Branding texts for the bottom newsletter box.</p>
            </div>

            <div class="border border-gray-200 p-6 bg-white space-y-4 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Eyebrow</label>
                        <input type="text" name="news_eyebrow" value="{{ $newsletter['eyebrow'] ?? '' }}" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                    </div>
                    <div>
                        <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Title</label>
                        <textarea name="news_title" rows="2" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">{{ $newsletter['title'] ?? '' }}</textarea>
                    </div>
                </div>
                <div>
                    <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Description</label>
                    <textarea name="news_desc" rows="2" class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">{{ $newsletter['description'] ?? '' }}</textarea>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-100 mt-8 flex justify-end">
                <button type="submit" class="btn-primary !py-3 !px-8 uppercase tracking-widest text-[10px] font-semibold">
                    Save Homepage Layout & Banners
                </button>
            </div>
        </form>

        {{-- ── Instagram Feed ──────────────────────────────────────── --}}
        <div class="mt-10 pt-8 border-t border-gray-100">
            <div class="mb-4">
                <h3 class="text-sm font-bold text-primary uppercase">Instagram Feed</h3>
                <p class="text-[10px] text-muted">Posts are pulled live from Instagram (token configured in <code>.env</code>). Set the @handle shown on the homepage below. Toggle the section's visibility in the layout list above.</p>
            </div>
            <form action="{{ route('admin.design.update') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="instagram">
                <div class="max-w-sm">
                    <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-2">Instagram Handle (without @)</label>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-muted">@</span>
                        <input type="text" name="instagram_handle" value="{{ $instagram['handle'] ?? 'madhavistores' }}"
                               class="flex-1 text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none"
                               placeholder="madhavistores">
                    </div>
                </div>
                <div class="pt-6 flex justify-end">
                    <button type="submit" class="btn-primary !py-3 !px-8 uppercase tracking-widest text-[10px] font-semibold">
                        Save Instagram Handle
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── TAB 1: HERO CAROUSEL ──────────────────────────────────── --}}
    <div id="hero-tab" class="tab-content space-y-8 hidden">
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
                                <label class="text-[9px] font-bold text-muted uppercase tracking-wider block">Slide Showcase Image (Desktop · 1920×800)</label>
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
                                <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mt-4">Mobile Image (optional · 1080×1620)</label>
                                @if(!empty($slide['mobile_image_url']))
                                    <img src="{{ $slide['mobile_image_url'] }}"
                                         alt="Mobile Slide {{ $index }}"
                                         class="w-full h-24 object-cover border border-gray-200/50 mb-2">
                                @endif
                                <input type="text"
                                       name="slides[{{ $index }}][mobile_image_url]"
                                       value="{{ $slide['mobile_image_url'] ?? '' }}"
                                       placeholder="Mobile image URL (falls back to desktop)..."
                                       class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                                <span class="text-[9px] text-muted block text-center">or upload local file:</span>
                                <input type="file"
                                       name="slides[{{ $index }}][mobile_image_file]"
                                       class="w-full text-[10px] text-primary cursor-pointer">
                            </div>

                            {{-- Text Column --}}
                            <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Slide Title</label>
                                    <textarea name="slides[{{ $index }}][title]" 
                                              rows="2" 
                                              class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">{{ $slide['title'] ?? '' }}</textarea>
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
                    Save Hero Swiper Slides
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
                        <p class="text-[10px] text-muted">Configure the dynamic story chapters, brand philosophies, and sidebar cover artwork.</p>
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
                    <label class="text-[9px] font-bold text-muted uppercase tracking-wider block">Slide Showcase Image (Desktop · 1920×800)</label>
                    <input type="text"
                           name="slides[\${slideIndexCounter}][image_url]"
                           placeholder="Fallback Image URL..."
                           class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                    <span class="text-[9px] text-muted block text-center">or upload local file:</span>
                    <input type="file"
                           name="slides[\${slideIndexCounter}][image_file]"
                           class="w-full text-[10px] text-primary cursor-pointer">
                    <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mt-4">Mobile Image (optional · 1080×1620)</label>
                    <input type="text"
                           name="slides[\${slideIndexCounter}][mobile_image_url]"
                           placeholder="Mobile image URL (falls back to desktop)..."
                           class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                    <span class="text-[9px] text-muted block text-center">or upload local file:</span>
                    <input type="file"
                           name="slides[\${slideIndexCounter}][mobile_image_file]"
                           class="w-full text-[10px] text-primary cursor-pointer">
                </div>

                <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Slide Title</label>
                        <textarea name="slides[\${slideIndexCounter}][title]" 
                                  rows="2" 
                                  class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none"></textarea>
                    </div>

                    <div>
                        <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Eyebrow (Overline)</label>
                        <input type="text" 
                               name="slides[\${slideIndexCounter}][eyebrow]" 
                               class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                    </div>

                    <div>
                        <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Subtitle</label>
                        <input type="text" 
                               name="slides[\${slideIndexCounter}][subtitle]" 
                               class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                    </div>

                    <div>
                        <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Primary Button Text</label>
                        <input type="text" 
                               name="slides[\${slideIndexCounter}][button_text]" 
                               class="w-full text-xs text-primary bg-white border border-gray-200 px-3 py-2 outline-none">
                    </div>

                    <div>
                        <label class="text-[9px] font-bold text-muted uppercase tracking-wider block mb-1">Primary Button URL</label>
                        <input type="text" 
                               name="slides[\${slideIndexCounter}][button_url]" 
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
                input.setAttribute('name', `slides[\${newIndex}]\${rest}`);
            });
        });
        slideIndexCounter = cards.length;
    }



    // HTML5 Drag and Drop Sorting for Homepage Sections
    let dragSrcEl = null;

    function initSectionDragAndDrop() {
        const rows = document.querySelectorAll('#sections-list .section-row');
        rows.forEach(row => {
            row.addEventListener('dragstart', handleDragStart);
            row.addEventListener('dragover', handleDragOver);
            row.addEventListener('dragenter', handleDragEnter);
            row.addEventListener('dragleave', handleDragLeave);
            row.addEventListener('drop', handleDrop);
            row.addEventListener('dragend', handleDragEnd);
        });
    }

    function handleDragStart(e) {
        dragSrcEl = this;
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', this.innerHTML);
        this.classList.add('bg-slate-200', 'border-dashed');
    }

    function handleDragOver(e) {
        if (e.preventDefault) {
            e.preventDefault();
        }
        e.dataTransfer.dropEffect = 'move';
        return false;
    }

    function handleDragEnter(e) {
        this.classList.add('border-primary');
    }

    function handleDragLeave(e) {
        this.classList.remove('border-primary');
    }

    function handleDrop(e) {
        if (e.stopPropagation) {
            e.stopPropagation();
        }
        
        if (dragSrcEl !== this) {
            const list = document.getElementById('sections-list');
            const children = Array.from(list.children);
            const srcIndex = children.indexOf(dragSrcEl);
            const targetIndex = children.indexOf(this);
            
            if (srcIndex < targetIndex) {
                this.after(dragSrcEl);
            } else {
                this.before(dragSrcEl);
            }
        }
        this.classList.remove('border-primary');
        updateLayoutJson();
        return false;
    }

    function handleDragEnd(e) {
        const rows = document.querySelectorAll('#sections-list .section-row');
        rows.forEach(row => {
            row.classList.remove('bg-slate-200', 'border-dashed', 'border-primary');
        });
        updateLayoutJson();
    }

    function updateLayoutJson() {
        const list = document.getElementById('sections-list');
        const data = [];
        Array.from(list.children).forEach(el => {
            const id = el.getAttribute('data-id');
            const name = el.querySelector('span.uppercase').innerText;
            const checked = el.querySelector('input.section-toggle').checked;
            data.push({ id: id, name: name, visible: checked });
        });
        document.getElementById('sections-json-input').value = JSON.stringify(data);
    }



    // Call layout updates and registers on page load
    document.addEventListener('DOMContentLoaded', () => {
        initSectionDragAndDrop();
        updateLayoutJson();

        // Prevent accidental form submissions when pressing Enter inside inputs
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Enter' && event.target.tagName === 'INPUT') {
                event.preventDefault();
            }
        });
    });
</script>
@endsection
