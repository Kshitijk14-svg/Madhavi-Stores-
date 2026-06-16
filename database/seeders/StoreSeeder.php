<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Category;
use App\Models\Product;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Kurta Sets', 'image_url' => 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?w=800&q=80', 'slug' => 'kurta-sets'],
            ['name' => 'Sarees', 'image_url' => 'https://images.unsplash.com/photo-1613915617430-8ab0fd7c6baf?w=800&q=80', 'slug' => 'sarees'],
            ['name' => 'Lehengas', 'image_url' => 'https://images.unsplash.com/photo-1596455607563-ad6193f76b17?w=800&q=80', 'slug' => 'lehengas'],
            ['name' => 'Co-ord Sets', 'image_url' => 'https://images.unsplash.com/photo-1551488831-00ddcb6c6bd3?w=800&q=80', 'slug' => 'coord-sets']
        ];

        foreach ($categories as $catData) {
            Category::firstOrCreate(['slug' => $catData['slug']], $catData);
        }

        $kurtaId = Category::where('slug', 'kurta-sets')->value('id');
        $sareesId = Category::where('slug', 'sarees')->value('id');
        $lehengasId = Category::where('slug', 'lehengas')->value('id');
        $coordsId = Category::where('slug', 'coord-sets')->value('id');

        $products = [
            [
                'category_id' => $kurtaId,
                'name' => 'Ivory Chanderi Kurta',
                'price' => 4500,
                'original_price' => 5500,
                'image_url' => 'https://images.unsplash.com/photo-1583391733958-6c78278104ba?w=600&q=80',
                'badge' => 'NEW',
                'slug' => 'ivory-chanderi-kurta',
                'rating' => 4.5,
                'review_count' => 24,
                'is_bestseller' => false,
                'is_new_arrival' => true
            ],
            [
                'category_id' => $kurtaId,
                'name' => 'Midnight Anarkali Set',
                'price' => 8500,
                'original_price' => null,
                'image_url' => 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?w=600&q=80',
                'badge' => null,
                'slug' => 'midnight-anarkali-set',
                'rating' => 5.0,
                'review_count' => 12,
                'is_bestseller' => false,
                'is_new_arrival' => true
            ],
            [
                'category_id' => null,
                'name' => 'Terracotta Block Print Dupatta',
                'price' => 1200,
                'original_price' => 1800,
                'image_url' => 'https://images.unsplash.com/photo-1604928141064-207cea6f5722?w=600&q=80',
                'badge' => 'SALE',
                'slug' => 'terracotta-block-print-dupatta',
                'rating' => 4.0,
                'review_count' => 56,
                'is_bestseller' => false,
                'is_new_arrival' => false
            ],
            [
                'category_id' => $coordsId,
                'name' => 'Emerald Silk Co-ord',
                'price' => 6200,
                'original_price' => null,
                'image_url' => 'https://images.unsplash.com/photo-1551488831-00ddcb6c6bd3?w=600&q=80',
                'badge' => null,
                'slug' => 'emerald-silk-coord',
                'rating' => 4.8,
                'review_count' => 18,
                'is_bestseller' => false,
                'is_new_arrival' => true
            ],
            [
                'category_id' => $sareesId,
                'name' => 'Blush Organza Saree',
                'price' => 7800,
                'original_price' => 8500,
                'image_url' => 'https://images.unsplash.com/photo-1613915617430-8ab0fd7c6baf?w=600&q=80',
                'badge' => 'SALE',
                'slug' => 'blush-organza-saree',
                'rating' => 4.9,
                'review_count' => 31,
                'is_bestseller' => true,
                'is_new_arrival' => false
            ],
            [
                'category_id' => null,
                'name' => 'Onyx Velvet Tunic',
                'price' => 3400,
                'original_price' => null,
                'image_url' => 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=600&q=80',
                'badge' => 'NEW',
                'slug' => 'onyx-velvet-tunic',
                'rating' => 4.2,
                'review_count' => 9,
                'is_bestseller' => true,
                'is_new_arrival' => true
            ],
            [
                'category_id' => $kurtaId,
                'name' => 'Mustard Handloom Kurta',
                'price' => 2800,
                'original_price' => 3200,
                'image_url' => 'https://images.unsplash.com/photo-1509319117193-57bab727e09d?w=600&q=80',
                'badge' => null,
                'slug' => 'mustard-handloom-kurta',
                'rating' => 4.6,
                'review_count' => 42,
                'is_bestseller' => true,
                'is_new_arrival' => false
            ],
            [
                'category_id' => $lehengasId,
                'name' => 'Pearl Embroidered Lehenga',
                'price' => 15500,
                'original_price' => null,
                'image_url' => 'https://images.unsplash.com/photo-1596455607563-ad6193f76b17?w=600&q=80',
                'badge' => 'LIMITED',
                'slug' => 'pearl-embroidered-lehenga',
                'rating' => 5.0,
                'review_count' => 7,
                'is_bestseller' => true,
                'is_new_arrival' => false
            ]
        ];

        foreach ($products as $prodData) {
            Product::updateOrCreate(['slug' => $prodData['slug']], $prodData);
        }
    }
}
