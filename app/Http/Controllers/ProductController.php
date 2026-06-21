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

        $collections = $query->paginate(12)->withQueryString();
        return view('pages.collections', compact('collections'));
    }

    public function index(Request $request)
    {
        $query = Product::with('category');

        // Filter by Collections (Category checkboxes + Special category markers: new arrivals / bestsellers)
        $hasNewCollectionFilter = $request->filled('new_arrivals') || $request->input('filter') === 'new-arrivals' || $request->input('filter') === 'new_arrivals';
        $hasBestsellersCollectionFilter = $request->filled('bestsellers') || $request->input('filter') === 'bestsellers' || $request->input('filter') === 'bestseller';
        $hasCategoryFilter = $request->filled('category');

        if ($hasNewCollectionFilter || $hasBestsellersCollectionFilter || $hasCategoryFilter) {
            $query->where(function($q) use ($request, $hasNewCollectionFilter, $hasBestsellersCollectionFilter, $hasCategoryFilter) {
                $first = true;

                if ($hasNewCollectionFilter) {
                    $q->where('is_new_arrival', true);
                    $first = false;
                }

                if ($hasBestsellersCollectionFilter) {
                    if ($first) {
                        $q->where('is_bestseller', true);
                        $first = false;
                    } else {
                        $q->orWhere('is_bestseller', true);
                    }
                }

                if ($hasCategoryFilter) {
                    $categories = is_array($request->category) ? $request->category : [$request->category];
                    if ($first) {
                        $q->whereHas('category', function($subQ) use ($categories) {
                            $subQ->whereIn('slug', $categories);
                        });
                        $first = false;
                    } else {
                        $q->orWhereHas('category', function($subQ) use ($categories) {
                            $subQ->whereIn('slug', $categories);
                        });
                    }
                }
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

        // Search query (NB: products has no 'description' column — searching it
        // threw an SQL error under strict mode, which broke search entirely).
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('seo_keywords', 'like', "%{$search}%")
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

        $products = $query->paginate(12)->withQueryString();
        $categories = \Illuminate\Support\Facades\Cache::remember('all_categories', 86400, function() {
            return Category::withCount('products')->get();
        });
        
        // Extract unique tags from all products for the filter sidebar
        $allTags = \Illuminate\Support\Facades\Cache::remember('all_tags', 86400, function() {
            return Product::whereNotNull('tags')->pluck('tags')->flatten()->unique()->filter()->values()->toArray();
        });

        return view('pages.shop', compact('products', 'categories', 'allTags'));
    }

    public function show($slug)
    {
        $product = Product::with(['sizes', 'reviews.user', 'category'])->where('slug', $slug)->firstOrFail();

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

        // The Review model's saved/deleted hook recomputes the product's
        // rating + review_count, so they stay correct even on review deletion.
        $product->reviews()->updateOrCreate(
            ['user_id' => auth()->id()],
            ['rating' => $request->rating, 'comment' => $request->comment]
        );

        return back()->with('success', 'Review submitted successfully.');
    }
}
