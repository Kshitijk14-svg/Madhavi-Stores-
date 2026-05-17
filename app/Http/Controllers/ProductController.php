<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        // Filter by category
        if ($request->has('category') && $request->category !== '') {
            $categorySlug = $request->category;
            $query->whereHas('category', function($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        // Search query
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        if ($request->has('sort')) {
            if ($request->sort === 'price_low') {
                $query->orderBy('price', 'asc');
            } elseif ($request->sort === 'price_high') {
                $query->orderBy('price', 'desc');
            } elseif ($request->sort === 'newest') {
                $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->latest();
        }

        $products = $query->get();
        $categories = Category::all();

        return view('pages.collection', compact('products', 'categories'));
    }

    public function show($slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();

        // Get related products (same category, excluding current)
        $relatedProducts = Product::where('category_id', $product->category_id)
                                  ->where('id', '!=', $product->id)
                                  ->take(4)
                                  ->get();

        // If not enough in same category, pad with latest
        if ($relatedProducts->count() < 4) {
            $needed = 4 - $relatedProducts->count();
            $extra = Product::where('id', '!=', $product->id)
                            ->whereNotIn('id', $relatedProducts->pluck('id'))
                            ->latest()
                            ->take($needed)
                            ->get();
            $relatedProducts = $relatedProducts->concat($extra);
        }

        return view('pages.product-show', compact('product', 'relatedProducts'));
    }
}
