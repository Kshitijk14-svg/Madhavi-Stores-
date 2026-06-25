<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Coupon;
use App\Models\Setting;
use App\Models\CartItem;
use App\Models\WishlistItem;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminController extends Controller
{
    public function dashboard()
    {
        $revenue = Order::where('order_status', '!=', 'Cancelled')->sum('total');
        $ordersCount = Order::where('order_status', '!=', 'Cancelled')->count();
        $avgOrderValue = $ordersCount > 0 ? $revenue / $ordersCount : 0;
        $customersCount = User::where('role', 'customer')->count();
        if ($customersCount === 0) {
            $customersCount = User::count();
        }
        
        $stats = [
            ['label' => 'Total Revenue', 'value' => '₹' . number_format($revenue, 2), 'trend' => 'Non-Cancelled'],
            ['label' => 'Sales Volume', 'value' => number_format($ordersCount), 'trend' => 'Total Orders'],
            ['label' => 'Average Order', 'value' => '₹' . number_format($avgOrderValue, 2), 'trend' => 'Avg Value (AOV)'],
            ['label' => 'Registered Clients', 'value' => number_format($customersCount), 'trend' => 'Atelier Members'],
        ];

        $recentProducts = Product::with('category')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentProducts'));
    }

    public function salesChartData(Request $request)
    {
        $range = $request->input('range', 'month');
        $labels = [];
        $values = [];

        switch ($range) {
            case 'custom':
                if ($request->has('start') && $request->has('end')) {
                    $request->validate(['start' => 'required|date', 'end' => 'required|date|after_or_equal:start']);
                    $start = Carbon::parse($request->input('start'))->startOfDay();
                    $end = Carbon::parse($request->input('end'))->endOfDay();
                    
                    $orders = Order::where('order_status', '!=', 'Cancelled')
                        ->whereBetween('created_at', [$start, $end])
                        ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as total_sales'))
                        ->groupBy('date')
                        ->pluck('total_sales', 'date')
                        ->toArray();

                    $days = $start->diffInDays($end);
                    if ($days > 60) $days = 60; // Max limit to prevent browser crash
                    
                    for ($i = 0; $i <= $days; $i++) {
                        $dateObj = $start->copy()->addDays($i);
                        $dateStr = $dateObj->format('Y-m-d');
                        $labels[] = $dateObj->format('d M');
                        $values[] = $orders[$dateStr] ?? 0;
                    }
                }
                break;

            case 'day':
                // Hourly sales for today
                $start = Carbon::today();
                $end = Carbon::today()->endOfDay();
                
                $orders = Order::where('order_status', '!=', 'Cancelled')
                    ->whereBetween('created_at', [$start, $end])
                    ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('SUM(total) as total_sales'))
                    ->groupBy('hour')
                    ->pluck('total_sales', 'hour')
                    ->toArray();

                for ($i = 0; $i < 24; $i++) {
                    $hourLabel = $i % 12 === 0 ? 12 : $i % 12;
                    $ampm = $i < 12 ? 'AM' : 'PM';
                    $labels[] = $hourLabel . ' ' . $ampm;
                    $values[] = $orders[$i] ?? 0;
                }
                break;

            case 'week':
                // Daily sales for the last 7 days
                $start = Carbon::now()->subDays(6)->startOfDay();
                $end = Carbon::now()->endOfDay();

                $orders = Order::where('order_status', '!=', 'Cancelled')
                    ->whereBetween('created_at', [$start, $end])
                    ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as total_sales'))
                    ->groupBy('date')
                    ->pluck('total_sales', 'date')
                    ->toArray();

                for ($i = 6; $i >= 0; $i--) {
                    $date = Carbon::now()->subDays($i)->format('Y-m-d');
                    $dateObj = Carbon::parse($date);
                    $labels[] = $dateObj->format('D');
                    $values[] = $orders[$date] ?? 0;
                }
                break;

            case 'month':
                // Daily sales for the last 30 days
                $start = Carbon::now()->subDays(29)->startOfDay();
                $end = Carbon::now()->endOfDay();

                $orders = Order::where('order_status', '!=', 'Cancelled')
                    ->whereBetween('created_at', [$start, $end])
                    ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as total_sales'))
                    ->groupBy('date')
                    ->pluck('total_sales', 'date')
                    ->toArray();

                for ($i = 29; $i >= 0; $i--) {
                    $date = Carbon::now()->subDays($i)->format('Y-m-d');
                    $dateObj = Carbon::parse($date);
                    $labels[] = $dateObj->format('d M');
                    $values[] = $orders[$date] ?? 0;
                }
                break;

            case 'year':
                // Monthly sales for the current year
                $start = Carbon::now()->startOfYear();
                $end = Carbon::now()->endOfYear();

                $orders = Order::where('order_status', '!=', 'Cancelled')
                    ->whereBetween('created_at', [$start, $end])
                    ->select(DB::raw('MONTH(created_at) as month'), DB::raw('SUM(total) as total_sales'))
                    ->groupBy('month')
                    ->pluck('total_sales', 'month')
                    ->toArray();

                for ($i = 1; $i <= 12; $i++) {
                    $labels[] = Carbon::create()->month($i)->format('M');
                    $values[] = $orders[$i] ?? 0;
                }
                break;

            case 'all':
            default:
                // Sales by year-month for all time (up to 12 labels)
                $orders = Order::where('order_status', '!=', 'Cancelled')
                    ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month_year'), DB::raw('SUM(total) as total_sales'))
                    ->groupBy('month_year')
                    ->orderBy('month_year', 'asc')
                    ->take(12)
                    ->pluck('total_sales', 'month_year')
                    ->toArray();

                if (empty($orders)) {
                    $labels[] = Carbon::now()->format('Y');
                    $values[] = 0;
                } else {
                    foreach ($orders as $monthYear => $totalSales) {
                        $dateObj = Carbon::parse($monthYear . '-01');
                        $labels[] = $dateObj->format('M Y');
                        $values[] = $totalSales;
                    }
                }
                break;
        }

        return response()->json([
            'labels' => $labels,
            'values' => $values,
            'total' => '₹' . number_format(array_sum($values), 2),
            'orders_count' => Order::where('order_status', '!=', 'Cancelled')->count(),
        ]);
    }

    // ── PRODUCTS CRUD ───────────────────────────────────────────

    public function products(Request $request)
    {
        $query = Product::with('category');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('slug', 'LIKE', "%{$search}%")
                  ->orWhere('badge', 'LIKE', "%{$search}%")
                  ->orWhere('seo_title', 'LIKE', "%{$search}%")
                  ->orWhere('seo_description', 'LIKE', "%{$search}%");
                  
                if (is_numeric($search)) {
                    $q->orWhere('id', $search);
                }
            });
        }

        // Category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // Stock status filter
        if ($request->filled('stock_status')) {
            $status = $request->input('stock_status');
            if ($status === 'in_stock') {
                $query->where(function($q) {
                    $q->where(function($sq) {
                        $sq->where('has_sizes', false)
                           ->where('stock', '>', 0);
                    })->orWhere(function($sq) {
                        $sq->where('has_sizes', true)
                           ->whereHas('sizes', function($ssq) {
                               $ssq->where('stock', '>', 0);
                           });
                    });
                });
            } elseif ($status === 'out_of_stock') {
                $query->where(function($q) {
                    $q->where(function($sq) {
                        $sq->where('has_sizes', false)
                           ->where('stock', '<=', 0);
                    })->orWhere(function($sq) {
                        $sq->where('has_sizes', true)
                           ->whereDoesntHave('sizes', function($ssq) {
                               $ssq->where('stock', '>', 0);
                           });
                    });
                });
            }
        }

        // Sort option
        $sortBy = $request->input('sort_by', 'latest');
        switch ($sortBy) {
            case 'price_low_high':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high_low':
                $query->orderBy('price', 'desc');
                break;
            case 'name_a_z':
                $query->orderBy('name', 'asc');
                break;
            case 'name_z_a':
                $query->orderBy('name', 'desc');
                break;
            case 'bestseller':
                $query->orderBy('is_bestseller', 'desc')->latest();
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        $products = $query->paginate(15)->withQueryString();
        $categories = Category::all();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function createProduct()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    public function storeProduct(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'price'          => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'category_id'    => 'required|exists:categories,id',
            'stock'          => 'nullable|integer|min:0',
            'has_sizes'      => 'nullable|boolean',
            'image'          => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
            'image_url'      => 'nullable|url',
            'size_chart_image'=> 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
            'badge'          => 'nullable|string|max:50',
            'is_bestseller'  => 'nullable|boolean',
            'discount_type'  => 'nullable|in:percent,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'is_new_arrival' => 'nullable|boolean',
            'new_arrival_expires_at' => 'nullable|date',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
            'seo_title'      => 'nullable|string|max:255',
            'seo_description'=> 'nullable|string',
            'seo_keywords'   => 'nullable|string|max:255',
            'details'        => 'nullable|string',
            'sizes'          => 'nullable|array',
            'sizes.*'        => 'nullable|integer|min:0',
        ]);

        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $count = 1;
        while (Product::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $imageUrl = $request->image_url;
        if ($request->hasFile('image')) {
            $imageUrl = $this->convertToWebp($request->file('image'), 'images/products') ?: $imageUrl;
        }

        $gallery = array_fill(0, 7, null);
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $index => $gFile) {
                if ($index >= 0 && $index < 7 && $gFile) {
                    $webpUrl = $this->convertToWebp($gFile, 'images/products');
                    if ($webpUrl) {
                        $gallery[$index] = $webpUrl;
                    }
                }
            }
        }
        $gallery = array_values(array_filter($gallery));

        $sizeChartUrl = null;
        if ($request->hasFile('size_chart_image')) {
            $webpUrl = $this->convertToWebp($request->file('size_chart_image'), 'images/products');
            if ($webpUrl) {
                $sizeChartUrl = ltrim($webpUrl, '/');
            }
        }

        $details = null;
        if ($request->filled('details')) {
            $details = array_values(array_filter(array_map('trim', explode("\n", $request->details))));
        }

        $product = Product::create([
            'name'           => $request->name,
            'slug'           => $slug,
            'price'          => $request->price,
            'original_price' => $request->original_price,
            'category_id'    => $request->category_id,
            'has_sizes'      => $request->has_sizes == '1',
            'stock'          => $request->has_sizes == '1' ? 0 : (int)$request->stock,
            'image_url'      => $imageUrl,
            'size_chart_image'=> $sizeChartUrl,
            'badge'          => $request->badge,
            'is_bestseller'  => $request->has('is_bestseller'),
            'rating'         => 5.0,
            'review_count'   => 0,
            'discount_type'  => $request->discount_type,
            'discount_value' => $request->discount_value,
            'is_new_arrival' => $request->has('is_new_arrival'),
            'new_arrival_expires_at' => $request->new_arrival_expires_at ? Carbon::parse($request->new_arrival_expires_at) : null,
            'gallery_images' => $gallery,
            'seo_title'      => $request->seo_title,
            'seo_description'=> $request->seo_description,
            'seo_keywords'   => $request->seo_keywords,
            'details'        => $details,
        ]);

        if ($request->has_sizes == '1' && $request->has('sizes')) {
            foreach ($request->sizes as $size => $stock) {
                if ($stock !== null && $stock !== '') {
                    $product->sizes()->create([
                        'size' => $size,
                        'stock' => (int)$stock
                    ]);
                }
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Product added successfully.');
    }

    public function editProduct($id)
    {
        $product    = Product::with('sizes')->findOrFail($id);
        $categories = Category::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function updateProduct(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name'           => 'required|string|max:255',
            'price'          => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'category_id'    => 'required|exists:categories,id',
            'image'          => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
            'image_url'      => 'nullable|url',
            'badge'          => 'nullable|string|max:50',
            'is_bestseller'  => 'nullable|boolean',
            'discount_type'  => 'nullable|in:percent,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'is_new_arrival' => 'nullable|boolean',
            'new_arrival_expires_at' => 'nullable|date',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
            'existing_gallery' => 'nullable|array',
            'seo_title'      => 'nullable|string|max:255',
            'seo_description'=> 'nullable|string',
            'seo_keywords'   => 'nullable|string|max:255',
            'details'        => 'nullable|string',
            'sizes'          => 'nullable|array',
            'sizes.*'        => 'nullable|integer|min:0',
        ]);

        $slug = $product->slug;
        if ($product->name !== $request->name) {
            $slug = Str::slug($request->name);
            $originalSlug = $slug;
            $count = 1;
            while (Product::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }
        }

        $oldImageUrl = $product->image_url;
        $imageUrl = $request->image_url ?? $product->image_url;
        if ($request->hasFile('image')) {
            $newImageUrl = $this->convertToWebp($request->file('image'), 'images/products');
            if ($newImageUrl) {
                $this->deleteLocalFile($oldImageUrl);
                $imageUrl = $newImageUrl;
            }
        }

        $gallery = array_fill(0, 7, null);
        if ($request->has('existing_gallery')) {
            foreach ($request->existing_gallery as $index => $imgUrl) {
                if ($index >= 0 && $index < 7) {
                    $gallery[$index] = $imgUrl;
                }
            }
        }
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $index => $gFile) {
                if ($index >= 0 && $index < 7 && $gFile) {
                    $webpUrl = $this->convertToWebp($gFile, 'images/products');
                    if ($webpUrl) {
                        $gallery[$index] = $webpUrl;
                    }
                }
            }
        }
        $gallery = array_values(array_filter($gallery));

        $sizeChartUrl = $product->size_chart_image;
        if ($request->hasFile('size_chart_image')) {
            $webpUrl = $this->convertToWebp($request->file('size_chart_image'), 'images/products');
            if ($webpUrl) {
                $sizeChartUrl = ltrim($webpUrl, '/');
            }
        }

        $details = $product->details;
        if ($request->has('details')) {
            $details = null;
            if ($request->filled('details')) {
                $details = array_values(array_filter(array_map('trim', explode("\n", $request->details))));
            }
        }

        $product->update([
            'name'           => $request->name,
            'slug'           => $slug,
            'price'          => $request->price,
            'original_price' => $request->original_price,
            'category_id'    => $request->category_id,
            'has_sizes'      => $request->has_sizes == '1',
            'stock'          => $request->has_sizes == '1' ? 0 : (int)$request->stock,
            'image_url'      => $imageUrl,
            'size_chart_image'=> $sizeChartUrl,
            'badge'          => $request->badge,
            'is_bestseller'  => $request->has('is_bestseller'),
            'discount_type'  => $request->discount_type,
            'discount_value' => $request->discount_value,
            'is_new_arrival' => $request->has('is_new_arrival'),
            'new_arrival_expires_at' => $request->new_arrival_expires_at ? Carbon::parse($request->new_arrival_expires_at) : null,
            'gallery_images' => $gallery,
            'seo_title'      => $request->seo_title,
            'seo_description'=> $request->seo_description,
            'seo_keywords'   => $request->seo_keywords,
            'details'        => $details,
        ]);

        if ($request->has_sizes == '1') {
            if ($request->has('sizes')) {
                foreach ($request->sizes as $size => $stock) {
                    if ($stock !== null && $stock !== '') {
                        $product->sizes()->updateOrCreate(
                            ['size' => $size],
                            ['stock' => (int)$stock]
                        );
                    }
                }
            }
        } else {
            $product->sizes()->delete();
        }

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function deleteProduct($id)
    {
        $product = Product::findOrFail($id);

        // Delete associated image files from disk
        $this->deleteLocalFile($product->image_url);
        $this->deleteLocalFile($product->size_chart_image);
        if (is_array($product->gallery_images)) {
            foreach ($product->gallery_images as $galleryImg) {
                $this->deleteLocalFile($galleryImg);
            }
        }

        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }

    // ── CATEGORIES (COLLECTIONS) CRUD ────────────────────────────

    public function categories(Request $request)
    {
        $query = Category::withCount('products');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('slug', 'LIKE', "%{$search}%");
            });
        }

        // Product count range filter
        if ($request->filled('product_count_range')) {
            $range = $request->input('product_count_range');
            switch ($range) {
                case 'empty':
                    $query->having('products_count', '=', 0);
                    break;
                case '1_10':
                    $query->having('products_count', '>=', 1)->having('products_count', '<=', 10);
                    break;
                case '11_50':
                    $query->having('products_count', '>=', 11)->having('products_count', '<=', 50);
                    break;
                case '50_plus':
                    $query->having('products_count', '>', 50);
                    break;
            }
        }

        // Sort option
        $sortBy = $request->input('sort_by', 'latest');
        switch ($sortBy) {
            case 'name_a_z':
                $query->orderBy('name', 'asc');
                break;
            case 'name_z_a':
                $query->orderBy('name', 'desc');
                break;
            case 'products_desc':
                $query->orderBy('products_count', 'desc');
                break;
            case 'products_asc':
                $query->orderBy('products_count', 'asc');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        $categories = $query->paginate(20)->withQueryString();
        return view('admin.categories.index', compact('categories'));
    }

    public function createCategory()
    {
        return view('admin.categories.create');
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255|unique:categories,name',
            'image'     => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
            'image_url' => 'nullable|url',
            'size_chart_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
        ]);

        $slug     = Str::slug($request->name);
        $imageUrl = $request->image_url;

        if ($request->hasFile('image')) {
            $imageUrl = $this->convertToWebp($request->file('image'), 'images/categories') ?: $imageUrl;
        }

        $sizeChartUrl = null;
        if ($request->hasFile('size_chart_image')) {
            $sizeChartUrl = $this->convertToWebp($request->file('size_chart_image'), 'images/categories') ?: $sizeChartUrl;
        }

        Category::create([
            'name'      => $request->name,
            'slug'      => $slug,
            'image_url' => $imageUrl,
            'size_chart_image' => $sizeChartUrl,
        ]);

        \Illuminate\Support\Facades\Cache::forget('all_categories_no_count');
        \Illuminate\Support\Facades\Cache::forget('all_categories');

        return redirect()->route('admin.categories.index')->with('success', 'Collection created successfully.');
    }

    public function editCategory($id)
    {
        $category = Category::findOrFail($id);
        return view('admin.categories.edit', compact('category'));
    }

    public function updateCategory(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name'      => 'required|string|max:255|unique:categories,name,' . $id,
            'image'     => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
            'image_url' => 'nullable|url',
            'size_chart_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
        ]);

        $slug     = Str::slug($request->name);
        $imageUrl = $request->image_url ?? $category->image_url;

        if ($request->hasFile('image')) {
            $imageUrl = $this->convertToWebp($request->file('image'), 'images/categories') ?: $imageUrl;
        }

        $sizeChartUrl = $category->size_chart_image;
        if ($request->hasFile('size_chart_image')) {
            $sizeChartUrl = $this->convertToWebp($request->file('size_chart_image'), 'images/categories') ?: $sizeChartUrl;
        }

        // Delete old image files if new ones were uploaded
        if ($request->hasFile('image') && $category->image_url && $imageUrl !== $category->image_url) {
            $this->deleteLocalFile($category->image_url);
        }
        if ($request->hasFile('size_chart_image') && $category->size_chart_image && $sizeChartUrl !== $category->size_chart_image) {
            $this->deleteLocalFile($category->size_chart_image);
        }

        $category->update([
            'name'      => $request->name,
            'slug'      => $slug,
            'image_url' => $imageUrl,
            'size_chart_image' => $sizeChartUrl,
        ]);

        \Illuminate\Support\Facades\Cache::forget('all_categories_no_count');
        \Illuminate\Support\Facades\Cache::forget('all_categories');

        return redirect()->route('admin.categories.index')->with('success', 'Collection updated successfully.');
    }

    public function deleteCategory($id)
    {
        $category = Category::withCount('products')->findOrFail($id);

        if ($category->products_count > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Cannot delete "' . $category->name . '" — it has ' . $category->products_count . ' product(s) assigned to it. Reassign or delete those products first.');
        }

        $this->deleteLocalFile($category->image_url);
        $this->deleteLocalFile($category->size_chart_image);

        $category->delete();

        \Illuminate\Support\Facades\Cache::forget('all_categories_no_count');
        \Illuminate\Support\Facades\Cache::forget('all_categories');

        return redirect()->route('admin.categories.index')->with('success', 'Collection deleted successfully.');
    }

    // ── ORDERS MANAGEMENT ────────────────────────────────────────

    public function orders(Request $request)
    {
        $query = Order::with(['items.product', 'user']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")
                  ->orWhere('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // Order status filter
        if ($request->filled('status')) {
            $query->where('order_status', $request->status);
        }

        // Payment status filter
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Min price filter
        if ($request->filled('min_price')) {
            $query->where('total', '>=', $request->min_price);
        }

        // Max price filter
        if ($request->filled('max_price')) {
            $query->where('total', '<=', $request->max_price);
        }

        // Sorting options
        $sortBy = $request->input('sort_by', 'latest');
        switch ($sortBy) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'total_high_low':
                $query->orderBy('total', 'desc');
                break;
            case 'total_low_high':
                $query->orderBy('total', 'asc');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        $orders = $query->paginate(15)->withQueryString();
        return view('admin.orders.index', compact('orders'));
    }

    public function updateOrderStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        $request->validate([
            'order_status'   => 'required|in:Pending,Processing,Shipped,Delivered,Cancelled',
            'payment_status' => 'required|in:Pending,Paid,Refunded',
        ]);

        $order->update([
            'order_status'   => $request->order_status,
            'payment_status' => $request->payment_status,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully.',
                'order_status' => $order->order_status,
                'payment_status' => $order->payment_status
            ]);
        }

        return redirect()->route('admin.orders.index')->with('success', 'Order status updated successfully.');
    }

    public function deleteOrder($id)
    {
        Order::findOrFail($id)->delete();
        return redirect()->route('admin.orders.index')->with('success', 'Order record deleted.');
    }

    /**
     * Generate and download a PDF invoice for the given order.
     */
    public function orderInvoice($id)
    {
        $order = Order::with(['items.product', 'user'])->findOrFail($id);

        try {
            $pdf = Pdf::loadView('admin.invoice', compact('order'))
                      ->setPaper('a4', 'portrait');

            return $pdf->download('Invoice-' . $order->order_number . '.pdf');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Invoice PDF generation failed: ' . $e->getMessage(), ['order_id' => $id]);
            return redirect()->route('admin.orders.index')
                ->with('error', 'Could not generate the invoice PDF for order ' . $order->order_number . '. Please try again.');
        }
    }

    /**
     * Dispatch an email with the PDF invoice attached to the customer.
     */
    public function sendInvoiceEmail($id)
    {
        $order = Order::with(['items.product', 'user'])->findOrFail($id);

        if (!$order->user || !$order->user->email) {
            return response()->json(['success' => false, 'message' => 'Customer email not found.']);
        }

        try {
            \Illuminate\Support\Facades\Mail::to($order->user->email)
                ->queue(new \App\Mail\InvoiceMail($order));
            
            return response()->json(['success' => true, 'message' => 'Invoice queued for sending successfully.']);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to queue invoice email: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to queue invoice email.']);
        }
    }

    // ── USER MANAGEMENT WITH ROLE CONTROL ──────────────────────

    public function usersCartWishlist()
    {
        $query = User::with(['cartItems.product', 'wishlistItems.product', 'orders'])
                     ->withSum('orders', 'total');

        // Search by name or email
        if (request()->filled('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // Filter by role
        if (request()->filled('role')) {
            $query->where('role', request('role'));
        }

        // Filter by total spend range
        if (request()->filled('min_spend')) {
            $query->having('orders_sum_total', '>=', request('min_spend'));
        }
        if (request()->filled('max_spend')) {
            $query->having('orders_sum_total', '<=', request('max_spend'));
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        $totalCartsCount    = CartItem::sum('quantity');
        $totalWishlistCount = WishlistItem::count();

        $mostWishlisted = WishlistItem::select('product_id', DB::raw('count(*) as count'))
                                     ->groupBy('product_id')
                                     ->orderBy('count', 'desc')
                                     ->first();

        $mostWishlistedProduct = null;
        if ($mostWishlisted) {
            $mostWishlistedProduct = Product::find($mostWishlisted->product_id);
            if ($mostWishlistedProduct) {
                $mostWishlistedProduct->wishlist_count = $mostWishlisted->count;
            }
        }

        return view('admin.users.index', compact(
            'users', 'totalCartsCount', 'totalWishlistCount', 'mostWishlistedProduct'
        ));
    }

    /**
     * Update a user's role.
     */
    public function updateUserRole(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Protect SuperAdmins from being changed by non-SuperAdmins
        if ($user->role === 'superadmin' && !auth()->user()->isSuperAdmin()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only Super Administrators can modify a Super Admin.'
                ], 403);
            }
            return redirect()->route('admin.users.index')
                             ->with('error', 'Only Super Administrators can modify a Super Admin.');
        }

        $request->validate([
            'role' => 'required|in:customer,admin,superadmin',
        ]);

        // Prevent non-superadmins from assigning superadmin role
        if ($request->role === 'superadmin' && !auth()->user()->isSuperAdmin()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only Super Administrators can assign the Super Admin role.'
                ], 403);
            }
            return redirect()->route('admin.users.index')
                             ->with('error', 'Only Super Administrators can assign the Super Admin role.');
        }

        // Prevent changing another superadmin's role
        if ($user->role === 'superadmin' && $user->id !== auth()->id()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot change another Super Administrator\'s role.'
                ], 403);
            }
            return redirect()->route('admin.users.index')
                             ->with('error', 'You cannot change another Super Administrator\'s role.');
        }

        // role is the single source of truth; the User saving() hook keeps the
        // legacy is_admin column in sync automatically.
        $user->role = $request->role;
        $user->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Role for {$user->name} updated to " . ucfirst($request->role) . '.',
                'role' => $request->role
            ]);
        }

        return redirect()->route('admin.users.index')
                         ->with('success', "Role for {$user->name} updated to " . ucfirst($request->role) . '.');
    }

    // ── COUPON MANAGEMENT ────────────────────────────────────────

    public function coupons()
    {
        $coupons = Coupon::latest()->get();
        return view('admin.coupons.index', compact('coupons'));
    }

    public function storeCoupon(Request $request)
    {
        $request->validate([
            'code'               => 'required|string|unique:coupons,code|max:50',
            'type'               => 'required|in:percent,fixed',
            'value'              => 'required|numeric|min:0',
            'min_cart_value'     => 'required|numeric|min:0',
            'expires_at'         => 'nullable|date',
            'max_uses'           => 'nullable|integer|min:1',
            'max_uses_per_user'  => 'required|integer|min:1',
            'is_active'          => 'nullable|boolean',
        ]);

        // Convert IST datetime to UTC for storage
        $expiresAt = null;
        if ($request->filled('expires_at')) {
            $expiresAt = Carbon::createFromFormat('Y-m-d\TH:i', $request->expires_at, 'Asia/Kolkata')
                               ->setTimezone('UTC');
        }

        Coupon::create([
            'code'              => strtoupper($request->code),
            'type'              => $request->type,
            'value'             => $request->value,
            'min_cart_value'    => $request->min_cart_value,
            'expires_at'        => $expiresAt,
            'max_uses'          => $request->max_uses ?: null,
            'max_uses_per_user' => $request->max_uses_per_user ?? 1,
            'is_active'         => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon created successfully.');
    }

    public function updateCoupon(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);

        $request->validate([
            'code'               => 'required|string|max:50|unique:coupons,code,' . $id,
            'type'               => 'required|in:percent,fixed',
            'value'              => 'required|numeric|min:0',
            'min_cart_value'     => 'required|numeric|min:0',
            'expires_at'         => 'nullable|date',
            'max_uses'           => 'nullable|integer|min:1',
            'max_uses_per_user'  => 'required|integer|min:1',
        ]);

        $expiresAt = null;
        if ($request->filled('expires_at')) {
            $expiresAt = Carbon::createFromFormat('Y-m-d\TH:i', $request->expires_at, 'Asia/Kolkata')
                               ->setTimezone('UTC');
        }

        $coupon->update([
            'code'              => strtoupper($request->code),
            'type'              => $request->type,
            'value'             => $request->value,
            'min_cart_value'    => $request->min_cart_value,
            'expires_at'        => $expiresAt,
            'max_uses'          => $request->max_uses ?: null,
            'max_uses_per_user' => $request->max_uses_per_user ?? 1,
        ]);

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon updated successfully.');
    }

    public function toggleCoupon($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->update(['is_active' => !$coupon->is_active]);

        $status = $coupon->is_active ? 'activated' : 'deactivated';
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Coupon {$coupon->code} {$status}.",
                'is_active' => $coupon->is_active
            ]);
        }

        return redirect()->route('admin.coupons.index')->with('success', "Coupon {$coupon->code} {$status}.");
    }

    public function deleteCoupon($id)
    {
        Coupon::findOrFail($id)->delete();
        return redirect()->route('admin.coupons.index')->with('success', 'Coupon deleted.');
    }

    // ── EDITORIAL DESIGN MANAGER ─────────────────────────────────

    public function designManager()
    {
        $heroSlides  = Setting::get('hero_slides', []);
        $about       = Setting::get('about_settings', []);
        $signinImage = Setting::get('signin_image', '');

        // Fetch homepage layout settings
        $defaultSections = [
            ['id' => 'hero', 'name' => 'Hero Carousel', 'visible' => true],
            ['id' => 'categories', 'name' => 'Category Circles', 'visible' => true],
            ['id' => 'dual_banners', 'name' => 'Dual Banners', 'visible' => true],
            ['id' => 'new_arrivals', 'name' => 'New Arrivals Slider', 'visible' => true],
            ['id' => 'promo_banner', 'name' => 'Promo Banner', 'visible' => true],
            ['id' => 'bestsellers', 'name' => 'Bestsellers Grid', 'visible' => true],
            ['id' => 'instagram_feed', 'name' => 'Instagram Feed', 'visible' => true],
            ['id' => 'newsletter', 'name' => 'Newsletter Atelier', 'visible' => true],
        ];
        $homepageSections = Setting::get('homepage_sections', $defaultSections);
        // Exclude the (unbuilt) lookbook section.
        $homepageSections = array_values(array_filter($homepageSections, function($sec) {
            return !in_array($sec['id'], ['lookbook']);
        }));

        // Fetch Dual Banners settings
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

        // Fetch Promo Banner settings
        $defaultPromoBanner = [
            'image_url' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=1920&q=80&auto=format&fit=crop',
            'eyebrow' => 'Limited Time',
            'title' => "Up to 40% Off\nBestsellers",
            'button_text' => 'Shop the Sale',
            'button_link' => '/collection'
        ];
        $promoBanner = Setting::get('promo_banner_settings', $defaultPromoBanner);

        // Fetch Newsletter settings
        $defaultNewsletter = [
            'eyebrow' => 'Stay Close',
            'title' => 'Join the <em>Atelier</em>',
            'description' => 'Early access, exclusive drops, editorial stories.'
        ];
        $newsletter = Setting::get('newsletter_settings', $defaultNewsletter);

        // Fetch Instagram settings (display handle only; token lives in .env).
        $instagram = Setting::get('instagram_settings', ['handle' => 'madhavistores']);

        return view('admin.design.index', compact(
            'heroSlides', 'about', 'signinImage',
            'homepageSections', 'dualBanners', 'promoBanner', 'newsletter', 'instagram'
        ));
    }

    public function updateDesignSettings(Request $request)
    {
        $type = $request->input('type');

        // Link/URL fields are rendered into href/src on the storefront. Block
        // dangerous schemes (javascript:, data:, vbscript:) while still allowing
        // relative paths like "images/banners/x.webp" and normal http(s) URLs.
        // Uploaded files must be real images (convertToWebp re-encodes them too).
        $safeUrl = ['nullable', 'string', 'max:2048', 'not_regex:/^\s*(?:javascript|data|vbscript):/i'];
        $image   = ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:2048'];

        $request->validate([
            'banner1_link'  => $safeUrl, 'banner2_link'  => $safeUrl, 'banner3_link'  => $safeUrl,
            'banner1_image_url' => $safeUrl, 'banner2_image_url' => $safeUrl, 'banner3_image_url' => $safeUrl,
            'banner1_file'  => $image, 'banner2_file'  => $image, 'banner3_file'  => $image,
            'promo_button_link' => $safeUrl, 'promo_image_url' => $safeUrl, 'promo_file' => $image,
            'signin_image'  => $safeUrl, 'signin_file' => $image,
            'cover_image'   => $safeUrl, 'about_cover_file' => $image,
            'slides.*.image_url'         => $safeUrl,
            'slides.*.mobile_image_url'  => $safeUrl,
            'slides.*.button_url'        => $safeUrl,
            'slides.*.second_button_url' => $safeUrl,
            'slides.*.image_file'        => $image,
            'slides.*.mobile_image_file' => $image,
        ]);

        // ── Homepage Layout & Banners ─────────────────────────────
        if ($type === 'layout') {
            // Save sections order and visibility
            if ($request->has('sections_json')) {
                $sections = json_decode($request->input('sections_json'), true);
                if (is_array($sections)) {
                    Setting::set('homepage_sections', $sections);
                }
            }

            // Save Dual Banners settings
            $dualBanners = Setting::get('dual_banners_settings', []);
            $banners = ['banner1', 'banner2', 'banner3'];
            foreach ($banners as $bKey) {
                if (!isset($dualBanners[$bKey])) {
                    $dualBanners[$bKey] = [];
                }
                $dualBanners[$bKey]['eyebrow'] = $request->input("{$bKey}_eyebrow");
                $dualBanners[$bKey]['title'] = $request->input("{$bKey}_title");
                $dualBanners[$bKey]['link'] = $request->input("{$bKey}_link");

                $dualBanners[$bKey]['image_url'] = $request->input("{$bKey}_image_url") ?? ($dualBanners[$bKey]['image_url'] ?? '');
                if ($request->hasFile("{$bKey}_file")) {
                    $webpUrl = $this->convertToWebp($request->file("{$bKey}_file"), 'images/banners');
                    if ($webpUrl) {
                        $dualBanners[$bKey]['image_url'] = $webpUrl;
                    }
                }
            }
            Setting::set('dual_banners_settings', $dualBanners);

            // Save Promo Banner settings
            $promoBanner = Setting::get('promo_banner_settings', []);
            $promoBanner['eyebrow'] = $request->input('promo_eyebrow');
            $promoBanner['title'] = $request->input('promo_title');
            $promoBanner['button_text'] = $request->input('promo_button_text');
            $promoBanner['button_link'] = $request->input('promo_button_link');

            $promoBanner['image_url'] = $request->input('promo_image_url') ?? ($promoBanner['image_url'] ?? '');
            if ($request->hasFile('promo_file')) {
                $webpUrl = $this->convertToWebp($request->file('promo_file'), 'images/banners');
                if ($webpUrl) {
                    $promoBanner['image_url'] = $webpUrl;
                }
            }
            Setting::set('promo_banner_settings', $promoBanner);

            // Save Newsletter Settings
            $newsletter = Setting::get('newsletter_settings', []);
            $newsletter['eyebrow'] = $request->input('news_eyebrow');
            $newsletter['title'] = $request->input('news_title');
            $newsletter['description'] = $request->input('news_desc');
            Setting::set('newsletter_settings', $newsletter);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Homepage layout and banner configurations saved successfully.'
                ]);
            }

            return redirect()->route('admin.design.index')->with('success', 'Homepage layout and banner configurations saved successfully.');
        }

        // ── Instagram Feed (display handle only) ──────────────────
        if ($type === 'instagram') {
            $request->validate([
                'instagram_handle' => ['nullable', 'string', 'max:60', 'regex:/^[A-Za-z0-9_.]*$/'],
            ]);

            $handle = ltrim(trim((string) $request->input('instagram_handle')), '@');
            Setting::set('instagram_settings', ['handle' => $handle ?: 'madhavistores']);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Instagram handle updated.'
                ]);
            }

            return redirect()->route('admin.design.index')->with('success', 'Instagram handle updated.');
        }

        // ── Signin Image ──────────────────────────────────────────
        if ($type === 'signin') {
            $signinImage = $request->input('signin_image', '');

            if ($request->hasFile('signin_file')) {
                $webpUrl = $this->convertToWebp($request->file('signin_file'), 'images/auth');
                if ($webpUrl) {
                    $signinImage = $webpUrl;
                }
            }

            Setting::set('signin_image', $signinImage);
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sign-In page image updated.'
                ]);
            }
            return redirect()->route('admin.design.index')->with('success', 'Sign-In page image updated.');
        }

        // ── Hero Carousel ─────────────────────────────────────────
        if ($type === 'hero') {
            $slides = [];
            if ($request->has('slides')) {
                foreach ($request->input('slides') as $index => $slideData) {
                    $imageUrl = $slideData['image_url'] ?? '';
                    if ($request->hasFile("slides.{$index}.image_file")) {
                        $webpUrl = $this->convertToWebp($request->file("slides.{$index}.image_file"), 'images/hero');
                        if ($webpUrl) {
                            $imageUrl = $webpUrl;
                        }
                    }

                    $mobileImageUrl = $slideData['mobile_image_url'] ?? '';
                    if ($request->hasFile("slides.{$index}.mobile_image_file")) {
                        $webpUrl = $this->convertToWebp($request->file("slides.{$index}.mobile_image_file"), 'images/hero');
                        if ($webpUrl) {
                            $mobileImageUrl = $webpUrl;
                        }
                    }

                    $slides[] = [
                        'image_url'          => $imageUrl,
                        'mobile_image_url'   => $mobileImageUrl,
                        'eyebrow'            => $slideData['eyebrow'] ?? '',
                        'title'              => $slideData['title'] ?? '',
                        'subtitle'           => $slideData['subtitle'] ?? '',
                        'button_text'        => $slideData['button_text'] ?? '',
                        'button_url'         => $slideData['button_url'] ?? '',
                        'has_second_button'  => isset($slideData['has_second_button']),
                        'second_button_text' => $slideData['second_button_text'] ?? '',
                        'second_button_url'  => $slideData['second_button_url'] ?? '',
                    ];
                }
            }
            Setting::set('hero_slides', $slides);
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Hero Carousel updated successfully.'
                ]);
            }
            return redirect()->route('admin.design.index')->with('success', 'Hero Carousel updated successfully.');
        }



        // ── About Us ──────────────────────────────────────────────
        if ($type === 'about') {
            $about = Setting::get('about_settings', []);

            $about['story_eyebrow']   = $request->input('story_eyebrow');
            $about['story_title']     = $request->input('story_title');
            $about['story_paragraphs'] = [
                $request->input('story_paragraph_1'),
                $request->input('story_paragraph_2'),
                $request->input('story_paragraph_3'),
            ];

            $about['value1_title'] = $request->input('value1_title');
            $about['value1_desc']  = $request->input('value1_desc');
            $about['value2_title'] = $request->input('value2_title');
            $about['value2_desc']  = $request->input('value2_desc');
            $about['value3_title'] = $request->input('value3_title');
            $about['value3_desc']  = $request->input('value3_desc');

            $about['cover_image'] = $request->input('cover_image') ?? ($about['cover_image'] ?? '');
            if ($request->hasFile('about_cover_file')) {
                $webpUrl = $this->convertToWebp($request->file('about_cover_file'), 'images/about');
                if ($webpUrl) {
                    $about['cover_image'] = $webpUrl;
                }
            }

            Setting::set('about_settings', $about);
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'About Us settings updated.'
                ]);
            }
            return redirect()->route('admin.design.index')->with('success', 'About Us settings updated.');
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid setting update type.'
            ], 400);
        }
        return redirect()->route('admin.design.index')->with('error', 'Invalid setting update type.');
    }

    private function convertToWebp($file, $destinationFolder): ?string
    {
        try {
            $imageInfo = getimagesize($file->getRealPath());
        } catch (\Throwable $e) {
            logger()->warning('convertToWebp: getimagesize failed — ' . $e->getMessage());
            return null;
        }

        if (!$imageInfo) {
            return null;
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            logger()->warning('convertToWebp: file too large (' . $file->getSize() . ' bytes)');
            return null;
        }

        $mime = $imageInfo['mime'];
        $image = null;

        try {
            switch ($mime) {
                case 'image/jpeg':
                case 'image/jpg':
                    $image = imagecreatefromjpeg($file->getRealPath());
                    break;
                case 'image/png':
                    $image = imagecreatefrompng($file->getRealPath());
                    if ($image) {
                        imagepalettetotruecolor($image);
                        imagealphablending($image, true);
                        imagesavealpha($image, true);
                    }
                    break;
                case 'image/webp':
                    $image = imagecreatefromwebp($file->getRealPath());
                    break;
                case 'image/gif':
                    $image = imagecreatefromgif($file->getRealPath());
                    if ($image) {
                        imagepalettetotruecolor($image);
                    }
                    break;
                default:
                    return null;
            }
        } catch (\Throwable $e) {
            logger()->warning('convertToWebp: image creation failed — ' . $e->getMessage());
            return null;
        }

        if (!$image) {
            return null;
        }

        $filename = time() . '_' . uniqid() . '.webp';
        $destinationPath = public_path($destinationFolder);
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }
        $fullPath = $destinationPath . '/' . $filename;

        $success = imagewebp($image, $fullPath, 80);
        imagedestroy($image);

        if (!$success) {
            logger()->warning('convertToWebp: imagewebp() failed for destination ' . $fullPath);
            return null;
        }

        return '/' . $destinationFolder . '/' . $filename;
    }

    private function deleteLocalFile(?string $url): void
    {
        if (!$url) return;
        // Only delete local files (paths starting with /)
        if (str_starts_with($url, 'http')) return;
        $path = public_path(ltrim($url, '/'));
        if (file_exists($path)) {
            unlink($path);
        }
    }
}
