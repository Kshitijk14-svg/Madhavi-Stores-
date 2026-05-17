<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\CheckoutController;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Collection and Products
Route::get('/collection', [ProductController::class, 'index'])->name('collection');
Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.show');

// Editorial & Static Pages
Route::get('/lookbook', [HomeController::class, 'lookbook'])->name('lookbook');
Route::get('/about',    [HomeController::class, 'about'])->name('about');

// ── Auth Routes ────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    // Register
    Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');

    // Email OTP Verification (after registration)
    Route::get('/verify-email',  [AuthController::class, 'showVerify'])->name('verify.show');
    Route::post('/verify-email', [AuthController::class, 'verify'])->name('verify.post');
    Route::post('/verify-email/resend', [AuthController::class, 'resendOtp'])->name('verify.resend');

    // Forgot Password
    Route::get('/forgot-password',  [AuthController::class, 'showForgot'])->name('password.forgot');
    Route::post('/forgot-password', [AuthController::class, 'sendResetOtp'])->name('password.forgot.post');

    // Reset Password (OTP-based)
    Route::get('/reset-password',  [AuthController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset.post');
});

// ── Authenticated Routes ───────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/account', [AuthController::class, 'account'])->name('account');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Cart Operations
    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::post('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/coupon/apply', [CartController::class, 'applyCoupon'])->name('coupon.apply');
    Route::post('/coupon/remove', [CartController::class, 'removeCoupon'])->name('coupon.remove');

    // Wishlist Operations
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
    Route::post('/wishlist/toggle/{productId}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

    // Checkout Operations
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
});

// Admin Routes (Custom Blade Admin Dashboard)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    
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

    // User Management with Role Control
    Route::get('/users', [AdminController::class, 'usersCartWishlist'])->name('users.index');
    Route::post('/users/{id}/role', [AdminController::class, 'updateUserRole'])->name('users.role');

    // Coupon Management
    Route::get('/coupons', [AdminController::class, 'coupons'])->name('coupons.index');
    Route::post('/coupons', [AdminController::class, 'storeCoupon'])->name('coupons.store');
    Route::post('/coupons/{id}/update', [AdminController::class, 'updateCoupon'])->name('coupons.update');
    Route::post('/coupons/{id}/toggle', [AdminController::class, 'toggleCoupon'])->name('coupons.toggle');
    Route::post('/coupons/{id}/delete', [AdminController::class, 'deleteCoupon'])->name('coupons.delete');

    // Single-Page Dynamic Design Manager
    Route::get('/design', [AdminController::class, 'designManager'])->name('design.index');
    Route::post('/design/update', [AdminController::class, 'updateDesignSettings'])->name('design.update');
});
