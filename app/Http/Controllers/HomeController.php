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
        // Query new arrivals tagged with is_new_arrival flag
        $newArrivals = Product::with('category')->where('is_new_arrival', true)->latest()->take(8)->get();
        if ($newArrivals->isEmpty()) {
            $newArrivals = Product::with('category')->latest()->take(8)->get();
        }

        // Query bestsellers tagged with is_bestseller flag
        $bestSellers = Product::with('category')->where('is_bestseller', true)->latest()->take(8)->get();
        if ($bestSellers->isEmpty()) {
            $bestSellers = Product::with('category')->latest()->take(8)->get();
        }

        $categories = \Illuminate\Support\Facades\Cache::remember('all_categories_no_count', 86400, function() {
            return Category::all();
        });

        // Load dynamic hero slides
        $heroSlides = Setting::get('hero_slides', []);

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
            ['id' => 'newsletter', 'name' => 'Newsletter Atelier', 'visible' => true],
        ];
        $homepageSections = Setting::get('homepage_sections', $defaultSections);
        // Exclude lookbook and instagram feed sections
        $homepageSections = array_values(array_filter($homepageSections, function($sec) {
            return !in_array($sec['id'], ['lookbook', 'instagram_feed']);
        }));

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
            'newArrivals', 'bestSellers', 'categories', 'heroSlides', 
            'cartCount', 
            'homepageSections', 'dualBanners', 'promoBanner', 'newsletter'
        ));
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
