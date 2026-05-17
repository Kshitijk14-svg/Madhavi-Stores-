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

        return view('pages.home', compact('products', 'newArrivals', 'bestSellers', 'categories', 'heroSlides', 'lookbookSettings', 'cartCount', 'lookbook'));
    }

    public function lookbook()
    {
        $lookbook = Setting::get('lookbook_settings', []);
        
        $cartCount = 0;
        if (auth()->check()) {
            $cartCount = CartItem::where('user_id', auth()->id())->sum('quantity');
        }

        return view('pages.lookbook', compact('lookbook', 'cartCount'));
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
