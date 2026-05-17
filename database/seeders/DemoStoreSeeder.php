<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use App\Models\Coupon;
use App\Models\Setting;
use App\Models\CartItem;
use App\Models\WishlistItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoStoreSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. SEED COUPONS ──────────────────────────────────────
        Coupon::updateOrCreate(
            ['code' => 'FESTIVE20'],
            [
                'type' => 'percent',
                'value' => 20.00,
                'min_cart_value' => 2000.00,
                'is_active' => true,
                'expires_at' => now()->addMonths(6),
            ]
        );

        Coupon::updateOrCreate(
            ['code' => 'ATELIER1000'],
            [
                'type' => 'fixed',
                'value' => 1000.00,
                'min_cart_value' => 5000.00,
                'is_active' => true,
                'expires_at' => now()->addMonths(3),
            ]
        );

        Coupon::updateOrCreate(
            ['code' => 'WELCOME500'],
            [
                'type' => 'fixed',
                'value' => 500.00,
                'min_cart_value' => 0.00,
                'is_active' => true,
                'expires_at' => now()->addMonths(12),
            ]
        );

        // ── 2. SEED DEFAULT SITE SETTINGS ────────────────────────
        
        // Hero Carousel Settings
        $heroSlides = [
            [
                'image_url' => 'https://images.unsplash.com/photo-1583391733958-6c78278104ba?w=1920&q=85&auto=format&fit=crop&crop=top',
                'eyebrow' => 'New Collection · 2026',
                'title' => 'The Modern<br><em>Heritage</em>',
                'subtitle' => 'Handcrafted textiles. Ancient craft. Contemporary sensibility.',
                'button_text' => 'Shop Now',
                'button_url' => '/collection',
                'has_second_button' => true,
                'second_button_text' => 'View Lookbook',
                'second_button_url' => '/lookbook'
            ],
            [
                'image_url' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=1920&q=85&auto=format&fit=crop',
                'eyebrow' => 'Bridal Edit · 2026',
                'title' => 'Draped in<br><em>Silk</em>',
                'subtitle' => 'Exquisite sarees handwoven in fine mulberry silk.',
                'button_text' => 'Explore Sarees',
                'button_url' => '/collection',
                'has_second_button' => false,
                'second_button_text' => '',
                'second_button_url' => ''
            ],
            [
                'image_url' => 'https://images.unsplash.com/photo-1485230895905-31d1d1ebc325?w=1920&q=85&auto=format&fit=crop',
                'eyebrow' => 'Summer Edit · 2026',
                'title' => '<em>Breezy</em> Essentials',
                'subtitle' => 'Lightweight cotton silhouettes for warm, golden days.',
                'button_text' => 'Shop Summer',
                'button_url' => '/collection',
                'has_second_button' => false,
                'second_button_text' => '',
                'second_button_url' => ''
            ]
        ];
        Setting::set('hero_slides', $heroSlides);

        // Lookbook Settings
        $lookbookSettings = [
            'cover_image' => 'https://images.unsplash.com/photo-1485230895905-31d1d1ebc325?w=1920&q=85&auto=format&fit=crop',
            'cover_eyebrow' => '2026 Seasonal Editorial',
            'cover_title' => 'The<br><em>Modern</em><br>Muse',
            'chapter1_title' => 'Silk &<br><em>Shadow</em>',
            'chapter1_eyebrow' => 'Chapter 01',
            'chapter1_description' => 'Exploring the delicate interplay between light and hand-woven textures. Our signature silk collection captures the fleeting moments of golden hour, where craft becomes poetry.',
            'chapter1_image' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=1200&q=80&auto=format&fit=crop',
            'chapter1_inset_image' => 'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?w=600&q=80&auto=format&fit=crop',
            'middle_image' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=1920&q=80&auto=format&fit=crop',
            'chapter2_title' => 'Earthly<br><em>Grace</em>',
            'chapter2_eyebrow' => 'Chapter 02',
            'chapter2_description' => 'A tribute to natural dyes and organic cotton. This chapter celebrates beauty found in imperfection — the grace of simple silhouettes and the richness of undyed earth.',
            'chapter2_image' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=1200&q=80&auto=format&fit=crop',
            'bts_images' => [
                'https://images.unsplash.com/photo-1596455607563-ad6193f76b17?w=400&q=75',
                'https://images.unsplash.com/photo-1583391733958-6c78278104ba?w=400&q=75',
                'https://images.unsplash.com/photo-1613915617430-8ab0fd7c6baf?w=400&q=75',
                'https://images.unsplash.com/photo-1604928141064-207cea6f5722?w=400&q=75',
                'https://images.unsplash.com/photo-1551488831-00ddcb6c6bd3?w=400&q=75',
                'https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=400&q=75'
            ]
        ];
        Setting::set('lookbook_settings', $lookbookSettings);

        // About Us Settings
        $aboutSettings = [
            'cover_image' => 'https://images.unsplash.com/photo-1469334031218-e382a71b716b?w=1600&q=80',
            'story_eyebrow' => 'Rooted in Tradition',
            'story_title' => 'Crafting Luxury with a Soul',
            'story_paragraphs' => [
                'Founded in the heart of traditional textile hubs, Madhavi Stores was born out of a desire to preserve ancient Indian craftsmanship while catering to the sensibilities of the modern world.',
                'Our philosophy is simple: Quiet Luxury. We don\'t believe in loud logos or fast fashion. Instead, we focus on the subtle details—the hand-spun threads, the natural dyes, and the intricate embroidery that only a human hand can achieve.',
                'Every garment at Madhavi Stores is a labor of love, created by artisans who have inherited their skills through generations. By choosing us, you are not just buying clothing; you are supporting a legacy of sustainable, ethical fashion.'
            ],
            'value1_title' => 'Artisanal Craft',
            'value1_desc' => 'Every piece is meticulously handcrafted by skilled artisans, ensuring unique character and superior quality.',
            'value2_title' => 'Ethical Sourcing',
            'value2_desc' => 'We work directly with weaving clusters and craftsmen, ensuring fair wages and sustainable production methods.',
            'value3_title' => 'Timeless Design',
            'value3_desc' => 'Our designs transcend seasonal trends, focusing on silhouettes and patterns that remain elegant for years.'
        ];
        Setting::set('about_settings', $aboutSettings);

        // ── 3. CREATE DEMO CUSTOMERS ─────────────────────────────
        $customer1 = User::updateOrCreate(
            ['email' => 'aarav@example.com'],
            [
                'name' => 'Aarav Sharma',
                'password' => Hash::make('password'),
                'is_admin' => false,
                'email_verified_at' => now(),
            ]
        );

        $customer2 = User::updateOrCreate(
            ['email' => 'meera@example.com'],
            [
                'name' => 'Meera Iyer',
                'password' => Hash::make('password'),
                'is_admin' => false,
                'email_verified_at' => now(),
            ]
        );

        // ── 4. PRE-FILL ACTIVE CARTS AND WISHLISTS ────────────────
        $products = Product::all();
        if ($products->count() >= 4) {
            $p1 = $products[0];
            $p2 = $products[1];
            $p3 = $products[2];
            $p4 = $products[3];

            // Aarav Cart
            CartItem::updateOrCreate(
                ['user_id' => $customer1->id, 'product_id' => $p1->id, 'size' => 'M'],
                ['quantity' => 1]
            );
            CartItem::updateOrCreate(
                ['user_id' => $customer1->id, 'product_id' => $p2->id, 'size' => 'L'],
                ['quantity' => 2]
            );

            // Aarav Wishlist
            WishlistItem::updateOrCreate([
                'user_id' => $customer1->id,
                'product_id' => $p3->id
            ]);

            // Meera Cart
            CartItem::updateOrCreate(
                ['user_id' => $customer2->id, 'product_id' => $p3->id, 'size' => 'S'],
                ['quantity' => 1]
            );

            // Meera Wishlist
            WishlistItem::updateOrCreate([
                'user_id' => $customer2->id,
                'product_id' => $p1->id
            ]);
            WishlistItem::updateOrCreate([
                'user_id' => $customer2->id,
                'product_id' => $p4->id
            ]);
        }

        // ── 5. PRE-FILL ORDER HISTORIES ──────────────────────────
        if ($products->count() >= 3) {
            $p1 = $products[0];
            $p2 = $products[1];
            $p3 = $products[2];

            // Order 1: Aarav (Completed/Delivered order)
            $subtotal1 = $p1->price + $p2->price;
            $tax1 = round($subtotal1 * 0.18, 2);
            $total1 = $subtotal1 + $tax1;

            $order1 = Order::updateOrCreate(
                ['order_number' => 'MS-EL29837X'],
                [
                    'user_id' => $customer1->id,
                    'email' => $customer1->email,
                    'first_name' => 'Aarav',
                    'last_name' => 'Sharma',
                    'address' => 'Apt 4B, Regency Crest, Koramangala',
                    'city' => 'Bengaluru',
                    'postal_code' => '560034',
                    'payment_method' => 'Card',
                    'payment_status' => 'Paid',
                    'order_status' => 'Delivered',
                    'subtotal' => $subtotal1,
                    'discount' => 0,
                    'tax' => $tax1,
                    'total' => $total1,
                    'created_at' => now()->subDays(15),
                ]
            );

            OrderItem::firstOrCreate(
                ['order_id' => $order1->id, 'product_id' => $p1->id, 'size' => 'M'],
                [
                    'product_name' => $p1->name,
                    'price' => $p1->price,
                    'quantity' => 1
                ]
            );

            OrderItem::firstOrCreate(
                ['order_id' => $order1->id, 'product_id' => $p2->id, 'size' => 'L'],
                [
                    'product_name' => $p2->name,
                    'price' => $p2->price,
                    'quantity' => 1
                ]
            );

            // Order 2: Meera (Pending, UPI payment order)
            $subtotal2 = $p3->price * 2;
            $discount2 = 500.00; // Applied coupon WELCOME500
            $tax2 = round(($subtotal2 - $discount2) * 0.18, 2);
            $total2 = ($subtotal2 - $discount2) + $tax2;

            $order2 = Order::updateOrCreate(
                ['order_number' => 'MS-TR98231O'],
                [
                    'user_id' => $customer2->id,
                    'email' => $customer2->email,
                    'first_name' => 'Meera',
                    'last_name' => 'Iyer',
                    'address' => 'Flat 502, Orchid Heights, Prabhadevi',
                    'city' => 'Mumbai',
                    'postal_code' => '400025',
                    'payment_method' => 'UPI',
                    'payment_status' => 'Paid',
                    'order_status' => 'Processing',
                    'subtotal' => $subtotal2,
                    'discount' => $discount2,
                    'tax' => $tax2,
                    'total' => $total2,
                    'coupon_code' => 'WELCOME500',
                    'created_at' => now()->subDays(2),
                ]
            );

            OrderItem::firstOrCreate(
                ['order_id' => $order2->id, 'product_id' => $p3->id, 'size' => 'S'],
                [
                    'product_name' => $p3->name,
                    'price' => $p3->price,
                    'quantity' => 2
                ]
            );
        }
    }
}
