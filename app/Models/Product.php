<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'has_sizes', 'name', 'slug', 'price', 'original_price', 'stock',
        'image_url', 'size_chart_image', 'badge', 'rating', 'review_count', 'is_bestseller',
        'discount_type', 'discount_value', 'is_new_arrival', 
        'new_arrival_expires_at', 'gallery_images', 'seo_title', 
        'seo_description', 'seo_keywords', 'tags', 'details'
    ];

    protected $casts = [
        'gallery_images' => 'array',
        'tags' => 'array',
        'details' => 'array',
        'is_new_arrival' => 'boolean',
        'has_sizes' => 'boolean',
        'new_arrival_expires_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function sizes()
    {
        return $this->hasMany(ProductSize::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
