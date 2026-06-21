<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index()
    {
        try {
            // Cached + column-limited so a crawler hit doesn't load every product
            // row into memory on each request.
            $content = Cache::remember('sitemap.xml', now()->addHours(6), function () {
                $products = Product::select('slug', 'updated_at')
                    ->latest()
                    ->limit(5000)
                    ->get();
                $categories = Category::select('slug', 'updated_at')
                    ->latest()
                    ->limit(1000)
                    ->get();

                return view('sitemap', compact('products', 'categories'))->render();
            });
        } catch (\Throwable $e) {
            logger()->error('Sitemap generation failed: ' . $e->getMessage());
            // Minimal valid sitemap rather than a 500 to crawlers.
            $content = '<?xml version="1.0" encoding="UTF-8"?>'
                . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
                . '<url><loc>' . url('/') . '</loc></url></urlset>';
        }

        return response($content, 200)->header('Content-Type', 'text/xml');
    }
}
