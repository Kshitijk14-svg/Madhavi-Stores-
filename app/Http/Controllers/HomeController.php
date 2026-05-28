<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Setting;
use App\Models\CartItem;

class HomeController extends Controller
{
    public function index()
    {
        $newArrivals = Product::latest()->take(4)->get();
        $bestSellers = Product::where('is_bestseller', true)->take(4)->get();
        $categories = Category::all();
        $products = Product::all();

        // Load dynamic hero slides
        $heroSlides = Setting::get('hero_slides', []);

        // Load lookbook settings for dynamic sections
        $lookbookSettings = Setting::get('lookbook_settings', []);

        // Dynamic preview list
        $lookbook = [];
        if (!empty($lookbookSettings['bts_images'])) {
            $lookbook = array_slice($lookbookSettings['bts_images'], 0, 4);
        } else {
            $lookbook = [
                'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=800&q=80',
                'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?w=600&q=80',
                'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=600&q=80',
                'https://images.unsplash.com/photo-1485230895905-31d1d1ebc325?w=1000&q=80'
            ];
        }

        // Cart count
        $cartCount = 0;
        if (auth()->check()) {
            $cartCount = CartItem::where('user_id', auth()->id())->sum('quantity');
        }

        // Load homepage layout order
        $defaultSections = [
            ['id' => 'hero', 'name' => 'Hero Carousel', 'visible' => true],
            ['id' => 'categories', 'name' => 'Category Circles', 'visible' => true],
            ['id' => 'dual_banners', 'name' => 'Dual Banners', 'visible' => true],
            ['id' => 'new_arrivals', 'name' => 'New Arrivals Slider', 'visible' => true],
            ['id' => 'promo_banner', 'name' => 'Promo Banner', 'visible' => true],
            ['id' => 'bestsellers', 'name' => 'Bestsellers Grid', 'visible' => true],
            ['id' => 'lookbook', 'name' => 'Lookbook Grid', 'visible' => true],
            ['id' => 'instagram_feed', 'name' => 'Instagram Feed', 'visible' => true],
            ['id' => 'newsletter', 'name' => 'Newsletter Atelier', 'visible' => true],
        ];
        $homepageSections = Setting::get('homepage_sections', $defaultSections);

        // Load Dual Banners settings
        $defaultDualBanners = [
            'banner1' => [
                'image_url' => 'https://images.unsplash.com/photo-1617627143750-d86bc21e42bb?w=900&q=80&auto=format&fit=crop',
                'eyebrow' => 'New Season',
                'title' => 'Saree Glow',
                'link' => '/collection'
            ],
            'banner2' => [
                'image_url' => 'https://images.unsplash.com/photo-1551488831-00ddcb6c6bd3?w=900&q=80&auto=format&fit=crop',
                'eyebrow' => 'Trending',
                'title' => 'Co-ord Sets',
                'link' => '/collection'
            ],
            'banner3' => [
                'image_url' => 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?w=900&q=80&auto=format&fit=crop',
                'eyebrow' => 'Most Loved',
                'title' => 'Kurta Sets',
                'link' => '/collection'
            ],
        ];
        $dualBanners = Setting::get('dual_banners_settings', $defaultDualBanners);

        // Load Promo Banner settings
        $defaultPromoBanner = [
            'image_url' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=1920&q=80&auto=format&fit=crop',
            'eyebrow' => 'Limited Time',
            'title' => "Up to 40% Off\nBestsellers",
            'button_text' => 'Shop the Sale',
            'button_link' => '/collection'
        ];
        $promoBanner = Setting::get('promo_banner_settings', $defaultPromoBanner);

        // Load Newsletter settings
        $defaultNewsletter = [
            'eyebrow' => 'Stay Close',
            'title' => 'Join the <em>Atelier</em>',
            'description' => 'Early access, exclusive drops, editorial stories.'
        ];
        $newsletter = Setting::get('newsletter_settings', $defaultNewsletter);

        return view('pages.home', compact(
            'products', 'newArrivals', 'bestSellers', 'categories', 'heroSlides', 
            'lookbookSettings', 'cartCount', 'lookbook', 
            'homepageSections', 'dualBanners', 'promoBanner', 'newsletter'
        ));
    }

    public function lookbook()
    {
        $lookbook = Setting::get('lookbook_settings', []);

        $defaultLookbookSections = [
            ['id' => 'cover', 'name' => 'Cinematic Cover', 'visible' => true],
            ['id' => 'chapters', 'name' => 'Editorial Chapters Stack', 'visible' => true],
            ['id' => 'interlude', 'name' => 'Full Width Interlude Image', 'visible' => true],
            ['id' => 'bts', 'name' => 'Behind The Scenes Grid', 'visible' => true],
            ['id' => 'cta', 'name' => 'Call To Action (Shop the Look)', 'visible' => true],
        ];
        $lookbookSections = Setting::get('lookbook_sections', $defaultLookbookSections);
        
        $cartCount = 0;
        if (auth()->check()) {
            $cartCount = CartItem::where('user_id', auth()->id())->sum('quantity');
        }

        return view('pages.lookbook', compact('lookbook', 'cartCount', 'lookbookSections'));
    }

    public function about()
    {
        $about = Setting::get('about_settings', []);

        $cartCount = 0;
        if (auth()->check()) {
            $cartCount = CartItem::where('user_id', auth()->id())->sum('quantity');
        }

        return view('pages.about', compact('about', 'cartCount'));
    }
}
