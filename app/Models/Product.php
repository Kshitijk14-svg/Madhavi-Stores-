<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'has_sizes', 'name', 'slug', 'price', 'original_price', 'stock',
        'image_url', 'size_chart_image', 'badge', 'is_bestseller',
        'discount_type', 'discount_value', 'is_new_arrival',
        'new_arrival_expires_at', 'gallery_images', 'seo_title',
        'seo_description', 'seo_keywords', 'tags', 'details'
    ];
    // NOTE: 'rating' and 'review_count' are intentionally NOT mass-assignable — they are
    // derived aggregates maintained by the Review model's recompute hook (App\Models\Review).

    protected $casts = [
        'gallery_images' => 'array',
        'tags' => 'array',
        'details' => 'array',
        'is_new_arrival' => 'boolean',
        'has_sizes' => 'boolean',
        'new_arrival_expires_at' => 'datetime',
    ];

    /**
     * The single source of truth for the price a customer is actually charged.
     * Applies discount_type/discount_value to the base price. Display (PDP, shop,
     * cart) AND the checkout charge path MUST use this so they can never drift.
     */
    public function getFinalPriceAttribute(): float
    {
        $price = (float) $this->price;

        if ($this->discount_type === 'percent' && $this->discount_value > 0) {
            $price = $price - ($price * ($this->discount_value / 100));
        } elseif ($this->discount_type === 'fixed' && $this->discount_value > 0) {
            $price = $price - $this->discount_value;
        }

        return round(max(0, $price), 2);
    }

    /** Derive the thumbnail path for a locally-stored WebP image, or null otherwise. */
    public static function thumbUrlFor(?string $url): ?string
    {
        if ($url && str_starts_with($url, '/images/') && str_ends_with($url, '.webp')) {
            return substr($url, 0, -strlen('.webp')) . '_thumb.webp';
        }
        return null;
    }

    /**
     * Card-sized image for product grids: the small thumbnail when it exists on disk,
     * otherwise the original image_url (covers external URLs and pre-thumbnail products).
     */
    public function getThumbUrlAttribute(): ?string
    {
        $thumb = self::thumbUrlFor($this->image_url);
        if ($thumb && file_exists(public_path(ltrim($thumb, '/')))) {
            return $thumb;
        }
        return $this->image_url;
    }

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
