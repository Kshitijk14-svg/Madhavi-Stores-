<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\SitemapController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// Collection and Products
Route::get('/shop', [ProductController::class, 'index'])->name('shop');
// Live search autocomplete (JSON) — public, throttled to curb scraping.
Route::get('/search/suggestions', [ProductController::class, 'suggestions'])->middleware('throttle:60,1')->name('search.suggestions');
Route::get('/collections', [ProductController::class, 'collections'])->name('collections.index');
Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.show');

// Editorial & Static Pages
Route::get('/about',    [HomeController::class, 'about'])->name('about');
Route::get('/shipping-policy', [HomeController::class, 'shippingPolicy'])->name('shipping.policy');
Route::get('/return-policy',   [HomeController::class, 'returnPolicy'])->name('return.policy');
Route::get('/privacy-policy',  [HomeController::class, 'privacyPolicy'])->name('privacy.policy');
Route::get('/terms-and-conditions', [HomeController::class, 'termsAndConditions'])->name('terms.conditions');

// Razorpay server-to-server webhook (no auth, CSRF-exempt — see bootstrap/app.php).
// Signature-verified inside the controller.
Route::post('/webhooks/razorpay', [CheckoutController::class, 'webhook'])->name('webhooks.razorpay');

// ── Auth Routes ────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.post');

    // Register
    Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.post');

    // OTP Verification (register email + reset password)
    Route::get('/verify-email',          [AuthController::class, 'showVerify'])->name('verify.show');
    Route::post('/verify-email',         [AuthController::class, 'verify'])->middleware('throttle:5,1')->name('verify.post');
    Route::post('/verify-email/resend',  [AuthController::class, 'resendOtp'])->middleware('throttle:3,10')->name('verify.resend');

    // Forgot / Reset Password (OTP-based)
    Route::get('/forgot-password',  [AuthController::class, 'showForgotPassword'])->name('password.forgot');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1')->name('password.forgot.post');
    Route::get('/reset-password',   [AuthController::class, 'showResetPassword'])->name('password.reset.show');
    Route::post('/reset-password',  [AuthController::class, 'resetPassword'])->middleware('throttle:5,1')->name('password.reset.post');
});

// ── Cart & Wishlist (guests allowed; checkout still requires login) ──
Route::get('/cart', [CartController::class, 'index'])->name('cart');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
// Coupon apply is brute-forceable for valid codes — throttle it.
Route::post('/coupon/apply', [CartController::class, 'applyCoupon'])->middleware('throttle:10,1')->name('coupon.apply');
Route::post('/coupon/remove', [CartController::class, 'removeCoupon'])->name('coupon.remove');

Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
Route::post('/wishlist/toggle/{product}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

// ── Authenticated Routes ───────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/account', [AuthController::class, 'account'])->name('account');
    Route::post('/account', [AuthController::class, 'updateProfile'])->name('account.update');
    Route::get('/account/orders/{id}/receipt', [AuthController::class, 'orderReceipt'])->name('account.order.receipt');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Checkout Operations (throttled — each store() can hit the Razorpay API)
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:20,1')->name('checkout.store');
    Route::post('/checkout/verify', [CheckoutController::class, 'verifyPayment'])->middleware('throttle:20,1')->name('checkout.verify');
});

// Admin Routes (Custom Blade Admin Dashboard)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/sales-chart-data', [AdminController::class, 'salesChartData'])->name('sales.chart');
    
    // Products CRUD
    Route::get('/products', [AdminController::class, 'products'])->name('products.index');
    Route::get('/products/create', [AdminController::class, 'createProduct'])->name('products.create');
    Route::post('/products', [AdminController::class, 'storeProduct'])->name('products.store');
    Route::get('/products/{id}/edit', [AdminController::class, 'editProduct'])->name('products.edit');
    Route::post('/products/{id}', [AdminController::class, 'updateProduct'])->name('products.update');
    Route::post('/products/{id}/delete', [AdminController::class, 'deleteProduct'])->name('products.delete');
    
    // Categories (Collections) CRUD
    Route::get('/categories', [AdminController::class, 'categories'])->name('categories.index');
    Route::get('/categories/create', [AdminController::class, 'createCategory'])->name('categories.create');
    Route::post('/categories', [AdminController::class, 'storeCategory'])->name('categories.store');
    Route::get('/categories/{id}/edit', [AdminController::class, 'editCategory'])->name('categories.edit');
    Route::post('/categories/{id}', [AdminController::class, 'updateCategory'])->name('categories.update');
    Route::post('/categories/{id}/delete', [AdminController::class, 'deleteCategory'])->name('categories.delete');

    // Orders Management
    Route::get('/orders', [AdminController::class, 'orders'])->name('orders.index');
    Route::post('/orders/{id}/status', [AdminController::class, 'updateOrderStatus'])->name('orders.update');
    Route::post('/orders/{id}/delete', [AdminController::class, 'deleteOrder'])->name('orders.delete');
    Route::get('/orders/{id}/invoice', [AdminController::class, 'orderInvoice'])->name('orders.invoice');
    Route::post('/orders/{id}/send-invoice', [AdminController::class, 'sendInvoiceEmail'])->name('orders.send_invoice');

    // User Management with Role Control
    Route::get('/users', [AdminController::class, 'usersCartWishlist'])->name('users.index');
    Route::post('/users/{id}/role', [AdminController::class, 'updateUserRole'])->name('users.role');

    // Coupon Management
    Route::get('/coupons', [AdminController::class, 'coupons'])->name('coupons.index');
    Route::post('/coupons', [AdminController::class, 'storeCoupon'])->name('coupons.store');
    Route::post('/coupons/{id}/update', [AdminController::class, 'updateCoupon'])->name('coupons.update');
    Route::post('/coupons/{id}/toggle', [AdminController::class, 'toggleCoupon'])->name('coupons.toggle');
    Route::post('/coupons/{id}/delete', [AdminController::class, 'deleteCoupon'])->name('coupons.delete');

    // Single-Page Dynamic Design Manager (desktop only — no mobile experience)
    Route::get('/design', [AdminController::class, 'designManager'])->name('design.index')->middleware('desktop.only');
    Route::post('/design/update', [AdminController::class, 'updateDesignSettings'])->name('design.update')->middleware('desktop.only');
});
