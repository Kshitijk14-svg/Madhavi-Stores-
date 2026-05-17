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
        
        $stats = [
            ['label' => 'Total Sales', 'value' => '₹' . number_format($revenue), 'trend' => 'All Orders'],
            ['label' => 'Total Orders', 'value' => Order::count(), 'trend' => 'Completed & Pending'],
            ['label' => 'Active Carts', 'value' => CartItem::distinct('user_id')->count('user_id'), 'trend' => 'Customer Carts'],
            ['label' => 'Total Coupons', 'value' => Coupon::where('is_active', true)->count(), 'trend' => 'Active Promos'],
        ];

        $recentProducts = Product::with('category')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentProducts'));
    }

    // ── PRODUCTS CRUD ───────────────────────────────────────────

    public function products()
    {
        $products = Product::with('category')->latest()->paginate(10);
        return view('admin.products.index', compact('products'));
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
            'image'          => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
            'image_url'      => 'nullable|url',
            'badge'          => 'nullable|string|max:50',
            'is_bestseller'  => 'nullable|boolean',
        ]);

        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $count = 1;
        while (Product::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $imageUrl = $request->image_url;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/products'), $filename);
            $imageUrl = '/images/products/' . $filename;
        }

        Product::create([
            'name'           => $request->name,
            'slug'           => $slug,
            'price'          => $request->price,
            'original_price' => $request->original_price,
            'category_id'    => $request->category_id,
            'image_url'      => $imageUrl,
            'badge'          => $request->badge,
            'is_bestseller'  => $request->has('is_bestseller'),
            'rating'         => 5.0,
            'review_count'   => 0,
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Product added successfully.');
    }

    public function editProduct($id)
    {
        $product    = Product::findOrFail($id);
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

        $imageUrl = $request->image_url ?? $product->image_url;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/products'), $filename);
            $imageUrl = '/images/products/' . $filename;
        }

        $product->update([
            'name'           => $request->name,
            'slug'           => $slug,
            'price'          => $request->price,
            'original_price' => $request->original_price,
            'category_id'    => $request->category_id,
            'image_url'      => $imageUrl,
            'badge'          => $request->badge,
            'is_bestseller'  => $request->has('is_bestseller'),
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function deleteProduct($id)
    {
        Product::findOrFail($id)->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }

    // ── CATEGORIES (COLLECTIONS) CRUD ────────────────────────────

    public function categories()
    {
        $categories = Category::withCount('products')->latest()->get();
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
        ]);

        $slug     = Str::slug($request->name);
        $imageUrl = $request->image_url;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/categories'), $filename);
            $imageUrl = '/images/categories/' . $filename;
        }

        Category::create([
            'name'      => $request->name,
            'slug'      => $slug,
            'image_url' => $imageUrl,
        ]);

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
        ]);

        $slug     = Str::slug($request->name);
        $imageUrl = $request->image_url ?? $category->image_url;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/categories'), $filename);
            $imageUrl = '/images/categories/' . $filename;
        }

        $category->update([
            'name'      => $request->name,
            'slug'      => $slug,
            'image_url' => $imageUrl,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Collection updated successfully.');
    }

    public function deleteCategory($id)
    {
        Category::findOrFail($id)->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Collection deleted successfully.');
    }

    // ── ORDERS MANAGEMENT ────────────────────────────────────────

    public function orders(Request $request)
    {
        $query = Order::with(['items.product', 'user'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")
                  ->orWhere('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('order_status', $request->status);
        }

        $orders = $query->paginate(10);
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

        $pdf = Pdf::loadView('admin.invoice', compact('order'))
                  ->setPaper('a4', 'portrait');

        return $pdf->download('Invoice-' . $order->order_number . '.pdf');
    }

    // ── USER MANAGEMENT WITH ROLE CONTROL ──────────────────────

    public function usersCartWishlist()
    {
        $users = User::with(['cartItems.product', 'wishlistItems.product'])
                     ->latest()
                     ->paginate(15);

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
     * Update a user's role. Only SuperAdmin can do this.
     */
    public function updateUserRole(Request $request, $id)
    {
        // Only superadmin can change roles
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only the Super Administrator can change user roles.');
        }

        $request->validate([
            'role' => 'required|in:customer,admin,superadmin',
        ]);

        $user = User::findOrFail($id);

        // Prevent changing another superadmin's role
        if ($user->isSuperAdmin() && $user->id !== auth()->id()) {
            return redirect()->route('admin.users.index')
                             ->with('error', 'You cannot change another Super Administrator\'s role.');
        }

        $user->update([
            'role'     => $request->role,
            'is_admin' => in_array($request->role, ['admin', 'superadmin']),
        ]);

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
        $lookbook    = Setting::get('lookbook_settings', []);
        $about       = Setting::get('about_settings', []);
        $signinImage = Setting::get('signin_image', '');

        return view('admin.design.index', compact('heroSlides', 'lookbook', 'about', 'signinImage'));
    }

    public function updateDesignSettings(Request $request)
    {
        $type = $request->input('type');

        // ── Signin Image ──────────────────────────────────────────
        if ($type === 'signin') {
            $signinImage = $request->input('signin_image', '');

            if ($request->hasFile('signin_file')) {
                $file = $request->file('signin_file');
                if (!is_dir(public_path('images/auth'))) {
                    mkdir(public_path('images/auth'), 0777, true);
                }
                $filename    = time() . '_signin_' . $file->getClientOriginalName();
                $file->move(public_path('images/auth'), $filename);
                $signinImage = '/images/auth/' . $filename;
            }

            Setting::set('signin_image', $signinImage);
            return redirect()->route('admin.design.index')->with('success', 'Sign-In page image updated.');
        }

        // ── Hero Carousel ─────────────────────────────────────────
        if ($type === 'hero') {
            $slides = [];
            if ($request->has('slides')) {
                foreach ($request->input('slides') as $index => $slideData) {
                    $imageUrl = $slideData['image_url'] ?? '';
                    if ($request->hasFile("slides.{$index}.image_file")) {
                        if (!is_dir(public_path('images/hero'))) {
                            mkdir(public_path('images/hero'), 0777, true);
                        }
                        $file     = $request->file("slides.{$index}.image_file");
                        $filename = time() . '_hero_' . $index . '_' . $file->getClientOriginalName();
                        $file->move(public_path('images/hero'), $filename);
                        $imageUrl = '/images/hero/' . $filename;
                    }

                    $slides[] = [
                        'image_url'          => $imageUrl,
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
            return redirect()->route('admin.design.index')->with('success', 'Hero Carousel updated successfully.');
        }

        // ── Lookbook ──────────────────────────────────────────────
        if ($type === 'lookbook') {
            $lookbook = Setting::get('lookbook_settings', []);

            $lookbook['cover_eyebrow']        = $request->input('cover_eyebrow');
            $lookbook['cover_title']          = $request->input('cover_title');
            $lookbook['chapter1_eyebrow']     = $request->input('chapter1_eyebrow');
            $lookbook['chapter1_title']       = $request->input('chapter1_title');
            $lookbook['chapter1_description'] = $request->input('chapter1_description');
            $lookbook['chapter2_eyebrow']     = $request->input('chapter2_eyebrow');
            $lookbook['chapter2_title']       = $request->input('chapter2_title');
            $lookbook['chapter2_description'] = $request->input('chapter2_description');

            $imageFields = [
                'cover_image'           => ['input' => 'cover_image',          'file' => 'cover_file',           'dir' => 'lookbook', 'prefix' => 'lb_cover'],
                'chapter1_image'        => ['input' => 'chapter1_image',       'file' => 'chapter1_file',        'dir' => 'lookbook', 'prefix' => 'lb_ch1'],
                'chapter1_inset_image'  => ['input' => 'chapter1_inset_image', 'file' => 'chapter1_inset_file',  'dir' => 'lookbook', 'prefix' => 'lb_ch1_inset'],
                'middle_image'          => ['input' => 'middle_image',         'file' => 'middle_file',          'dir' => 'lookbook', 'prefix' => 'lb_middle'],
                'chapter2_image'        => ['input' => 'chapter2_image',       'file' => 'chapter2_file',        'dir' => 'lookbook', 'prefix' => 'lb_ch2'],
            ];

            foreach ($imageFields as $key => $cfg) {
                $lookbook[$key] = $request->input($cfg['input']) ?? ($lookbook[$key] ?? '');
                if ($request->hasFile($cfg['file'])) {
                    if (!is_dir(public_path('images/' . $cfg['dir']))) {
                        mkdir(public_path('images/' . $cfg['dir']), 0777, true);
                    }
                    $file = $request->file($cfg['file']);
                    $filename = time() . '_' . $cfg['prefix'] . '_' . $file->getClientOriginalName();
                    $file->move(public_path('images/' . $cfg['dir']), $filename);
                    $lookbook[$key] = '/images/' . $cfg['dir'] . '/' . $filename;
                }
            }

            $bts = $lookbook['bts_images'] ?? [];
            for ($i = 0; $i < 6; $i++) {
                $bts[$i] = $request->input("bts_images.{$i}") ?? ($bts[$i] ?? '');
                if ($request->hasFile("bts_files.{$i}")) {
                    if (!is_dir(public_path('images/lookbook'))) {
                        mkdir(public_path('images/lookbook'), 0777, true);
                    }
                    $file = $request->file("bts_files.{$i}");
                    $filename = time() . '_lb_bts_' . $i . '_' . $file->getClientOriginalName();
                    $file->move(public_path('images/lookbook'), $filename);
                    $bts[$i] = '/images/lookbook/' . $filename;
                }
            }
            $lookbook['bts_images'] = $bts;

            Setting::set('lookbook_settings', $lookbook);
            return redirect()->route('admin.design.index')->with('success', 'Lookbook Editorial settings updated.');
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
                if (!is_dir(public_path('images/about'))) {
                    mkdir(public_path('images/about'), 0777, true);
                }
                $file = $request->file('about_cover_file');
                $filename = time() . '_about_cover_' . $file->getClientOriginalName();
                $file->move(public_path('images/about'), $filename);
                $about['cover_image'] = '/images/about/' . $filename;
            }

            Setting::set('about_settings', $about);
            return redirect()->route('admin.design.index')->with('success', 'About Us settings updated.');
        }

        return redirect()->route('admin.design.index')->with('error', 'Invalid setting update type.');
    }
}
