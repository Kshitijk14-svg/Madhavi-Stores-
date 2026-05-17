<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'name', 'slug', 'price', 'original_price', 
        'image_url', 'badge', 'rating', 'review_count', 'is_bestseller'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
