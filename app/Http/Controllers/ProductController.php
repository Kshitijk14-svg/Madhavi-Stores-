<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;

class ProductController extends Controller
{
    public function collections(Request $request)
    {
        $query = Category::query();

        // Sorting A-Z, Z-A
        if ($request->has('sort')) {
            if ($request->sort === 'az') {
                $query->orderBy('name', 'asc');
            } elseif ($request->sort === 'za') {
                $query->orderBy('name', 'desc');
            }
        } else {
            $query->orderBy('name', 'asc');
        }

        $collections = $query->get();
        return view('pages.collections', compact('collections'));
    }

    public function index(Request $request)
    {
        $query = Product::query();

        // Filter by category (collections)
        if ($request->filled('category')) {
            $categories = is_array($request->category) ? $request->category : [$request->category];
            $query->whereHas('category', function($q) use ($categories) {
                $q->whereIn('slug', $categories);
            });
        }

        // Filter by tags
        if ($request->filled('tags') && is_array($request->tags)) {
            $tags = $request->tags;
            $query->where(function($q) use ($tags) {
                foreach ($tags as $tag) {
                    $q->orWhereJsonContains('tags', $tag);
                }
            });
        }

        // Filter by price range
        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }

        // Search query
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereJsonContains('tags', $search);
                  
                if (is_numeric($search)) {
                    $q->orWhere('id', $search);
                }
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
        
        // Extract unique tags from all products for the filter sidebar
        $allTags = Product::whereNotNull('tags')->pluck('tags')->flatten()->unique()->filter()->values()->toArray();

        return view('pages.shop', compact('products', 'categories', 'allTags'));
    }

    public function show($slug)
    {
        $product = Product::with(['sizes', 'reviews.user'])->where('slug', $slug)->firstOrFail();

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

        $hasPurchased = false;
        if (auth()->check()) {
            $hasPurchased = auth()->user()->orders()->whereHas('items', function ($query) use ($product) {
                $query->where('product_id', $product->id);
            })->exists();
        }

        return view('pages.product-show', compact('product', 'relatedProducts', 'hasPurchased'));
    }

    public function review(Request $request, Product $product)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $hasPurchased = auth()->user()->orders()->whereHas('items', function ($query) use ($product) {
            $query->where('product_id', $product->id);
        })->exists();

        if (!$hasPurchased) {
            return back()->with('error', 'You can only review products you have purchased.');
        }

        $product->reviews()->updateOrCreate(
            ['user_id' => auth()->id()],
            ['rating' => $request->rating, 'comment' => $request->comment]
        );

        $avgRating = $product->reviews()->avg('rating');
        $product->update([
            'rating' => $avgRating,
            'review_count' => $product->reviews()->count()
        ]);

        return back()->with('success', 'Review submitted successfully.');
    }
}
